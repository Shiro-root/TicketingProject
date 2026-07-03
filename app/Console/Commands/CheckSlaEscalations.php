<?php

namespace App\Console\Commands;

use App\Enums\ActivityType;
use App\Enums\NotificationType;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\NotificationService;
use App\Services\TicketActivityService;
use Illuminate\Console\Command;

/**
 * Auto Escalation — dijadwalkan lewat routes/console.php (setiap 15 menit).
 *
 * 1) SLA_WARNING dikirim sekali saat sisa waktu ticket < 60 menit.
 * 2) SLA_BREACHED dikirim sekali + is_sla_breached ditandai + activity ESCALATED
 *    dicatat saat ticket resmi melewati due_at.
 *
 * "Sekali" dijaga lewat riwayat `ticket_activities` (tipe ESCALATED) supaya
 * tidak perlu menambah kolom/migration baru di tabel tickets.
 */
class CheckSlaEscalations extends Command
{
    protected $signature = 'tickets:check-sla';

    protected $description = 'Cek ticket mendekati/melewati SLA, kirim notifikasi, dan eskalasi otomatis.';

    private const WARNING_WINDOW_MINUTES = 60;

    public function __construct(
        private readonly NotificationService $notifications,
        private readonly TicketActivityService $activity,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $openStatuses = array_map(fn ($s) => $s->value, [
            TicketStatus::OPEN, TicketStatus::ASSIGNED, TicketStatus::ACCEPTED, TicketStatus::IN_PROGRESS,
            TicketStatus::WAITING_USER, TicketStatus::PENDING_VENDOR, TicketStatus::REOPENED,
        ]);

        $candidates = Ticket::query()
            ->whereIn('status', $openStatuses)
            ->whereNotNull('due_at')
            ->with(['creator', 'assignee', 'watchers', 'technicians', 'activities'])
            ->get();

        $warned = 0;
        $breached = 0;

        foreach ($candidates as $ticket) {
            $minutesLeft = now()->diffInMinutes($ticket->due_at, false);

            if ($ticket->due_at->isPast()) {
                if (! $ticket->is_sla_breached) {
                    $ticket->is_sla_breached = true;
                    $ticket->save();
                }

                if (! $this->alreadyEscalated($ticket, 'breached')) {
                    $this->activity->escalated($ticket, 'SLA terlewati, ticket dieskalasi otomatis (breached)');
                    $this->notifications->sendToMany(
                        $this->notifications->stakeholders($ticket),
                        NotificationType::SLA_BREACHED,
                        $ticket,
                    );
                    $breached++;
                }

                continue;
            }

            if ($minutesLeft <= self::WARNING_WINDOW_MINUTES && ! $this->alreadyEscalated($ticket, 'warning')) {
                $this->activity->escalated($ticket, 'peringatan SLA — sisa waktu kurang dari 1 jam (warning)');
                $this->notifications->sendToMany(
                    $this->notifications->stakeholders($ticket),
                    NotificationType::SLA_WARNING,
                    $ticket,
                    ['time_remaining' => max($minutesLeft, 0).' menit'],
                );
                $warned++;
            }
        }

        $this->info("SLA check selesai — {$warned} peringatan, {$breached} eskalasi dikirim.");

        return self::SUCCESS;
    }

    /** Cegah notifikasi/eskalasi berulang untuk marker yang sama ('warning' atau 'breached'). */
    private function alreadyEscalated(Ticket $ticket, string $marker): bool
    {
        return $ticket->activities
            ->where('type', ActivityType::ESCALATED)
            ->contains(fn ($a) => str_contains($a->meta['reason'] ?? '', "({$marker})"));
    }
}