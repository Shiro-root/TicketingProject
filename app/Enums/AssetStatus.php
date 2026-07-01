<?php

namespace App\Enums;

enum AssetStatus: string
{
    case AVAILABLE = 'available';
    case IN_USE = 'in_use';
    case UNDER_MAINTENANCE = 'under_maintenance';
    case RETIRED = 'retired';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::IN_USE => 'In Use',
            self::UNDER_MAINTENANCE => 'Under Maintenance',
            self::RETIRED => 'Retired',
            self::LOST => 'Lost',
        };
    }
}
