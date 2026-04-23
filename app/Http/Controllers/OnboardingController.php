<?php

namespace App\Http\Controllers;

use App\Enums\AuditEvent;
use App\Enums\LifecycleStage;
use App\Services\AuditService;
use App\Services\LifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function __invoke(): Response
    {
        $user = auth()->user();

        return Inertia::render('App/Onboarding', [
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

        // Transition to activated lifecycle stage
        if ($user) {
            try {
                app(LifecycleService::class)->transition(
                    $user,
                    LifecycleStage::ACTIVATED,
                    'onboarding_completed'
                );
            } catch (\Throwable $e) {
                Log::warning('lifecycle_transition_failed', ['user_id' => $user->id]);
            }
        }

        if ($user) {
            $this->auditService->log(AuditEvent::ONBOARDING_COMPLETED, [
                'user_id' => $user->id,
            ]);
        }

        session()->flash('success', 'Welcome aboard! Your account is all set.');

        return redirect()->route('dashboard');
    }
}
