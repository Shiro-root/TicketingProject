<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{id}/read', [NotificationController::class, 'read'])->name('read');
        Route::post('read-all', [NotificationController::class, 'readAll'])->name('read-all');
    });

    Route::put('profile/notifications', [NotificationSettingController::class, 'update'])
        ->name('profile.notifications.update');
});