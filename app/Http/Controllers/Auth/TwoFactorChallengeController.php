<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactor\TwoFactorChallengeRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorChallengeController extends Controller
{
    public function __construct(
        private AuditService $auditService
    ) {
        abort_unless(config('features.two_factor.enabled', false), 404);
    }

    public function create(): Response|RedirectResponse
    {
        if (! session('login.id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(TwoFactorChallengeRequest $request): RedirectResponse
    {
        $userId = session('login.id');
        $remember = session('login.remember', false);

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        $code = $request->input('code');
        $recoveryCode = $request->input('recovery_code');

        $valid = false;

        if ($code) {
            $valid = $user->validateTwoFactorCode($code, useRecoveryCodes: false);
        } elseif ($recoveryCode) {
            $valid = $user->validateTwoFactorCode($recoveryCode);
        }

        if (! $valid) {
            return back()->withErrors([
                'code' => 'The provided two-factor code is invalid.',
            ]);
        }

        Auth::loginUsingId($userId, $remember);

        $request->session()->regenerate();
        $request->session()->forget(['login.id', 'login.remember']);

        $this->auditService->log('auth.2fa_verified', [
            'email' => $user->email,
            'method' => $code ? 'totp' : 'recovery',
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
