<?php

namespace App\Enums;

enum AssetType: string
{
    case LAPTOP = 'laptop';
    case DESKTOP = 'desktop';
    case PRINTER = 'printer';
    case MONITOR = 'monitor';
    case ROUTER = 'router';
    case SWITCH = 'switch';
    case SERVER = 'server';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LAPTOP => 'Laptop',
            self::DESKTOP => 'Desktop',
            self::PRINTER => 'Printer',
            self::MONITOR => 'Monitor',
            self::ROUTER => 'Router',
            self::SWITCH => 'Switch',
            self::SERVER => 'Server',
            self::OTHER => 'Other',
        };
    }
}
