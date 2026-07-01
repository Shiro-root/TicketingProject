<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['FAQ', 'Tutorial', 'SOP', 'Documentation'];

        foreach ($categories as $name) {
            KnowledgeBaseCategory::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => $name.' articles',
            ]);
        }

        \App\Models\KnowledgeBaseArticle::factory()->count(20)->create();
    }
}
