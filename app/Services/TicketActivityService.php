<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Models\Ticket;
use App\Models\User;

/**
 * Satu-satunya tempat yang menulis ke tabel `ticket_activities`.
 * Deskripsi dibuat otomatis dari ActivityType::description(), tidak pernah
 * diketik manual dari Controller — supaya timeline selalu konsisten.
 */
class TicketActivityService
{
    public function log(Ticket $ticket, ActivityType $type, ?User $actor = null, array $meta = [], ?string $overrideDescription = null): void
    {
        $ticket->activities()->create([
            'user_id' => $actor?->id,
            'type' => $type,
            'description' => $overrideDescription ?? $type->description(),
            'meta' => $meta ?: null,
        ]);
    }

    public function created(Ticket $ticket, User $actor): void
    {
        $this->log($ticket, ActivityType::CREATED, $actor, [], "membuat ticket #{$ticket->ticket_number}");
    }

    public function assigned(Ticket $ticket, User $actor, User $technician): void
    {
        $this->log($ticket, ActivityType::ASSIGNED, $actor, ['assigned_to' => $technician->id],
            "menugaskan {$technician->name} untuk menangani ticket ini");
    }

    public function reassigned(Ticket $ticket, User $actor, User $from, User $to): void
    {
        $this->log($ticket, ActivityType::REASSIGNED, $actor, ['from' => $from->id, 'to' => $to->id],
            "menugaskan ulang dari {$from->name} ke {$to->name}");
    }

    public function accepted(Ticket $ticket, User $actor): void
    {
        $this->log($ticket, ActivityType::ACCEPTED, $actor);
    }

    public function statusChanged(Ticket $ticket, User $actor, string $from, string $to): void
    {
        $this->log($ticket, ActivityType::STATUS_CHANGED, $actor, ['from' => $from, 'to' => $to],
            "mengubah status dari {$from} menjadi {$to}");
    }

    public function priorityChanged(Ticket $ticket, User $actor, string $from, string $to): void
    {
        $this->log($ticket, ActivityType::PRIORITY_CHANGED, $actor, ['from' => $from, 'to' => $to],
            "mengubah prioritas dari {$from} menjadi {$to}");
    }

    public function commented(Ticket $ticket, User $actor, bool $isInternal): void
    {
        $this->log($ticket, $isInternal ? ActivityType::INTERNAL_NOTE_ADDED : ActivityType::COMMENTED, $actor);
    }

    public function attachmentAdded(Ticket $ticket, User $actor, string $filename): void
    {
        $this->log($ticket, ActivityType::ATTACHMENT_ADDED, $actor, ['file' => $filename],
            "menambahkan lampiran \"{$filename}\"");
    }

    public function merged(Ticket $ticket, User $actor, Ticket $into): void
    {
        $this->log($ticket, ActivityType::MERGED, $actor, ['merged_into' => $into->id],
            "menggabungkan ticket ini ke #{$into->ticket_number}");
    }

    public function duplicated(Ticket $original, Ticket $duplicate, User $actor): void
    {
        $this->log($duplicate, ActivityType::DUPLICATED, $actor, ['duplicate_of' => $original->id],
            "ticket ini adalah duplikat dari #{$original->ticket_number}");
    }

    public function closed(Ticket $ticket, User $actor): void
    {
        $this->log($ticket, ActivityType::CLOSED, $actor);
    }

    public function reopened(Ticket $ticket, User $actor): void
    {
        $this->log($ticket, ActivityType::REOPENED, $actor);
    }

    public function archived(Ticket $ticket, User $actor): void
    {
        $this->log($ticket, ActivityType::ARCHIVED, $actor);
    }

    public function restored(Ticket $ticket, User $actor): void
    {
        $this->log($ticket, ActivityType::RESTORED, $actor);
    }

    public function rated(Ticket $ticket, User $actor, int $rating): void
    {
        $this->log($ticket, ActivityType::RATED, $actor, ['rating' => $rating],
            "memberikan rating {$rating} bintang");
    }

    public function escalated(Ticket $ticket, string $reason): void
    {
        $this->log($ticket, ActivityType::ESCALATED, null, ['reason' => $reason],
            "eskalasi otomatis: {$reason}");
    }

    public function watcherAdded(Ticket $ticket, User $actor, User $watcher): void
    {
        $this->log($ticket, ActivityType::WATCHER_ADDED, $actor, ['watcher' => $watcher->id],
            "menambahkan {$watcher->name} sebagai watcher");
    }
}
