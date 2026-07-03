<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Models\NotificationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    /** Update preferensi in-app/email per tipe notifikasi dari halaman Profil. */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        foreach (NotificationType::cases() as $type) {
            NotificationSetting::updateOrCreate(
                ['user_id' => $user->id, 'type' => $type->value],
                [
                    'in_app' => $request->boolean("in_app.{$type->value}", true),
                    'email' => $request->boolean("email.{$type->value}", true),
                ]
            );
        }

        return back()->with('status', 'notification-settings-updated');
    }
}