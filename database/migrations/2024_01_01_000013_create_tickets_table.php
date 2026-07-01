<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 30)->unique(); // e.g. TCK-2026-000123, generated in TicketService
            $table->string('subject');
            $table->longText('description');
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('priority', 20)->default('medium'); // App\Enums\TicketPriority
            $table->string('status', 20)->default('open');     // App\Enums\TicketStatus
            $table->foreignId('sla_id')->nullable()->constrained('slas')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('due_at')->nullable();        // SLA resolution deadline
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('is_sla_breached')->default(false);
            $table->boolean('is_archived')->default(false);

            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->text('feedback')->nullable();

            // Merge / duplicate tracking
            $table->foreignId('merged_into_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('duplicate_of_id')->nullable()->constrained('tickets')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
