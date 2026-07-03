<?php

namespace App\Services;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Business logic untuk Modul 8: Report.
 * Controller tidak menyentuh Eloquent langsung — konsisten dengan pola
 * TicketService/KnowledgeBaseService/AssetService di modul-modul sebelumnya.
 *
 * Semua method menerima $filters yang sama (hasil dari ReportController::filters())
 * supaya query yang dipakai di layar, PDF, dan Excel selalu identik.
 */
class ReportService
{
    /**
     * Filter yang didukung: date_from, date_to, department_id, category_id,
     * assigned_to (teknisi), status, priority.
     */
    public function filteredQuery(array $filters): Builder
    {
        $query = Ticket::query()->with(['category', 'department', 'assignee', 'creator', 'sla']);

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query;
    }

    /** Kartu ringkasan + data grafik untuk halaman Report dan header PDF. */
    public function summary(array $filters): array
    {
        $base = $this->filteredQuery($filters);

        $total = (clone $base)->count();
        $resolvedTotal = (clone $base)->whereIn('status', [
            TicketStatus::RESOLVED->value, TicketStatus::CLOSED->value,
        ])->count();
        $overdueTotal = (clone $base)->overdue()->count();

        $slaBase = (clone $base)->whereNotNull('resolved_at')->whereNotNull('due_at');
        $resolvedWithSla = (clone $slaBase)->count();
        $metSla = (clone $slaBase)->whereColumn('resolved_at', '<=', 'due_at')->count();

        $statusCounts = (clone $base)->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');
        $priorityCounts = (clone $base)->selectRaw('priority, count(*) as total')
            ->groupBy('priority')->pluck('total', 'priority');

        $resolvedTickets = (clone $base)->whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);
        $avgResolutionHours = $resolvedTickets->isNotEmpty()
            ? round($resolvedTickets->avg(fn ($t) => $t->created_at->diffInHours($t->resolved_at)), 1)
            : 0.0;

        return [
            'total' => $total,
            'resolved_total' => $resolvedTotal,
            'overdue_total' => $overdueTotal,
            'sla_percentage' => $resolvedWithSla > 0 ? round(($metSla / $resolvedWithSla) * 100, 1) : 0.0,
            'avg_resolution_hours' => $avgResolutionHours,
            'by_status' => collect(TicketStatus::cases())->map(fn (TicketStatus $s) => [
                'label' => $s->label(),
                'color' => $s->color(),
                'total' => (int) ($statusCounts[$s->value] ?? 0),
            ])->all(),
            'by_priority' => collect(TicketPriority::cases())->map(fn (TicketPriority $p) => [
                'label' => $p->label(),
                'color' => $p->color(),
                'total' => (int) ($priorityCounts[$p->value] ?? 0),
            ])->all(),
        ];
    }

    /** Kinerja per teknisi (jumlah selesai + rata-rata rating) untuk tabel tambahan di laporan. */
    public function technicianPerformance(array $filters): Collection
    {
        $done = [TicketStatus::RESOLVED->value, TicketStatus::CLOSED->value];

        $ids = $this->filteredQuery($filters)->whereNotNull('assigned_to')->pluck('assigned_to')->unique();

        return User::query()
            ->whereIn('id', $ids)
            ->withCount([
                'ticketsAssigned as assigned_count' => fn ($q) => $this->applyRangeOnly($q, $filters),
                'ticketsAssigned as resolved_count' => fn ($q) => $this->applyRangeOnly($q, $filters)->whereIn('status', $done),
            ])
            ->withAvg(['ticketsAssigned as avg_rating' => fn ($q) => $this->applyRangeOnly($q, $filters)->whereNotNull('rating')], 'rating')
            ->orderByDesc('resolved_count')
            ->get(['id', 'name']);
    }

    /** Baris ticket lengkap untuk tabel di layar, PDF, dan Excel. */
    public function rows(array $filters): Collection
    {
        return $this->filteredQuery($filters)->latest()->get();
    }

    public function filterOptions(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'technicians' => User::whereHas('role', fn ($r) => $r->whereIn('slug', ['technician', 'supervisor']))
                ->orderBy('name')->get(),
        ];
    }

    /** Terapkan hanya filter tanggal + department/kategori (tanpa assigned_to) — dipakai di technicianPerformance(). */
    private function applyRangeOnly($query, array $filters)
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query;
    }
}
