<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Mengumpulkan seluruh data statistik/grafik/widget untuk Dashboard.
 * Dipisah dari Controller agar mudah diuji & dipakai ulang (mis. API/report nanti).
 */
class DashboardService
{
    private const TTL = 60; // detik — dashboard tidak perlu realtime ketat

    /** Dashboard lengkap untuk staff (Technician ke atas). */
    public function overview(User $user): array
    {
        return Cache::remember('dashboard:overview', self::TTL, function () use ($user) {
            return [
                'statusCounts' => $this->statusCounts(),
                'periodStats' => $this->periodStats(),
                'monthlyChart' => $this->monthlyChart(),
                'categoryChart' => $this->categoryChart(),
                'priorityChart' => $this->priorityChart(),
                'technicianChart' => $this->technicianChart(),
                'bestTechnicians' => $this->bestTechnicians(),
                'overdueTickets' => $this->overdueTickets(),
                'slaPerformance' => $this->slaPerformance(),
                'latestTickets' => $this->latestTickets(),
                'latestActivities' => $this->latestActivities(),
                'managerStats' => $this->canSeeManagerStats($user) ? $this->managerStats() : null,
            ];
        });
    }

    private function canSeeManagerStats(User $user): bool
    {
        return $user->hasRole(
            UserRole::MANAGER->value,
            UserRole::SUPERVISOR->value,
            UserRole::ADMIN->value,
            UserRole::SUPER_ADMIN->value,
        );
    }

    /** Kartu jumlah tiket per status. */
    public function statusCounts(): array
    {
        $counts = Ticket::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $wanted = [
            TicketStatus::OPEN, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS,
            TicketStatus::PENDING_VENDOR, TicketStatus::WAITING_USER,
            TicketStatus::RESOLVED, TicketStatus::CLOSED,
        ];

        $result = [];
        foreach ($wanted as $status) {
            $result[$status->value] = [
                'label' => $status->label(),
                'color' => $status->color(),
                'total' => (int) ($counts[$status->value] ?? 0),
            ];
        }

        return [
            'total' => (int) $counts->sum(),
            'items' => $result,
        ];
    }

