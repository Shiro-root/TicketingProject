<?php

namespace App\Http\Controllers;

use App\Models\SavedFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Bonus Feature: Saved Filter.
 * Tabel `saved_filters` sudah ada sejak Tahap 1 — modul ini hanya menambahkan
 * Controller + UI. Filter disimpan sebagai hasil parse dari query-string yang
 * sedang aktif di halaman Tickets (lihat tombol "Simpan Filter Ini").
 */
class SavedFilterController extends Controller
{
    private const ALLOWED_KEYS = [
        'search', 'status', 'priority', 'category_id', 'department_id',
        'assigned_to', 'sla_breached', 'date_from', 'date_to', 'show_archived',
    ];

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'query_string' => ['nullable', 'string', 'max:2000'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        parse_str(ltrim($data['query_string'] ?? '', '?'), $parsed);
        $filters = array_filter(
            array_intersect_key($parsed, array_flip(self::ALLOWED_KEYS)),
            fn ($v) => $v !== null && $v !== ''
        );

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            $request->user()->savedFilters()->update(['is_default' => false]);
        }

        $request->user()->savedFilters()->create([
            'name' => $data['name'],
            'filters' => $filters,
            'is_default' => $isDefault,
        ]);

        return back()->with('status', 'saved-filter-created');
    }

    public function apply(Request $request, SavedFilter $savedFilter): RedirectResponse
    {
        abort_unless($savedFilter->user_id === $request->user()->id, 403);

        return redirect()->route('tickets.index', $savedFilter->filters);
    }

    public function destroy(Request $request, SavedFilter $savedFilter): RedirectResponse
    {
        abort_unless($savedFilter->user_id === $request->user()->id, 403);

        $savedFilter->delete();

        return back()->with('status', 'saved-filter-deleted');
    }
}
