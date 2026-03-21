<?php

namespace App\Http\Controllers;

use App\Enums\AnalyticsEvent;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function __invoke(): Response
    {
        $user = auth()->user();

        return Inertia::render('Onboarding', [
            'email_verified' => $user?->hasVerifiedEmail() ?? false,
        ]);
    }

    /**
     * Mark onboarding as complete and redirect to the dashboard with a welcome flash.
     * Uses user_settings to persist completion (same key the middleware checks).
     */
    public function complete(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user && config('features.user_settings.enabled', true)) {
            $user->setSetting('onboarding_completed', now()->toISOString());
        }

        if ($user) {
            $this->auditService->logProductEvent(AnalyticsEvent::ONBOARDING_COMPLETED, $user);
        }

        session()->flash('success', 'Welcome aboard! Your account is all set.');

        return redirect()->route('dashboard');
    }
}
