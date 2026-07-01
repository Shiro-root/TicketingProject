<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Information Technology', 'Human Resources', 'Finance', 'Marketing',
            'Legal', 'Operations', 'Sales', 'Customer Service', 'Procurement', 'Facilities',
        ]);

        return [
            'name' => $name,
            'code' => strtoupper(substr(str_replace(' ', '', $name), 0, 4)),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
