<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->foreignId('knowledge_base_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete(); // ticket category link for AI suggestion matching
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->fullText(['title', 'content']);
        });

        Schema::create('knowledge_base_article_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_base_article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['knowledge_base_article_id', 'tag_id'], 'kb_article_tag_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_article_tag');
        Schema::dropIfExists('knowledge_base_articles');
    }
};
