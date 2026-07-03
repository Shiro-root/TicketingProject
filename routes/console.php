<?php

use App\Console\Commands\CheckSlaEscalations;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto Escalation (SLA) — cek tiap 15 menit, cukup granular tanpa membebani DB.
// Jalankan `php artisan schedule:work` saat development untuk mensimulasikan cron.
Schedule::command(CheckSlaEscalations::class)->everyFifteenMinutes();