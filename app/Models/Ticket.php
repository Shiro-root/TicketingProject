<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number', 'subject', 'description', 'category_id', 'department_id',
        'priority', 'status', 'sla_id', 'created_by', 'assigned_to',
        'due_at', 'first_response_at', 'accepted_at', 'resolved_at', 'closed_at',
        'is_sla_breached', 'is_archived', 'rating', 'feedback',
        'merged_into_id', 'duplicate_of_id',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'accepted_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_sla_breached' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    // ── Relations ──────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function sla(): BelongsTo
    {
        return $this->belongsTo(Sla::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Additional technicians beyond the primary assignee (multiple technician support). */
    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_technician', 'ticket_id', 'user_id')
            ->withPivot('is_lead', 'assigned_at')
            ->withTimestamps();
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_watchers')->withTimestamps();
    }

    public function bookmarkedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_bookmarks')->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ticket_tag')->withTimestamps();
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'ticket_asset')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->latest();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TicketApproval::class);
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'merged_into_id');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'duplicate_of_id');
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED, TicketStatus::ARCHIVED]);
    }

    public function scopeStatus(Builder $query, TicketStatus|string $status): Builder
    {
        return $query->where('status', $status instanceof TicketStatus ? $status->value : $status);
    }

    public function scopePriority(Builder $query, TicketPriority|string $priority): Builder
    {
        return $query->where('priority', $priority instanceof TicketPriority ? $priority->value : $priority);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('is_archived', true);
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->due_at
            && $this->due_at->isPast()
            && ! in_array($this->status, [TicketStatus::RESOLVED, TicketStatus::CLOSED, TicketStatus::ARCHIVED], true);
    }

    public function canTransitionTo(TicketStatus $target): bool
    {
        return in_array($target, $this->status->allowedTransitions(), true);
    }

    public function isRated(): bool
    {
        return ! is_null($this->rating);
    }
}
