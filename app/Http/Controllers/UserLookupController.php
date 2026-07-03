<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserLookupResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Dipakai oleh komponen mention (@) di comment box dan picker teknisi saat assign. */
class UserLookupController extends Controller
{
    public function search(Request $request): AnonymousResourceCollection
    {
        $term = $request->string('q');

        $users = User::query()
            ->when($term, fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->when($request->boolean('technicians_only'), fn ($q) => $q->whereHas(
                'role',
                fn ($r) => $r->whereIn('slug', ['technician', 'supervisor'])
            ))
            ->orderBy('name')
            ->limit(10)
            ->get();

        return UserLookupResource::collection($users);
    }
}
