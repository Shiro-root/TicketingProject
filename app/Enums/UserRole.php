<?php

namespace App\Enums;

/**
 * Canonical role slugs. Actual roles/permissions are stored in the `roles` and
 * `permissions` tables (RBAC), but this enum gives type-safe references for
 * seeders, policies, and gate checks.
 */
enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case SUPERVISOR = 'supervisor';
    case TECHNICIAN = 'technician';
    case EMPLOYEE = 'employee';
    case GUEST = 'guest';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::SUPERVISOR => 'Supervisor',
            self::TECHNICIAN => 'Technician',
            self::EMPLOYEE => 'Employee',
            self::GUEST => 'Guest',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
