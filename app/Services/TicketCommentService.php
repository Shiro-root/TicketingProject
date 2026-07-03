<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketCommentService
{
    public function __construct(private readonly TicketActivityService $activity)
    {
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

            $this->activity->commented($ticket, $author, (bool) ($data['is_internal'] ?? false));

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

    /** Cari pola @nama.pengguna / @email dan resolve ke user id yang valid. */
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

    /** Comment yang boleh dilihat oleh user sesuai role (internal note disembunyikan dari Employee/Guest). */
    public function visibleFor(Ticket $ticket, User $user)
    {
        $query = $ticket->comments()->with(['user', 'attachments', 'replies.user', 'replies.attachments']);

        if ($user->hasRole('employee', 'guest')) {
            $query->where('is_internal', false);
        }

        return $query->oldest()->get();
    }
}
