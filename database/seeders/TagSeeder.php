<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Urgent', 'color' => 'red'],
            ['name' => 'VIP', 'color' => 'purple'],
            ['name' => 'Recurring', 'color' => 'orange'],
            ['name' => 'Hardware Failure', 'color' => 'gray'],
            ['name' => 'Access Request', 'color' => 'blue'],
            ['name' => 'Password Reset', 'color' => 'green'],
            ['name' => 'Vendor', 'color' => 'indigo'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['slug' => Str::slug($tag['name'])], $tag);
        }
    }
}
