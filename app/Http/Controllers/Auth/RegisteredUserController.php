<?php

namespace App\Http\Controllers\Auth;

use App\Enums\LifecycleStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use App\Services\LifecycleService;
use App\Services\PlanLimitService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private PlanLimitService $planLimitService,
        private AuditService $auditService,
        private CacheInvalidationManager $cacheInvalidation,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): Response|RedirectResponse
    {
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('App/Auth/Register', [
            'features' => [
                'socialAuth' => config('features.social_auth.enabled', false),
            ],
            'rememberDays' => config('auth.remember.duration', 30),
            'onboardingEnabled' => config('features.onboarding.enabled', true),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            // When onboarding is enabled, name may be omitted from the form.
            // Default to the email's local part; the wizard lets the user update it.
            $name = $validated['name'] ?? null;
            if (empty($name)) {
                $name = ucfirst(explode('@', $validated['email'])[0]);
            }

            $user = User::create([
                'name' => $name,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Start trial for new users if enabled
            if ($this->planLimitService->isTrialEnabled()) {
                $this->planLimitService->startTrial($user);
                $trialDays = config('plans.trial.days', 14);
                session()->flash('trial_started', "Welcome! You have a {$trialDays}-day free Pro trial.");
            }

            return $user;
        });

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email during registration', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            session()->flash('warning', 'Account created, but we could not send the verification email. You can resend it from your profile settings.');
        }

        Auth::login($user, $request->boolean('remember', false));

        // Lifecycle stage assignment
        try {
            $stage = $this->planLimitService->isTrialEnabled() && $user->trial_ends_at
                ? LifecycleStage::TRIAL
                : LifecycleStage::VISITOR;
            app(LifecycleService::class)->transition($user, $stage, 'registration');
        } catch (\Throwable $e) {
            Log::error('lifecycle_transition_failed_on_registration', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        $this->auditService->logRegistration($user, [
            'plan_intent' => $request->query('plan'),
        ]);
        $this->cacheInvalidation->invalidateOnRegistration();

        // Preserve plan intent: if user came from a pricing page plan CTA, redirect
        // back to pricing with that plan pre-selected so they can complete checkout.
        $plan = $request->query('plan');
        $billing = $request->query('billing');

        if ($plan && in_array($plan, ['pro', 'team', 'enterprise'], true)) {
            $params = ['plan' => $plan];
            if ($billing && in_array($billing, ['monthly', 'annual'], true)) {
                $params['billing'] = $billing;
            }

            return redirect()->route('pricing', $params);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
