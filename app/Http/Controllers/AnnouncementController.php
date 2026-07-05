<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Bonus Feature: Announcement Banner.
 * Tabel `announcements` + model + scope currentlyActive() sudah ada sejak
 * Tahap 1 (dipakai AnnouncementSeeder). Modul ini menambahkan CRUD admin +
 * komponen banner (lihat resources/views/components/announcement-banner.blade.php)
 * yang tampil otomatis di layouts/app.blade.php untuk semua user yang login.
 *
 * Akses dibatasi permission `settings.manage` (Admin/Super Admin), konsisten
 * dengan pola permission langsung tanpa Policy terpisah seperti pada Report (Tahap 8).
 */
class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('announcements.index', [
            'announcements' => Announcement::with('creator')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        Announcement::create($data);

        return redirect()->route('announcements.index')->with('status', 'announcement-created');
    }

    public function edit(Announcement $announcement): View
    {
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validated($request));

        return redirect()->route('announcements.index')->with('status', 'announcement-updated');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('status', 'announcement-deleted');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'in:info,warning,success,danger'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
