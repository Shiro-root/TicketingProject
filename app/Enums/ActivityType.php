<?php

namespace App\Enums;

/** Used for the ticket activity timeline (auto-generated, never manually written). */
enum ActivityType: string
{
    case CREATED = 'created';
    case ASSIGNED = 'assigned';
    case REASSIGNED = 'reassigned';
    case ACCEPTED = 'accepted';
    case STATUS_CHANGED = 'status_changed';
    case PRIORITY_CHANGED = 'priority_changed';
    case COMMENTED = 'commented';
    case INTERNAL_NOTE_ADDED = 'internal_note_added';
    case ATTACHMENT_ADDED = 'attachment_added';
    case MERGED = 'merged';
    case DUPLICATED = 'duplicated';
    case CLOSED = 'closed';
    case REOPENED = 'reopened';
    case ARCHIVED = 'archived';
    case RESTORED = 'restored';
    case RATED = 'rated';
    case ESCALATED = 'escalated';
    case WATCHER_ADDED = 'watcher_added';
    case APPROVAL_REQUESTED = 'approval_requested';
    case APPROVAL_DECIDED = 'approval_decided';

    public function description(): string
    {
        return match ($this) {
            self::CREATED => 'membuat ticket',
            self::ASSIGNED => 'menugaskan teknisi',
            self::REASSIGNED => 'menugaskan ulang teknisi',
            self::ACCEPTED => 'menerima ticket',
            self::STATUS_CHANGED => 'mengubah status',
            self::PRIORITY_CHANGED => 'mengubah prioritas',
            self::COMMENTED => 'menambahkan komentar',
            self::INTERNAL_NOTE_ADDED => 'menambahkan catatan internal',
            self::ATTACHMENT_ADDED => 'menambahkan lampiran',
            self::MERGED => 'menggabungkan ticket',
            self::DUPLICATED => 'menduplikasi ticket',
            self::CLOSED => 'menutup ticket',
            self::REOPENED => 'membuka kembali ticket',
            self::ARCHIVED => 'mengarsipkan ticket',
            self::RESTORED => 'memulihkan ticket',
            self::RATED => 'memberikan rating',
            self::ESCALATED => 'melakukan eskalasi ticket',
            self::WATCHER_ADDED => 'menambahkan watcher',
            self::APPROVAL_REQUESTED => 'meminta persetujuan',
            self::APPROVAL_DECIDED => 'memutuskan persetujuan',
        };
    }
}
