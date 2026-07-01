<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Information Technology', 'code' => 'IT', 'description' => 'Infrastruktur, jaringan, dan dukungan teknis'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Kepegawaian dan pengembangan SDM'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Keuangan dan akuntansi'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Pemasaran dan brand'],
            ['name' => 'Legal', 'code' => 'LGL', 'description' => 'Hukum dan kepatuhan'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Operasional harian'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
