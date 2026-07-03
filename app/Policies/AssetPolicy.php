<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

/**
 * Super Admin selalu bypass lewat Gate::before di AppServiceProvider.
 * Baca daftar asset butuh asset.view (dipakai teknisi untuk mengecek asset
 * yang terkait ticketnya); tulis/hapus butuh asset.manage.
 */
class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('asset.view') || $user->hasPermission('asset.manage');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->hasPermission('asset.view') || $user->hasPermission('asset.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('asset.manage');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->hasPermission('asset.manage');
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->hasPermission('asset.manage');
    }

    public function restore(User $user, Asset $asset): bool
    {
        return $user->hasPermission('asset.manage');
    }
}