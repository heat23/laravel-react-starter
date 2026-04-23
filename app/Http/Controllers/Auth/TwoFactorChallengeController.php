<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuditEvent;
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
        abort_unless(feature_enabled('two_factor'), 404);
    }

    public function create(): Response|RedirectResponse
    {
        if (! session('login.id')) {
            return redirect()->route('login');
        }

        $expiresAt = session('login.expires_at');
        if ($expiresAt === null || now()->getTimestamp() > $expiresAt) {
            session()->forget(['login.id', 'login.remember', 'login.expires_at']);

            return redirect()->route('login')->withErrors(['email' => '2FA session expired, please sign in again.']);
        }

        return Inertia::render('App/Auth/TwoFactorChallenge');
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
        $request->session()->forget(['login.id', 'login.remember', 'login.expires_at']);

        $this->auditService->log(AuditEvent::AUTH_2FA_VERIFIED, [
            'method' => $code ? 'totp' : 'recovery',
        ]);

        $user->updateLastLogin();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function cancel(): RedirectResponse
    {
        session()->forget(['login.id', 'login.remember', 'login.expires_at']);

        return redirect()->route('login');
    }
}
