<?php

use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:settings.manage'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance');
    Route::post('maintenance/enable', [MaintenanceController::class, 'enable'])->name('maintenance.enable');
    Route::post('maintenance/disable', [MaintenanceController::class, 'disable'])->name('maintenance.disable');
});
