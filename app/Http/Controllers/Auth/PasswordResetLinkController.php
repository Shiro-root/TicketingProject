<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /** Display the forgot password page. */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /** Handle an incoming password reset link request. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We intentionally don't reveal whether the e-mail exists (avoids user enumeration).
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        // For unknown emails, Laravel returns INVALID_USER — surface a generic success
        // message instead so attackers can't probe which addresses are registered.
        if ($status === Password::INVALID_USER) {
            return back()->with('status', __(Password::RESET_LINK_SENT));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
