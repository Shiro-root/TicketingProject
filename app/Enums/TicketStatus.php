<?php

namespace App\Enums;

/**
 * Ticket status workflow:
 * Open -> Assigned -> Accepted -> In Progress -> Waiting User -> Pending Vendor -> Resolved -> Closed
 */
enum TicketStatus: string
{
    case OPEN = "open";
    case ASSIGNED = "assigned";
    case ACCEPTED = "accepted";
    case IN_PROGRESS = "in_progress";
    case WAITING_USER = "waiting_user";
    case PENDING_VENDOR = "pending_vendor";
    case RESOLVED = "resolved";
    case CLOSED = "closed";
    case REOPENED = "reopened";
    case ARCHIVED = "archived";

    public function label(): string
    {
        return match ($this) {
            self::OPEN => "Open",
            self::ASSIGNED => "Assigned",
            self::ACCEPTED => "Accepted",
            self::IN_PROGRESS => "In Progress",
            self::WAITING_USER => "Waiting User",
            self::PENDING_VENDOR => "Pending Vendor",
            self::RESOLVED => "Resolved",
            self::CLOSED => "Closed",
            self::REOPENED => "Reopened",
            self::ARCHIVED => "Archived",
        };
    }

    /** Color token - must map to design.md tokens when rendered on the frontend. */
    public function color(): string
    {
        return match ($this) {
            self::OPEN => "blue",
            self::ASSIGNED => "purple",
            self::ACCEPTED => "indigo",
            self::IN_PROGRESS => "amber",
            self::WAITING_USER => "orange",
            self::PENDING_VENDOR => "orange",
            self::RESOLVED => "green",
            self::CLOSED => "gray",
            self::REOPENED => "red",
            self::ARCHIVED => "gray",
        };
    }

    /** Valid next statuses from the current status (enforces workflow order). */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::OPEN => [self::ASSIGNED, self::ARCHIVED],
            self::ASSIGNED => [self::ACCEPTED, self::OPEN],
            self::ACCEPTED => [self::IN_PROGRESS],
            self::IN_PROGRESS => [self::WAITING_USER, self::PENDING_VENDOR, self::RESOLVED],
            self::WAITING_USER => [self::IN_PROGRESS, self::RESOLVED],
            self::PENDING_VENDOR => [self::IN_PROGRESS, self::RESOLVED],
            self::RESOLVED => [self::CLOSED, self::REOPENED],
            self::CLOSED => [self::REOPENED, self::ARCHIVED],
            self::REOPENED => [self::ASSIGNED, self::IN_PROGRESS],
            self::ARCHIVED => [],
        };
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
