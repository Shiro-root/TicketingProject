<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Sla;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'IT Support', 'dept' => 'IT', 'icon' => 'life-buoy', 'color' => 'blue'],
            ['name' => 'Network', 'dept' => 'IT', 'icon' => 'wifi', 'color' => 'purple'],
            ['name' => 'Hardware', 'dept' => 'IT', 'icon' => 'cpu', 'color' => 'orange'],
            ['name' => 'Software', 'dept' => 'IT', 'icon' => 'app-window', 'color' => 'indigo'],
            ['name' => 'Finance', 'dept' => 'FIN', 'icon' => 'wallet', 'color' => 'green'],
            ['name' => 'HR', 'dept' => 'HR', 'icon' => 'users', 'color' => 'pink'],
            ['name' => 'Legal', 'dept' => 'LGL', 'icon' => 'scale', 'color' => 'gray'],
            ['name' => 'Marketing', 'dept' => 'MKT', 'icon' => 'megaphone', 'color' => 'red'],
            ['name' => 'Security', 'dept' => 'IT', 'icon' => 'shield', 'color' => 'red'],
        ];

        $mediumSla = Sla::where('priority', 'medium')->first();

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => Str::slug($cat['name'])], [
                'name' => $cat['name'],
                'description' => $cat['name'].' related issues',
                'icon' => $cat['icon'],
                'color' => $cat['color'],
                'department_id' => Department::where('code', $cat['dept'])->value('id'),
                'sla_id' => $mediumSla?->id,
                'is_active' => true,
            ]);
        }
    }
}
