<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Support\Collection;

/**
 * Satu pintu untuk mengirim notifikasi (in-app + email) terkait ticket.
 * Menghormati preferensi per-user di `notification_settings` — default AKTIF
 * (in_app & email) kalau user belum pernah mengatur preferensinya.
 */
class NotificationService
{
    public function send(User $recipient, NotificationType $type, Ticket $ticket, array $data = [], ?User $actor = null): void
    {
        // Tidak perlu memberi tahu diri sendiri atas aksinya sendiri
        // (mis. teknisi mengubah status ticket miliknya sendiri).
        if ($actor && $actor->id === $recipient->id) {
            return;
        }

        $setting = $recipient->notificationSettings()->where('type', $type->value)->first();

        $wantsInApp = $setting?->in_app ?? true;
        $wantsEmail = $setting?->email ?? true;

        if (! $wantsInApp && ! $wantsEmail) {
            return;
        }

        $recipient->notify(new TicketNotification($type, $ticket, $data, $actor, $wantsInApp, $wantsEmail));
    }

    /** Kirim ke banyak penerima sekaligus, dedup otomatis berdasarkan user id. */
    public function sendToMany(iterable $recipients, NotificationType $type, Ticket $ticket, array $data = [], ?User $actor = null): void
    {
        $seen = [];

        foreach ($recipients as $recipient) {
            if (! $recipient || isset($seen[$recipient->id])) {
                continue;
            }
            $seen[$recipient->id] = true;
            $this->send($recipient, $type, $ticket, $data, $actor);
        }
    }

    /** Stakeholder ticket yang relevan: creator, assignee, tim teknisi, watcher. */
    public function stakeholders(Ticket $ticket): Collection
    {
        $ticket->loadMissing(['creator', 'assignee', 'technicians', 'watchers']);

        return collect([$ticket->creator, $ticket->assignee])
            ->merge($ticket->technicians)
            ->merge($ticket->watchers)
            ->filter()
            ->unique('id')
            ->values();
    }
}