<?php

use App\Http\Controllers\AssetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('assets/trashed/list', [AssetController::class, 'trashed'])->name('assets.trashed');
    Route::post('assets/trashed/{id}/restore', [AssetController::class, 'restore'])->name('assets.restore');

    Route::resource('assets', AssetController::class);
});