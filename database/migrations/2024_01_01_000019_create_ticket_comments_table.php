<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('ticket_comments')->cascadeOnDelete();
            $table->longText('body');
            $table->boolean('is_internal')->default(false); // internal note: hidden from Employee/Guest
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ticket_id', 'is_internal']);
        });

        // Pivot for @mention tracking inside comments
        Schema::create('comment_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ticket_comment_id', 'user_id'], 'comment_mentions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_mentions');
        Schema::dropIfExists('ticket_comments');
    }
};
