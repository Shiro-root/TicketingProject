<?php

namespace Database\Factories;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected static int $sequence = 1;

    public function definition(): array
    {
        $type = fake()->randomElement(AssetType::cases());

        return [
            'asset_tag' => 'AST-'.str_pad((string) static::$sequence++, 5, '0', STR_PAD_LEFT),
            'name' => $type->label().' '.fake()->word(),
            'type' => $type->value,
            'brand' => fake()->randomElement(['Dell', 'HP', 'Lenovo', 'Cisco', 'Asus', 'Apple']),
            'model' => strtoupper(fake()->bothify('??-####')),
            'serial_number' => strtoupper(fake()->unique()->bothify('SN########')),
            'department_id' => Department::query()->inRandomOrder()->value('id'),
            'assigned_to' => fake()->boolean(60) ? User::query()->inRandomOrder()->value('id') : null,
            'location' => fake()->randomElement(['Lantai 1', 'Lantai 2', 'Lantai 3', 'Gudang IT', 'Ruang Server']),
            'status' => fake()->randomElement(AssetStatus::cases())->value,
            'purchase_date' => fake()->dateTimeBetween('-3 years', '-1 month'),
            'purchase_price' => fake()->randomFloat(2, 1000000, 50000000),
            'warranty_expiry' => fake()->dateTimeBetween('now', '+2 years'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
