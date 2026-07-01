<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('priority', 20); // App\Enums\TicketPriority
            $table->unsignedInteger('response_time_minutes');   // time to first response
            $table->unsignedInteger('resolution_time_minutes'); // time to resolve
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slas');
    }
};
