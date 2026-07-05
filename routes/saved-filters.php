<?php

use App\Http\Controllers\SavedFilterController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('saved-filters')->name('saved-filters.')->group(function () {
    Route::post('/', [SavedFilterController::class, 'store'])->name('store');
    Route::get('{savedFilter}/apply', [SavedFilterController::class, 'apply'])->name('apply');
    Route::delete('{savedFilter}', [SavedFilterController::class, 'destroy'])->name('destroy');
});
