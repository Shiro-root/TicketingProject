<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->numerify('08##########'),
            'position' => fake()->jobTitle(),
            'division' => fake()->randomElement(['Operations', 'Support', 'Engineering', 'Sales', 'Admin']),
            'role_id' => Role::query()->inRandomOrder()->value('id') ?? Role::factory(),
            'department_id' => Department::query()->inRandomOrder()->value('id'),
            'status' => 'active',
            'locale' => 'id',
            'theme' => 'light',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => ['email_verified_at' => null]);
    }
}