    /** Statistik jumlah tiket dibuat: hari ini / minggu ini / bulan ini / tahun ini. */
    public function periodStats(): array
    {
        $now = now();

        return [
            'today' => Ticket::whereDate('created_at', $now->toDateString())->count(),
            'this_week' => Ticket::whereBetween('created_at', [
                $now->clone()->startOfWeek(), $now->clone()->endOfWeek(),
            ])->count(),
            'this_month' => Ticket::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)->count(),
            'this_year' => Ticket::whereYear('created_at', $now->year)->count(),
        ];
    }

    /** Tiket per bulan, 12 bulan terakhir (dihitung di PHP agar portable lintas driver DB). */
    public function monthlyChart(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'label' => $date->translatedFormat('M Y'),
                'total' => Ticket::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
            ];
        }

        return $months;
    }

    /** Tiket per kategori. */
    public function categoryChart(): array
    {
        return Category::query()
            ->withCount('tickets')
            ->orderByDesc('tickets_count')
            ->get(['id', 'name', 'color'])
            ->map(fn ($c) => ['label' => $c->name, 'color' => $c->color, 'total' => $c->tickets_count])
            ->all();
    }

    /** Tiket per prioritas, urut Low → Critical. */
    public function priorityChart(): array
    {
        $counts = Ticket::query()
            ->selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        return collect(TicketPriority::cases())->map(fn (TicketPriority $p) => [
            'label' => $p->label(),
            'color' => $p->color(),
            'total' => (int) ($counts[$p->value] ?? 0),
        ])->all();
    }

    /** Tiket per teknisi (berdasarkan assigned_to). */
    public function technicianChart(): array
    {
        return User::query()
            ->whereHas('ticketsAssigned')
            ->withCount('ticketsAssigned')
            ->orderByDesc('tickets_assigned_count')
            ->limit(8)
            ->get(['id', 'name'])
            ->map(fn ($u) => ['label' => $u->name, 'total' => $u->tickets_assigned_count])
            ->all();
    }

    /** Ranking teknisi terbaik: jumlah tiket selesai + rata-rata rating. */
    public function bestTechnicians(int $limit = 5): array
    {
        $done = [TicketStatus::RESOLVED->value, TicketStatus::CLOSED->value];

        return User::query()
            ->withCount(['ticketsAssigned as resolved_count' => fn ($q) => $q->whereIn('status', $done)])
            ->withAvg(['ticketsAssigned as avg_rating' => fn ($q) => $q->whereNotNull('rating')], 'rating')
            ->get(['id', 'name', 'avatar'])
            ->filter(fn ($u) => $u->resolved_count > 0)
            ->sortByDesc(fn ($u) => [$u->resolved_count, $u->avg_rating])
            ->take($limit)
            ->values()
            ->all();
    }

    /** Tiket overdue saat ini (belum selesai & due_at terlewati). */
    public function overdueTickets(int $limit = 10)
    {
        return Ticket::overdue()
            ->with(['category', 'assignee'])
            ->orderBy('due_at')
            ->limit($limit)
            ->get();
    }

    /** Persentase kepatuhan SLA dari tiket yang sudah resolved. */
    public function slaPerformance(): array
    {
        $resolved = Ticket::whereNotNull('resolved_at')->whereNotNull('due_at')->count();
        $metSla = Ticket::whereNotNull('resolved_at')->whereNotNull('due_at')
            ->whereColumn('resolved_at', '<=', 'due_at')->count();

        return [
            'resolved_total' => $resolved,
            'met_sla' => $metSla,
            'breached' => $resolved - $metSla,
            'percentage' => $resolved > 0 ? round(($metSla / $resolved) * 100, 1) : 0.0,
            'currently_overdue' => Ticket::overdue()->count(),
        ];
    }

    public function latestTickets(int $limit = 8)
    {
        return Ticket::with(['category', 'assignee', 'creator'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function latestActivities(int $limit = 10)
    {
        return TicketActivity::with(['ticket', 'user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** Widget khusus Manager+: rata-rata waktu penyelesaian & beban per divisi. */
    public function managerStats(): array
    {
        $resolvedTickets = Ticket::whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);

        $avgResolutionHours = $resolvedTickets->isNotEmpty()
            ? round($resolvedTickets->avg(fn ($t) => $t->created_at->diffInHours($t->resolved_at)), 1)
            : 0.0;

        $byDepartment = Department::withCount('tickets')
            ->orderByDesc('tickets_count')
            ->get(['id', 'name'])
            ->map(fn ($d) => ['label' => $d->name, 'total' => $d->tickets_count])
            ->all();

        return [
            'avg_resolution_hours' => $avgResolutionHours,
            'by_department' => $byDepartment,
        ];
    }

    // ── Dashboard ringkas untuk Employee / Guest ──────────────────────

    public function myOverview(User $user): array
    {
        $base = Ticket::where('created_by', $user->id);

        return [
            'statusCounts' => [
                'total' => (clone $base)->count(),
                'open' => (clone $base)->whereIn('status', [
                    TicketStatus::OPEN->value, TicketStatus::ASSIGNED->value, TicketStatus::ACCEPTED->value,
                    TicketStatus::IN_PROGRESS->value, TicketStatus::WAITING_USER->value, TicketStatus::PENDING_VENDOR->value,
                ])->count(),
                'resolved' => (clone $base)->where('status', TicketStatus::RESOLVED->value)->count(),
                'closed' => (clone $base)->where('status', TicketStatus::CLOSED->value)->count(),
            ],
            'latestTickets' => Ticket::where('created_by', $user->id)
                ->with(['category', 'assignee'])
                ->latest()
                ->limit(8)
                ->get(),
        ];
    }
}
