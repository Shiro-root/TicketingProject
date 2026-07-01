<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ApprovalWorkflow;
use App\Models\Category;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $itDept = Department::where('code', 'IT')->first();
        $hardwareCategory = Category::where('slug', 'hardware')->first();

        $workflow = ApprovalWorkflow::firstOrCreate(
            ['name' => 'IT Asset Request Approval'],
            [
                'description' => 'Alur persetujuan untuk permintaan perangkat/asset baru',
                'department_id' => $itDept?->id,
                'category_id' => $hardwareCategory?->id,
                'is_active' => true,
            ]
        );

        $steps = [
            ['order' => 1, 'name' => 'Manager Approval', 'role' => UserRole::MANAGER],
            ['order' => 2, 'name' => 'IT Supervisor Approval', 'role' => UserRole::SUPERVISOR],
            ['order' => 3, 'name' => 'Technician Fulfillment', 'role' => UserRole::TECHNICIAN],
        ];

        foreach ($steps as $step) {
            $workflow->steps()->firstOrCreate(
                ['step_order' => $step['order']],
                [
                    'name' => $step['name'],
                    'approver_role_id' => Role::where('slug', $step['role']->value)->value('id'),
                ]
            );
        }
    }
}
