<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('frequency', 10); // daily | weekly | monthly
            $table->unsignedSmallInteger('period_days')->nullable(); // filter "N hari terakhir" saat dikirim, null = tanpa batas tanggal
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->nullable();   // App\Enums\TicketStatus, null = semua status
            $table->string('priority', 20)->nullable();  // App\Enums\TicketPriority, null = semua prioritas
            $table->string('format', 10)->default('pdf'); // pdf | excel
            $table->json('recipients'); // daftar alamat email tujuan
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
