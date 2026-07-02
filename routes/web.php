<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Placeholder — replaced by the real DashboardController in Modul 3 (Dashboard).
// Kept here so `route('dashboard')` used by the login redirect resolves during Modul 2.
Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard-placeholder');
})->name('dashboard');

require __DIR__.'/auth.php';
