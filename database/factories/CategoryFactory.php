<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Sla;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'IT Support', 'Network', 'Hardware', 'Software', 'Finance',
            'HR', 'Legal', 'Marketing', 'Security', 'Facilities',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'icon' => 'folder',
            'color' => fake()->randomElement(['blue', 'green', 'purple', 'orange', 'red', 'gray']),
            'department_id' => Department::query()->inRandomOrder()->value('id'),
            'sla_id' => Sla::query()->inRandomOrder()->value('id'),
            'is_active' => true,
        ];
    }
}
