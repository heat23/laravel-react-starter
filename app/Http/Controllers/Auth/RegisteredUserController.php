<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PlanLimitService;
use App\Services\SessionDataMigrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private SessionDataMigrationService $sessionDataMigration,
        private PlanLimitService $planLimitService,
        private AuditService $auditService
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

        $sessionDataSummary = $this->sessionDataMigration->hasSessionData()
            ? $this->sessionDataMigration->getSessionDataSummary()
            : null;

        return Inertia::render('Auth/Register', [
            'sessionData' => $sessionDataSummary,
            'features' => [
                'socialAuth' => config('features.social_auth.enabled', false),
            ],
            'rememberDays' => config('auth.remember.duration', 30),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email during registration', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            session()->flash('warning', 'Account created, but we could not send the verification email. Please try again later.');
        }

        // Start trial for new users if enabled
        if ($this->planLimitService->isTrialEnabled()) {
            $this->planLimitService->startTrial($user);
            $trialDays = config('plans.trial.days', 14);
            session()->flash('trial_started', "Welcome! You have a {$trialDays}-day free Pro trial.");
        }

        // Migrate session data before logging in
        if ($this->sessionDataMigration->hasSessionData()) {
            try {
                $migrationResult = $this->sessionDataMigration->migrateSessionData($user);

                if ($migrationResult['project_items'] > 0) {
                    $itemsText = $migrationResult['project_items'] === 1 ? 'package' : 'packages';
                    session()->flash('success', "Welcome! We've imported {$migrationResult['project_items']} {$itemsText} from your quick scan.");
                }
            } catch (\Exception $e) {
                Log::error('Failed to migrate session data during registration', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't block registration if migration fails
            }
        }

        Auth::login($user, $request->boolean('remember', false));

        $this->auditService->logRegistration($user);

        return redirect(route('dashboard', absolute: false));
    }
}
