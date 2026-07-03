<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\NotificationType;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Sla;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function __construct(
        private readonly TicketActivityService $activity,
        private readonly AuditLogger $auditLogger,
        private readonly NotificationService $notifications,
    ) {
    }

    public function generateTicketNumber(): string
    {
        $year = now()->year;
        $last = Ticket::withTrashed()
            ->where('ticket_number', 'like', "TCK-{$year}-%")
            ->orderByDesc('id')
            ->value('ticket_number');

        $next = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('TCK-%d-%06d', $year, $next);
    }

    public function create(array $data, User $creator, Request $request): Ticket
    {
        return DB::transaction(function () use ($data, $creator, $request) {
            $category = Category::findOrFail($data['category_id']);
            $sla = $category->sla_id ? Sla::find($category->sla_id) : Sla::where('priority', $data['priority'])->first();
            $slaMinutes = $sla?->resolution_time_minutes ?? \App\Enums\TicketPriority::from($data['priority'])->defaultSlaHours() * 60;

            $ticket = Ticket::create([
                'ticket_number' => $this->generateTicketNumber(),
                'subject' => $data['subject'],
                'description' => $data['description'],
                'category_id' => $category->id,
                'department_id' => $data['department_id'] ?? $category->department_id,
                'priority' => $data['priority'],
                'status' => TicketStatus::OPEN->value,
                'sla_id' => $sla?->id,
                'created_by' => $creator->id,
                'due_at' => now()->addMinutes($slaMinutes),
            ]);

            if (! empty($data['tags'])) {
                $ticket->tags()->sync($data['tags']);
            }

            if (! empty($data['asset_ids'])) {
                $ticket->assets()->sync($data['asset_ids']);
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->storeAttachment($ticket, $file, $creator);
                }
            }

            $this->activity->created($ticket, $creator);
            $this->auditLogger->log(AuditAction::CREATE, $creator, $request, $ticket, null, $ticket->only([
                'subject', 'category_id', 'priority', 'status',
            ]));

            // Trigger: Ticket dibuat — konfirmasi ke pembuat (actor null supaya tidak di-skip self-notify).
            $this->notifications->send($creator, NotificationType::TICKET_CREATED, $ticket);

            return $ticket->fresh();
        });
    }

    public function update(Ticket $ticket, array $data, User $actor, Request $request): Ticket
    {
        return DB::transaction(function () use ($ticket, $data, $actor, $request) {
            $old = $ticket->only(['subject', 'description', 'category_id', 'priority', 'department_id']);
            $oldPriority = $ticket->priority;

            $ticket->fill([
                'subject' => $data['subject'] ?? $ticket->subject,
                'description' => $data['description'] ?? $ticket->description,
                'category_id' => $data['category_id'] ?? $ticket->category_id,
                'department_id' => $data['department_id'] ?? $ticket->department_id,
                'priority' => $data['priority'] ?? $ticket->priority,
            ]);

            $wasDirty = $ticket->isDirty();

            if ($ticket->isDirty('priority')) {
                $this->activity->priorityChanged($ticket, $actor, $oldPriority->value, $ticket->priority->value);
            }

            $ticket->save();

            if (isset($data['tags'])) {
                $ticket->tags()->sync($data['tags']);
            }

            if (isset($data['asset_ids'])) {
                $ticket->assets()->sync($data['asset_ids']);
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->storeAttachment($ticket, $file, $actor);
                }
            }

            $this->auditLogger->log(AuditAction::UPDATE, $actor, $request, $ticket, $old, $ticket->only([
                'subject', 'description', 'category_id', 'priority', 'department_id',
            ]));

            // Trigger: Ticket diupdate — beri tahu stakeholder selain aktor.
            if ($wasDirty) {
                $this->notifications->sendToMany(
                    $this->notifications->stakeholders($ticket),
                    NotificationType::TICKET_UPDATED,
                    $ticket,
                    [],
                    $actor,
                );
            }

            return $ticket->fresh();
        });
    }

    public function assign(Ticket $ticket, User $technician, User $actor, Request $request, bool $isLead = true): Ticket
    {
        return DB::transaction(function () use ($ticket, $technician, $actor, $request, $isLead) {
            $previousAssignee = $ticket->assignee;

            $ticket->assigned_to = $technician->id;

            if ($ticket->status === TicketStatus::OPEN) {
                $this->transitionStatus($ticket, TicketStatus::ASSIGNED, $actor, silent: true);
            }

            $ticket->save();

            $ticket->technicians()->syncWithoutDetaching([
                $technician->id => ['is_lead' => $isLead, 'assigned_at' => now()],
            ]);

            if ($previousAssignee && $previousAssignee->id !== $technician->id) {
                $this->activity->reassigned($ticket, $actor, $previousAssignee, $technician);
            } else {
                $this->activity->assigned($ticket, $actor, $technician);
            }

            $this->auditLogger->log(AuditAction::ASSIGN, $actor, $request, $ticket, null, [
                'assigned_to' => $technician->id,
            ]);

            // Trigger: Ticket diassign — beri tahu teknisi yang ditugaskan.
            $this->notifications->send($technician, NotificationType::TICKET_ASSIGNED, $ticket, [], $actor);

            return $ticket->fresh();
        });
    }

    public function accept(Ticket $ticket, User $actor): Ticket
    {
        $this->transitionStatus($ticket, TicketStatus::ACCEPTED, $actor);
        $ticket->accepted_at = now();
        $ticket->save();
        $this->activity->accepted($ticket, $actor);

        return $ticket->fresh();
    }

    public function transitionStatus(Ticket $ticket, TicketStatus $target, User $actor, bool $silent = false): Ticket
    {
        if (! $ticket->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'status' => "Tidak bisa mengubah status dari {$ticket->status->label()} ke {$target->label()}.",
            ]);
        }

        $from = $ticket->status;
        $ticket->status = $target;

        match ($target) {
            TicketStatus::RESOLVED => $ticket->resolved_at = now(),
            TicketStatus::CLOSED => $ticket->closed_at = now(),
            default => null,
        };

        $ticket->is_sla_breached = ! in_array($target, [TicketStatus::RESOLVED, TicketStatus::CLOSED, TicketStatus::ARCHIVED], true)
            && $ticket->due_at && $ticket->due_at->isPast();

        $ticket->save();

        if (! $silent) {
            $this->activity->statusChanged($ticket, $actor, $from->label(), $target->label());

            // Trigger: Status berubah — beri tahu creator & watcher.
            $this->notifications->sendToMany(
                collect([$ticket->creator])->merge($ticket->watchers)->filter()->unique('id'),
                NotificationType::STATUS_CHANGED,
                $ticket,
                ['from' => $from->label(), 'to' => $target->label()],
                $actor,
            );
        }

        if ($target === TicketStatus::CLOSED) {
            $this->activity->closed($ticket, $actor);
        } elseif ($target === TicketStatus::REOPENED) {
            $this->activity->reopened($ticket, $actor);
        }

        return $ticket->fresh();
    }

    public function close(Ticket $ticket, User $actor): Ticket
    {
        return $this->transitionStatus($ticket, TicketStatus::CLOSED, $actor);
    }

    public function reopen(Ticket $ticket, User $actor): Ticket
    {
        return $this->transitionStatus($ticket, TicketStatus::REOPENED, $actor);
    }

    public function archive(Ticket $ticket, User $actor): Ticket
    {
        $ticket->is_archived = true;
        $ticket->status = TicketStatus::ARCHIVED;
        $ticket->save();
        $this->activity->archived($ticket, $actor);

        return $ticket->fresh();
    }

    public function delete(Ticket $ticket, User $actor, Request $request): void
    {
        $ticket->delete();
        $this->auditLogger->log(AuditAction::DELETE, $actor, $request, $ticket);
    }

    public function restore(Ticket $ticket, User $actor, Request $request): Ticket
    {
        $ticket->restore();
        $this->activity->restored($ticket, $actor);
        $this->auditLogger->log(AuditAction::RESTORE, $actor, $request, $ticket);

        return $ticket->fresh();
    }

    public function duplicate(Ticket $original, User $actor, Request $request): Ticket
    {
        return DB::transaction(function () use ($original, $actor, $request) {
            $copy = Ticket::create([
                'ticket_number' => $this->generateTicketNumber(),
                'subject' => $original->subject.' (Duplikat)',
                'description' => $original->description,
                'category_id' => $original->category_id,
                'department_id' => $original->department_id,
                'priority' => $original->priority->value,
                'status' => TicketStatus::OPEN->value,
                'sla_id' => $original->sla_id,
                'created_by' => $actor->id,
                'due_at' => now()->addMinutes(
                    $original->sla?->resolution_time_minutes ?? $original->priority->defaultSlaHours() * 60
                ),
                'duplicate_of_id' => $original->id,
            ]);

            $copy->tags()->sync($original->tags()->pluck('tags.id'));

            $this->activity->created($copy, $actor);
            $this->activity->duplicated($original, $copy, $actor);
            $this->auditLogger->log(AuditAction::CREATE, $actor, $request, $copy, null, ['duplicate_of' => $original->id]);

            return $copy;
        });
    }

    public function merge(Ticket $ticket, Ticket $into, User $actor, Request $request): Ticket
    {
        return DB::transaction(function () use ($ticket, $into, $actor, $request) {
            $ticket->merged_into_id = $into->id;
            $ticket->status = TicketStatus::ARCHIVED->value;
            $ticket->is_archived = true;
            $ticket->save();

            $this->activity->merged($ticket, $actor, $into);
            $this->auditLogger->log(AuditAction::UPDATE, $actor, $request, $ticket, null, ['merged_into_id' => $into->id]);

            return $ticket->fresh();
        });
    }

    public function rate(Ticket $ticket, int $rating, ?string $feedback, User $actor): Ticket
    {
        $ticket->update(['rating' => $rating, 'feedback' => $feedback]);
        $this->activity->rated($ticket, $actor, $rating);

        return $ticket->fresh();
    }

    public function toggleWatcher(Ticket $ticket, User $user): bool
    {
        if ($ticket->watchers()->where('user_id', $user->id)->exists()) {
            $ticket->watchers()->detach($user->id);

            return false;
        }

        $ticket->watchers()->attach($user->id);
        $this->activity->watcherAdded($ticket, $user, $user);

        return true;
    }

    public function toggleBookmark(Ticket $ticket, User $user): bool
    {
        if ($ticket->bookmarkedBy()->where('user_id', $user->id)->exists()) {
            $ticket->bookmarkedBy()->detach($user->id);

            return false;
        }

        $ticket->bookmarkedBy()->attach($user->id);

        return true;
    }

    public function findPossibleDuplicates(string $subject, int $creatorId): \Illuminate\Support\Collection
    {
        return Ticket::where('created_by', $creatorId)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotIn('status', [TicketStatus::CLOSED, TicketStatus::ARCHIVED])
            ->get()
            ->filter(fn (Ticket $t) => similar_text(strtolower($t->subject), strtolower($subject)) / max(strlen($subject), 1) > 0.6)
            ->values();
    }

    private function storeAttachment(Ticket $ticket, $file, User $uploader, ?int $commentId = null): void
    {
        $path = $file->store('ticket-attachments/'.$ticket->id, 'public');

        $ticket->attachments()->create([
            'ticket_comment_id' => $commentId,
            'uploaded_by' => $uploader->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size_bytes' => $file->getSize(),
        ]);

        $this->activity->attachmentAdded($ticket, $uploader, $file->getClientOriginalName());
    }
}