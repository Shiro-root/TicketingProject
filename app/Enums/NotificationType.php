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
}
