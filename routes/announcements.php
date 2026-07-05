<?php

use App\Http\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:settings.manage'])->prefix('announcements')->name('announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::get('create', [AnnouncementController::class, 'create'])->name('create');
    Route::post('/', [AnnouncementController::class, 'store'])->name('store');
    Route::get('{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
    Route::put('{announcement}', [AnnouncementController::class, 'update'])->name('update');
    Route::delete('{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
});
