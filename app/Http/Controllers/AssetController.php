<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\Department;
use App\Models\User;
use App\Services\AssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(private readonly AssetService $assets)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Asset::class);

        return view('assets.index', [
            'assets' => $this->assets->search($request),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Asset::class);

        return view('assets.create', [
            'departments' => Department::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $asset = $this->assets->create($request->validated(), $request->user(), $request);

        return redirect()->route('assets.show', $asset)->with('status', 'asset-created');
    }

    public function show(Asset $asset): View
    {
        $this->authorize('view', $asset);

        $asset->load(['department', 'assignedUser', 'tickets' => fn ($q) => $q->latest()->limit(10)]);

        return view('assets.show', ['asset' => $asset]);
    }

    public function edit(Asset $asset): View
    {
        $this->authorize('update', $asset);

        return view('assets.edit', [
            'asset' => $asset,
            'departments' => Department::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $this->assets->update($asset, $request->validated(), $request->user(), $request);

        return redirect()->route('assets.show', $asset)->with('status', 'asset-updated');
    }

    public function destroy(Request $request, Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);
        $this->assets->delete($asset, $request->user(), $request);

        return redirect()->route('assets.index')->with('status', 'asset-deleted');
    }

    public function trashed(): View
    {
        $this->authorize('viewAny', Asset::class);

        $assets = Asset::onlyTrashed()
            ->with(['department', 'assignedUser'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('assets.trashed', ['assets' => $assets]);
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $asset = Asset::withTrashed()->findOrFail($id);
        $this->authorize('restore', $asset);
        $this->assets->restore($asset, $request->user(), $request);

        return redirect()->route('assets.show', $asset)->with('status', 'asset-restored');
    }
}