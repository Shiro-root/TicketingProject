<?php

use App\Http\Controllers\ReportScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:report.export'])->prefix('report-schedules')->name('report-schedules.')->group(function () {
    Route::get('/', [ReportScheduleController::class, 'index'])->name('index');
    Route::get('create', [ReportScheduleController::class, 'create'])->name('create');
    Route::post('/', [ReportScheduleController::class, 'store'])->name('store');
    Route::get('{reportSchedule}/edit', [ReportScheduleController::class, 'edit'])->name('edit');
    Route::put('{reportSchedule}', [ReportScheduleController::class, 'update'])->name('update');
    Route::delete('{reportSchedule}', [ReportScheduleController::class, 'destroy'])->name('destroy');
});
