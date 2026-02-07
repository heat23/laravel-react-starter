<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactor\ConfirmTwoFactorRequest;
use App\Http\Requests\TwoFactor\DisableTwoFactorRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function __construct(
        private AuditService $auditService
    ) {
        abort_unless(config('features.two_factor.enabled', false), 404);
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $twoFactorEnabled = $user->hasTwoFactorEnabled();

        $data = [
            'enabled' => $twoFactorEnabled,
            'qr_code' => null,
            'secret' => null,
            'recovery_codes' => null,
        ];

        // If there's a pending (not yet confirmed) 2FA setup, show QR
        if (! $twoFactorEnabled && $user->twoFactorAuth()->exists() && $user->twoFactorAuth->isDisabled()) {
            $data['qr_code'] = $user->twoFactorAuth->toQr();
            $data['secret'] = $user->twoFactorAuth->toString();
        }

        return Inertia::render('Settings/Security', $data);
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->createTwoFactorAuth();

        return back();
    }

    public function confirm(ConfirmTwoFactorRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->confirmTwoFactorAuth($request->validated('code'))) {
            return back()->withErrors(['code' => 'The provided code is invalid.']);
        }

        $this->auditService->log('auth.2fa_enabled', [
            'email' => $user->email,
        ]);

        return back()->with('success', 'Two-factor authentication has been enabled.');
    }

    public function disable(DisableTwoFactorRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->disableTwoFactorAuth();

        $this->auditService->log('auth.2fa_disabled', [
            'email' => $user->email,
        ]);

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    public function recoveryCodes(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            abort(403);
        }

        return response()->json([
            'recovery_codes' => $user->getRecoveryCodes()->pluck('code')->toArray(),
        ]);
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            abort(403);
        }

        $user->generateRecoveryCodes();

        $this->auditService->log('auth.2fa_recovery_regenerated', [
            'email' => $user->email,
        ]);

        return back()->with('success', 'Recovery codes have been regenerated.');
    }
}
