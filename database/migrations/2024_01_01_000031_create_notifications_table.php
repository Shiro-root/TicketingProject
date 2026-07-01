<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // Notification class, e.g. App\Notifications\TicketAssigned
            $table->morphs('notifiable'); // notifiable_type / notifiable_id -> User
            $table->json('data'); // {"type":"ticket_assigned","ticket_id":1,"message":"..."}
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
