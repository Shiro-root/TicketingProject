<?php

namespace App\Models;

use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'position', 'division',
        'role_id', 'department_id', 'status', 'avatar', 'locale', 'theme',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    // ── Relations ──────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** Tickets this user created (as Employee/Guest). */
    public function ticketsCreated(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    /** Tickets this user is the primary assignee of. */
    public function ticketsAssigned(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /** All tickets where this user is a co-technician (multiple technician support). */
    public function ticketsAsTechnician(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_technician')
            ->withPivot('is_lead', 'assigned_at')
            ->withTimestamps();
    }

    public function watchedTickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_watchers')->withTimestamps();
    }

    public function bookmarkedTickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_bookmarks')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_to');
    }

    public function savedFilters(): HasMany
    {
        return $this->hasMany(SavedFilter::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notificationSettings(): HasMany
    {
        return $this->hasMany(NotificationSetting::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function hasRole(string ...$slugs): bool
    {
        return in_array($this->role?->slug, $slugs, true);
    }

    public function hasPermission(string $slug): bool
    {
        return $this->role?->hasPermission($slug) ?? false;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    public function initials(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = strtoupper(($words[0][0] ?? '').($words[1][0] ?? ''));

        return $initials ?: 'U';
    }
}
