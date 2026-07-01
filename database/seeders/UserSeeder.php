<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Demo accounts — one per role, all with password "password".
     * Use these to test RBAC behaviour end-to-end after seeding.
     */
    public function run(): void
    {
        $itDept = Department::where('code', 'IT')->first();

        $demoUsers = [
            ['name' => 'Sarah Putri', 'email' => 'superadmin@helpdesk.test', 'role' => UserRole::SUPER_ADMIN, 'position' => 'System Administrator'],
            ['name' => 'Budi Santoso', 'email' => 'admin@helpdesk.test', 'role' => UserRole::ADMIN, 'position' => 'IT Admin'],
            ['name' => 'Dewi Lestari', 'email' => 'manager@helpdesk.test', 'role' => UserRole::MANAGER, 'position' => 'IT Manager'],
            ['name' => 'Rizky Aditya', 'email' => 'supervisor@helpdesk.test', 'role' => UserRole::SUPERVISOR, 'position' => 'IT Supervisor'],
            ['name' => 'Andi Nugraha', 'email' => 'technician@helpdesk.test', 'role' => UserRole::TECHNICIAN, 'position' => 'Support Technician'],
            ['name' => 'Maya Kusuma', 'email' => 'technician2@helpdesk.test', 'role' => UserRole::TECHNICIAN, 'position' => 'Network Technician'],
            ['name' => 'Joko Prasetyo', 'email' => 'employee@helpdesk.test', 'role' => UserRole::EMPLOYEE, 'position' => 'Staff Marketing'],
            ['name' => 'Guest User', 'email' => 'guest@helpdesk.test', 'role' => UserRole::GUEST, 'position' => 'External Vendor'],
        ];

        foreach ($demoUsers as $data) {
            User::firstOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone' => '08'.fake()->numerify('##########'),
                'position' => $data['position'],
                'division' => $itDept?->name,
                'role_id' => Role::where('slug', $data['role']->value)->value('id'),
                'department_id' => $itDept?->id,
                'status' => 'active',
                'locale' => 'id',
                'theme' => 'light',
            ]);
        }

        // Additional random employees/technicians for realistic dummy data volume.
        $employeeRoleId = Role::where('slug', UserRole::EMPLOYEE->value)->value('id');
        $technicianRoleId = Role::where('slug', UserRole::TECHNICIAN->value)->value('id');

        User::factory()->count(15)->create(['role_id' => $employeeRoleId]);
        User::factory()->count(5)->create(['role_id' => $technicianRoleId]);
    }
}
