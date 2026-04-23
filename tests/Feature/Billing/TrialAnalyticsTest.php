<?php

use App\Enums\AuditEvent;
use App\Enums\PlanTier;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\PlanLimitService;
use Carbon\Carbon;

beforeEach(function () {
    config(['plans.trial.enabled' => true, 'plans.trial.days' => 14]);
});

// ============================================
// Trial start
// ============================================

it('logs trial.started audit event when startTrial is called', function () {
    $user = User::factory()->create(['trial_ends_at' => null]);

    app(PlanLimitService::class)->startTrial($user);

    expect(
        AuditLog::where('user_id', $user->id)
            ->where('event', AuditEvent::TRIAL_STARTED->value)
            ->exists()
    )->toBeTrue();
});

it('sets trial_ends_at 14 days in future by default', function () {
    Carbon::setTestNow('2025-01-01 12:00:00');
    $user = User::factory()->create(['trial_ends_at' => null]);

    app(PlanLimitService::class)->startTrial($user);

    $user->refresh();
    expect($user->trial_ends_at->toDateString())->toBe('2025-01-15');
    Carbon::setTestNow();
});

// ============================================
// getUserPlan during/after trial
// ============================================

it('returns pro plan tier during active trial', function () {
    config(['plans.trial.tier' => 'pro']);
    $user = User::factory()->create([
        'trial_ends_at' => now()->addDays(7),
    ]);

    $plan = app(PlanLimitService::class)->getUserPlan($user);

    expect($plan)->toBe(PlanTier::Pro);
});

it('returns free plan tier after trial expires', function () {
    config(['features.billing.enabled' => false]);
    $user = User::factory()->create([
        'trial_ends_at' => now()->subDay(),
    ]);

    $plan = app(PlanLimitService::class)->getUserPlan($user);

    expect($plan)->toBe(PlanTier::Free);
});

// ============================================
// CheckExpiredTrials command
// ============================================

it('logs trial.expired for users with expired trial and no subscription', function () {
    ensureCashierTablesExist();

    $user = User::factory()->create([
        'trial_ends_at' => now()->subDays(1),
    ]);

    $this->artisan('trials:check-expired')->assertSuccessful();

    expect(
        AuditLog::where('user_id', $user->id)
            ->where('event', AuditEvent::TRIAL_EXPIRED->value)
            ->exists()
    )->toBeTrue();
});

it('never logs trial.expired again once already logged (lifetime idempotency)', function () {
    ensureCashierTablesExist();

    $user = User::factory()->create([
        'trial_ends_at' => now()->subDays(1),
    ]);

    // Simulate prior log from 30 days ago (past the old 2-day window)
    AuditLog::factory()->create([
        'user_id' => $user->id,
        'event' => AuditEvent::TRIAL_EXPIRED->value,
        'created_at' => now()->subDays(30),
    ]);

    $this->artisan('trials:check-expired')->assertSuccessful();

    expect(
        AuditLog::where('user_id', $user->id)
            ->where('event', AuditEvent::TRIAL_EXPIRED->value)
            ->count()
    )->toBe(1);
});

it('does not log trial.expired for users with active subscription', function () {
    ensureCashierTablesExist();
    config(['features.billing.enabled' => true]);
    registerBillingRoutes();

    $user = User::factory()->create([
        'trial_ends_at' => now()->subDays(1),
    ]);
    createSubscription($user, ['stripe_price' => 'price_pro', 'stripe_status' => 'active']);

    $this->artisan('trials:check-expired')->assertSuccessful();

    expect(
        AuditLog::where('user_id', $user->id)
            ->where('event', AuditEvent::TRIAL_EXPIRED->value)
            ->exists()
    )->toBeFalse();
});

it('skips command entirely when trials disabled', function () {
    config(['plans.trial.enabled' => false]);

    $user = User::factory()->create([
        'trial_ends_at' => now()->subDays(1),
    ]);

    $this->artisan('trials:check-expired')->assertSuccessful();

    expect(
        AuditLog::where('user_id', $user->id)
            ->where('event', AuditEvent::TRIAL_EXPIRED->value)
            ->exists()
    )->toBeFalse();
});
