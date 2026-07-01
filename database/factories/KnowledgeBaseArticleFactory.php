<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\KnowledgeBaseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KnowledgeBaseArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'content' => fake()->paragraphs(5, true),
            'excerpt' => fake()->sentence(15),
            'knowledge_base_category_id' => KnowledgeBaseCategory::query()->inRandomOrder()->value('id'),
            'category_id' => Category::query()->inRandomOrder()->value('id'),
            'created_by' => User::query()->inRandomOrder()->value('id'),
            'view_count' => fake()->numberBetween(0, 500),
            'is_published' => true,
        ];
    }
}
