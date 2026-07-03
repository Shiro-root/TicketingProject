<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    private array $permissions = [
        'ticket' => ['ticket.view', 'ticket.view_all', 'ticket.create', 'ticket.update', 'ticket.delete',
            'ticket.assign', 'ticket.close', 'ticket.reopen', 'ticket.archive', 'ticket.merge'],
        'user' => ['user.view', 'user.create', 'user.update', 'user.delete'],
        'department' => ['department.view', 'department.manage'],
        'category' => ['category.view', 'category.manage'],
        'asset' => ['asset.view', 'asset.manage'],
        'knowledge_base' => ['kb.view', 'kb.manage'],
        'report' => ['report.view', 'report.export'],
        'approval' => ['approval.manage', 'approval.decide'],
        'audit' => ['audit.view'],
        'settings' => ['settings.manage'],
    ];

    public function run(): void
    {
        foreach ($this->permissions as $group => $slugs) {
            foreach ($slugs as $slug) {
                Permission::firstOrCreate(['slug' => $slug], [
                    'name' => ucfirst(str_replace(['.', '_'], [' - ', ' '], $slug)),
                    'group' => $group,
                ]);
            }
        }

        $allPermissions = Permission::pluck('id')->all();
        $ticketOnly = Permission::whereIn('slug', [
            'ticket.view', 'ticket.create', 'ticket.update', 'ticket.assign', 'ticket.close', 'ticket.reopen',
            'kb.view',
        ])->pluck('id')->all();
        $viewOwnTicket = Permission::whereIn('slug', ['ticket.view', 'ticket.create', 'kb.view'])->pluck('id')->all();

        $roles = [
            UserRole::SUPER_ADMIN->value => ['name' => 'Super Admin', 'is_system' => true, 'permissions' => $allPermissions],
            UserRole::ADMIN->value => ['name' => 'Admin', 'is_system' => true, 'permissions' => $allPermissions],
            UserRole::MANAGER->value => ['name' => 'Manager', 'is_system' => true, 'permissions' => Permission::whereIn('slug', [
                'ticket.view', 'ticket.view_all', 'ticket.assign', 'ticket.close', 'ticket.reopen', 'ticket.archive',
                'report.view', 'report.export', 'approval.decide', 'user.view', 'kb.view', 'kb.manage',
            ])->pluck('id')->all()],
            UserRole::SUPERVISOR->value => ['name' => 'Supervisor', 'is_system' => true, 'permissions' => Permission::whereIn('slug', [
                'ticket.view', 'ticket.view_all', 'ticket.assign', 'ticket.close', 'ticket.reopen',
                'report.view', 'approval.decide', 'kb.view', 'kb.manage',
            ])->pluck('id')->all()],
            UserRole::TECHNICIAN->value => ['name' => 'Technician', 'is_system' => true, 'permissions' => $ticketOnly],
            UserRole::EMPLOYEE->value => ['name' => 'Employee', 'is_system' => true, 'permissions' => $viewOwnTicket],
            UserRole::GUEST->value => ['name' => 'Guest', 'is_system' => true, 'permissions' => Permission::whereIn('slug', ['ticket.create', 'ticket.view', 'kb.view'])->pluck('id')->all()],
        ];

        foreach ($roles as $slug => $data) {
            $role = Role::firstOrCreate(['slug' => $slug], [
                'name' => $data['name'],
                'is_system' => $data['is_system'],
                'description' => $data['name'].' role',
            ]);
            $role->permissions()->sync($data['permissions']);
        }
    }
}