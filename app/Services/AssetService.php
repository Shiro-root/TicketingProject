<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Business logic Asset Management. Konsisten dengan pola TicketService/KnowledgeBaseService:
 * Controller tidak menyentuh Eloquent langsung untuk operasi tulis.
 */
class AssetService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function create(array $data, User $actor, Request $request): Asset
    {
        return DB::transaction(function () use ($data, $actor, $request) {
            $asset = Asset::create([
                'asset_tag' => $data['asset_tag'],
                'name' => $data['name'],
                'type' => $data['type'],
                'brand' => $data['brand'] ?? null,
                'model' => $data['model'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'location' => $data['location'] ?? null,
                'status' => $data['status'],
                'purchase_date' => $data['purchase_date'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? null,
                'warranty_expiry' => $data['warranty_expiry'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditLogger->log(AuditAction::CREATE, $actor, $request, $asset, null, $asset->only([
                'asset_tag', 'name', 'type', 'status',
            ]));

            return $asset->fresh();
        });
    }

    public function update(Asset $asset, array $data, User $actor, Request $request): Asset
    {
        return DB::transaction(function () use ($asset, $data, $actor, $request) {
            $old = $asset->only([
                'asset_tag', 'name', 'type', 'brand', 'model', 'serial_number',
                'department_id', 'assigned_to', 'location', 'status',
                'purchase_date', 'purchase_price', 'warranty_expiry', 'notes',
            ]);

            $asset->fill([
                'asset_tag' => $data['asset_tag'],
                'name' => $data['name'],
                'type' => $data['type'],
                'brand' => $data['brand'] ?? null,
                'model' => $data['model'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'location' => $data['location'] ?? null,
                'status' => $data['status'],
                'purchase_date' => $data['purchase_date'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? null,
                'warranty_expiry' => $data['warranty_expiry'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $asset->save();

            $this->auditLogger->log(AuditAction::UPDATE, $actor, $request, $asset, $old, $asset->only([
                'asset_tag', 'name', 'type', 'brand', 'model', 'serial_number',
                'department_id', 'assigned_to', 'location', 'status',
                'purchase_date', 'purchase_price', 'warranty_expiry', 'notes',
            ]));

            return $asset->fresh();
        });
    }

    public function delete(Asset $asset, User $actor, Request $request): void
    {
        $asset->delete();
        $this->auditLogger->log(AuditAction::DELETE, $actor, $request, $asset);
    }

    public function restore(Asset $asset, User $actor, Request $request): Asset
    {
        $asset->restore();
        $this->auditLogger->log(AuditAction::RESTORE, $actor, $request, $asset);

        return $asset->fresh();
    }

    /** Filter + search untuk halaman index. */
    public function search(Request $request)
    {
        $query = Asset::query()->with(['department', 'assignedUser']);

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('asset_tag', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('serial_number', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%");
            });
        }

        foreach (['type', 'status', 'department_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('warranty')) {
            match ($request->string('warranty')->toString()) {
                'active' => $query->whereNotNull('warranty_expiry')->where('warranty_expiry', '>=', now()),
                'expired' => $query->whereNotNull('warranty_expiry')->where('warranty_expiry', '<', now()),
                'none' => $query->whereNull('warranty_expiry'),
                default => null,
            };
        }

        return $query->latest()->paginate(15)->withQueryString();
    }
}