<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Bonus Feature: Dashboard Real-time — di-poll via JS tiap beberapa detik.
    Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live');
});

require __DIR__.'/auth.php';
require __DIR__.'/tickets.php';
require __DIR__.'/notifications.php';
require __DIR__.'/knowledge-base.php';
require __DIR__.'/assets.php';
require __DIR__.'/reports.php';
require __DIR__.'/saved-filters.php';
require __DIR__.'/announcements.php';
require __DIR__.'/report-schedules.php';
require __DIR__.'/settings.php';
