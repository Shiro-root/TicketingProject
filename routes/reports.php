<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:report.view'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');

    // Export butuh izin lebih tinggi (report.export) — Supervisor hanya punya report.view,
    // Manager/Admin/Super Admin punya keduanya (lihat RoleSeeder Tahap 1).
    Route::middleware('permission:report.export')->group(function () {
        Route::get('export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
        Route::get('export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
    });
});
