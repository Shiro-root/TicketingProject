<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'frequency', 'period_days', 'department_id', 'category_id',
        'status', 'priority', 'format', 'recipients', 'is_active', 'last_sent_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'is_active' => 'boolean',
            'last_sent_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Kapan seharusnya jadwal ini berikutnya dikirim, dihitung dari last_sent_at (atau created_at kalau belum pernah). */
    public function nextRunAt(): Carbon
    {
        $base = $this->last_sent_at ?? $this->created_at;

        return match ($this->frequency) {
            'daily' => $base->copy()->addDay(),
            'weekly' => $base->copy()->addWeek(),
            'monthly' => $base->copy()->addMonthNoOverflow(),
            default => $base->copy()->addDay(),
        };
    }

    public function isDue(): bool
    {
        return $this->is_active && now()->greaterThanOrEqualTo($this->nextRunAt());
    }

    /** Filter yang dipakai ReportService, dihitung ulang tiap kali dikirim (period_days bersifat rolling). */
    public function toReportFilters(): array
    {
        return array_filter([
            'date_from' => $this->period_days ? now()->subDays($this->period_days)->toDateString() : null,
            'department_id' => $this->department_id,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'priority' => $this->priority,
        ]);
    }
}
