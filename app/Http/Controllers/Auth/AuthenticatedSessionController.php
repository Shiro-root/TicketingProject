<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuditAction;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /** Display the login page. */
    public function create(): View
    {
        return view('auth.login');
    }

    /** Handle an incoming authentication request. */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Suspended/inactive accounts are not allowed in, even with correct credentials.
        if (! $user->isActive()) {
            $status = $user->status?->value ?? 'inactive';
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => match ($status) {
                    UserStatus::SUSPENDED->value => 'Akun Anda telah di-suspend. Hubungi administrator.',
                    default => 'Akun Anda tidak aktif. Hubungi administrator.',
                },
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        $this->auditLogger->log(AuditAction::LOGIN, $user, $request);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /** Destroy an authenticated session. */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::guard('web')->logout();

        if ($user) {
            $this->auditLogger->log(AuditAction::LOGOUT, $user, $request);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
