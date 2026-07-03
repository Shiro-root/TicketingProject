<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketCommentService
{
    public function __construct(
        private readonly TicketActivityService $activity,
        private readonly NotificationService $notifications,
    ) {
    }

    public function create(Ticket $ticket, User $author, array $data, Request $request): TicketComment
    {
        return DB::transaction(function () use ($ticket, $author, $data, $request) {
            $comment = $ticket->allComments()->create([
                'user_id' => $author->id,
                'parent_id' => $data['parent_id'] ?? null,
                'body' => $data['body'],
                'is_internal' => $data['is_internal'] ?? false,
            ]);

            $mentionedIds = $this->extractMentions($data['body']);
            if ($mentionedIds->isNotEmpty()) {
                $comment->mentionedUsers()->sync($mentionedIds);
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket-attachments/'.$ticket->id, 'public');
                    $ticket->attachments()->create([
                        'ticket_comment_id' => $comment->id,
                        'uploaded_by' => $author->id,
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'disk' => 'public',
                        'mime_type' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            $isInternal = (bool) ($data['is_internal'] ?? false);
            $this->activity->commented($ticket, $author, $isInternal);

            // Trigger: Komentar baru. Catatan internal HANYA memberi tahu sesama staff
            // (assignee/tim teknisi) — tidak pernah bocor ke Employee/Guest lewat notifikasi.
            $recipients = $isInternal
                ? $ticket->technicians->merge([$ticket->assignee])->filter()->unique('id')
                : collect([$ticket->creator, $ticket->assignee])->filter()->unique('id');

            $this->notifications->sendToMany(
                $recipients,
                NotificationType::NEW_COMMENT,
                $ticket,
                ['comment_body' => Str::limit($data['body'], 200)],
                $author,
            );

            // Trigger: Mention — selalu dikirim ke user yang di-@ (mereka eksplisit ditandai).
            if ($mentionedIds->isNotEmpty()) {
                $mentionedUsers = User::whereIn('id', $mentionedIds)->get();
                $this->notifications->sendToMany($mentionedUsers, NotificationType::MENTIONED, $ticket, [], $author);
            }

            return $comment->fresh(['user', 'attachments', 'mentionedUsers']);
        });
    }

    public function update(TicketComment $comment, string $body, User $actor): TicketComment
    {
        $comment->update([
            'body' => $body,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        $mentionedIds = $this->extractMentions($body);
        $comment->mentionedUsers()->sync($mentionedIds);

        return $comment->fresh();
    }

    public function delete(TicketComment $comment): void
    {
        $comment->delete();
    }

    private function extractMentions(string $body): \Illuminate\Support\Collection
    {
        preg_match_all('/@([\w.\-]+)/', $body, $matches);
        $handles = collect($matches[1] ?? [])->unique();

        if ($handles->isEmpty()) {
            return collect();
        }

        return User::query()
            ->where(function ($q) use ($handles) {
                foreach ($handles as $handle) {
                    $q->orWhere('email', 'like', $handle.'%')
                      ->orWhere('name', 'like', str_replace('.', ' ', $handle).'%');
                }
            })
            ->pluck('id');
    }

    public function visibleFor(Ticket $ticket, User $user)
    {
        $query = $ticket->comments()->with(['user', 'attachments', 'replies.user', 'replies.attachments']);

        if ($user->hasRole('employee', 'guest')) {
            $query->where('is_internal', false);
        }

        return $query->oldest()->get();
    }
}