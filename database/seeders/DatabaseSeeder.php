<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeding order matters — each seeder depends on data created by the previous one.
     * Run with: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,          // roles + permissions (no FK deps)
            DepartmentSeeder::class,    // departments (no FK deps)
            UserSeeder::class,          // needs roles + departments
            SlaSeeder::class,           // no FK deps
            CategorySeeder::class,      // needs departments + slas
            TagSeeder::class,           // no FK deps
            AssetSeeder::class,         // needs departments + users
            KnowledgeBaseSeeder::class, // needs categories + users
            TicketSeeder::class,        // needs categories, departments, slas, users, tags
            ApprovalWorkflowSeeder::class, // needs roles, departments, categories
            EmailTemplateSeeder::class, // no FK deps
            AnnouncementSeeder::class,  // needs users
        ]);

        $this->command->info('');
        $this->command->info('✅ Database seeded successfully.');
        $this->command->info('');
        $this->command->info('Demo accounts (password: "password"):');
        $this->command->table(['Role', 'Email'], [
            ['Super Admin', 'superadmin@helpdesk.test'],
            ['Admin', 'admin@helpdesk.test'],
            ['Manager', 'manager@helpdesk.test'],
            ['Supervisor', 'supervisor@helpdesk.test'],
            ['Technician', 'technician@helpdesk.test'],
            ['Employee', 'employee@helpdesk.test'],
            ['Guest', 'guest@helpdesk.test'],
        ]);
    }
}
