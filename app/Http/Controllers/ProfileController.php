<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /** Display the authenticated user's profile form. */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /** Update the authenticated user's profile information. */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $oldValues = $user->only(['name', 'email', 'phone', 'position', 'locale', 'theme', 'avatar']);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'locale' => $validated['locale'],
            'theme' => $validated['theme'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        $this->auditLogger->log(
            AuditAction::UPDATE,
            $user,
            $request,
            $user,
            $oldValues,
            $user->only(['name', 'email', 'phone', 'position', 'locale', 'theme', 'avatar']),
        );

        return back()->with('status', 'profile-updated');
    }

    /** Update the authenticated user's password. */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        $this->auditLogger->log(
            AuditAction::UPDATE,
            $user,
            $request,
            $user,
            newValues: ['field' => 'password'],
        );

        return back()->with('status', 'password-updated');
    }
}
