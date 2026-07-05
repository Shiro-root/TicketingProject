<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Bonus Feature: Maintenance Mode.
 * Membungkus `php artisan down` / `php artisan up` bawaan Laravel dengan UI
 * sederhana, supaya Admin tidak perlu akses terminal server untuk mengaktifkan
 * mode maintenance. Halaman ini sendiri TIDAK ikut down (dilindungi --secret)
 * sehingga Admin tetap bisa mematikannya lagi lewat browser.
 */
class MaintenanceController extends Controller
{
    public function index(): View
    {
        return view('settings.maintenance', [
            'isDown' => app()->isDownForMaintenance(),
            'secret' => session('maintenance_secret'),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:255'],
            'retry' => ['nullable', 'integer', 'min:0'],
        ]);

        $secret = Str::random(32);

        // Pesan kustom disimpan lewat Cache sendiri (bukan opsi --message pada
        // command `down` bawaan Laravel, karena opsi tsb tidak konsisten tersedia
        // di semua versi) — dibaca kembali oleh resources/views/errors/503.blade.php.
        Cache::put('maintenance_message', $data['message'] ?? 'Sedang dalam pemeliharaan terjadwal. Silakan coba beberapa saat lagi.', now()->addDay());

        Artisan::call('down', array_filter([
            '--secret' => $secret,
            '--retry' => $data['retry'] ?? 60,
        ]));

        return redirect()->route('settings.maintenance')
            ->with('status', 'maintenance-enabled')
            ->with('maintenance_secret', $secret);
    }

    public function disable(): RedirectResponse
    {
        Artisan::call('up');
        Cache::forget('maintenance_message');

        return redirect()->route('settings.maintenance')->with('status', 'maintenance-disabled');
    }
}
