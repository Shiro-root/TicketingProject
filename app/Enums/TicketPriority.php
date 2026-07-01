<?php

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Design-token color name (maps to frontend badge styling). */
    public function color(): string
    {
        return match ($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'blue',
            self::HIGH => 'orange',
            self::CRITICAL => 'red',
        };
    }

    /** Default SLA resolution time in hours, used as fallback if no SLA record matches. */
    public function defaultSlaHours(): int
    {
        return match ($this) {
            self::LOW => 72,
            self::MEDIUM => 24,
            self::HIGH => 6,
            self::CRITICAL => 2,
        };
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
