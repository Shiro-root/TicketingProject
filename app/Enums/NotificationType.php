<?php

namespace App\Enums;

enum NotificationType: string
{
    case TICKET_CREATED = 'ticket_created';
    case TICKET_ASSIGNED = 'ticket_assigned';
    case TICKET_UPDATED = 'ticket_updated';
    case STATUS_CHANGED = 'status_changed';
    case NEW_COMMENT = 'new_comment';
    case SLA_WARNING = 'sla_warning';
    case SLA_BREACHED = 'sla_breached';
    case APPROVAL_REQUESTED = 'approval_requested';
    case MENTIONED = 'mentioned';

    public function label(): string
    {
        return match ($this) {
            self::TICKET_CREATED => 'Ticket Dibuat',
            self::TICKET_ASSIGNED => 'Ticket Ditugaskan',
            self::TICKET_UPDATED => 'Ticket Diperbarui',
            self::STATUS_CHANGED => 'Status Berubah',
            self::NEW_COMMENT => 'Komentar Baru',
            self::SLA_WARNING => 'SLA Hampir Habis',
            self::SLA_BREACHED => 'SLA Terlewati',
            self::APPROVAL_REQUESTED => 'Persetujuan Diminta',
            self::MENTIONED => 'Disebut (Mention)',
        };
    }

    /** Emoji ringan untuk dropdown/list notifikasi — tidak butuh icon-set tambahan. */
    public function icon(): string
    {
        return match ($this) {
            self::TICKET_CREATED => '🎫',
            self::TICKET_ASSIGNED => '👤',
            self::TICKET_UPDATED => '✏️',
            self::STATUS_CHANGED => '🔄',
            self::NEW_COMMENT => '💬',
            self::SLA_WARNING => '⏰',
            self::SLA_BREACHED => '🚨',
            self::APPROVAL_REQUESTED => '✅',
            self::MENTIONED => '📣',
        };
    }

    /** Cocok dengan `email_templates.key` (lihat EmailTemplateSeeder, Modul 1). Null = tidak ada template → kirim mail generik. */
    public function emailTemplateKey(): ?string
    {
        return match ($this) {
            self::TICKET_CREATED => 'ticket_created',
            self::TICKET_ASSIGNED => 'ticket_assigned',
            self::STATUS_CHANGED => 'status_changed',
            self::NEW_COMMENT => 'new_comment',
            self::SLA_WARNING => 'sla_warning',
            self::SLA_BREACHED => 'sla_breached',
            default => null,
        };
    }
}