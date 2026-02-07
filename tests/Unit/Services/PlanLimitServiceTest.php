<?php

use App\Models\User;
use App\Services\PlanLimitService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = app(PlanLimitService::class);
});

// ============================================
// isTrialEnabled() tests
// ============================================

it('returns true when plans.trial.enabled is true', function () {
    config(['plans.trial.enabled' => true]);
    config(['features.billing.trial_enabled' => false]);

    expect($this->service->isTrialEnabled())->toBeTrue();
});

it('returns true when features.billing.trial_enabled is true', function () {
    config(['plans.trial.enabled' => false]);
    config(['features.billing.trial_enabled' => true]);

    expect($this->service->isTrialEnabled())->toBeTrue();
});

it('returns true when both trial configs are true', function () {
    config(['plans.trial.enabled' => true]);
    config(['features.billing.trial_enabled' => true]);

    expect($this->service->isTrialEnabled())->toBeTrue();
});

it('returns false when both trial configs are false', function () {
    config(['plans.trial.enabled' => false]);
    config(['features.billing.trial_enabled' => false]);

    expect($this->service->isTrialEnabled())->toBeFalse();
});

it('returns false when trial configs are not set', function () {
    config(['plans.trial.enabled' => null]);
    config(['features.billing.trial_enabled' => null]);

    expect($this->service->isTrialEnabled())->toBeFalse();
});

// ============================================
// startTrial() tests
// ============================================

it('sets trial_ends_at to configured days', function () {
    Carbon::setTestNow('2024-01-15 12:00:00');
    config(['plans.trial.days' => 7]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    $this->service->startTrial($user);

    $user->refresh();
    expect($user->trial_ends_at)->not->toBeNull();
    expect($user->trial_ends_at->format('Y-m-d H:i:s'))->toBe('2024-01-22 12:00:00');

    Carbon::setTestNow();
});

it('uses default 14 days when trial days not configured', function () {
    Carbon::setTestNow('2024-01-01 00:00:00');
    config(['plans.trial.days' => 14]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    $this->service->startTrial($user);

    $user->refresh();
    expect($user->trial_ends_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 00:00:00');

    Carbon::setTestNow();
});

it('overwrites existing trial_ends_at', function () {
    Carbon::setTestNow('2024-06-01 00:00:00');
    config(['plans.trial.days' => 30]);
    $user = User::factory()->create(['trial_ends_at' => now()->subDays(10)]);

    $this->service->startTrial($user);

    $user->refresh();
    expect($user->trial_ends_at->format('Y-m-d H:i:s'))->toBe('2024-07-01 00:00:00');

    Carbon::setTestNow();
});

// ============================================
// isOnTrial() tests
// ============================================

it('returns false when trial_ends_at is null', function () {
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->isOnTrial($user))->toBeFalse();
});

it('returns true when trial_ends_at is in future', function () {
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(5)]);

    expect($this->service->isOnTrial($user))->toBeTrue();
});

it('returns false when trial_ends_at is in past', function () {
    $user = User::factory()->create(['trial_ends_at' => now()->subDays(1)]);

    expect($this->service->isOnTrial($user))->toBeFalse();
});

it('returns false when trial_ends_at is exactly now', function () {
    Carbon::setTestNow('2024-01-15 12:00:00');
    $user = User::factory()->create(['trial_ends_at' => Carbon::parse('2024-01-15 12:00:00')]);

    expect($this->service->isOnTrial($user))->toBeFalse();

    Carbon::setTestNow();
});

it('returns true for trial ending in one second', function () {
    Carbon::setTestNow('2024-01-15 12:00:00');
    $user = User::factory()->create(['trial_ends_at' => Carbon::parse('2024-01-15 12:00:01')]);

    expect($this->service->isOnTrial($user))->toBeTrue();

    Carbon::setTestNow();
});

// ============================================
// trialDaysRemaining() tests
// ============================================

it('returns zero days remaining when not on trial', function () {
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->trialDaysRemaining($user))->toBe(0);
});

it('returns zero days remaining when trial expired', function () {
    $user = User::factory()->create(['trial_ends_at' => now()->subDays(5)]);

    expect($this->service->trialDaysRemaining($user))->toBe(0);
});

it('returns correct remaining days count', function () {
    Carbon::setTestNow('2024-01-01 12:00:00');
    $user = User::factory()->create(['trial_ends_at' => Carbon::parse('2024-01-11 12:00:00')]);

    expect($this->service->trialDaysRemaining($user))->toBe(10);

    Carbon::setTestNow();
});

it('returns zero on last day of trial', function () {
    Carbon::setTestNow('2024-01-15 08:00:00');
    $user = User::factory()->create(['trial_ends_at' => Carbon::parse('2024-01-15 23:59:59')]);

    expect($this->service->trialDaysRemaining($user))->toBe(0);

    Carbon::setTestNow();
});

it('returns one when exactly one day left', function () {
    Carbon::setTestNow('2024-01-15 00:00:00');
    $user = User::factory()->create(['trial_ends_at' => Carbon::parse('2024-01-16 00:00:00')]);

    expect($this->service->trialDaysRemaining($user))->toBe(1);

    Carbon::setTestNow();
});

// ============================================
// getUserPlan() tests
// ============================================

it('returns pro plan when user is on trial', function () {
    config(['plans.trial.tier' => 'pro']);
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);

    expect($this->service->getUserPlan($user))->toBe('pro');
});

it('returns configured trial tier', function () {
    config(['plans.trial.tier' => 'premium']);
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);

    expect($this->service->getUserPlan($user))->toBe('premium');
});

it('returns pro as default trial tier', function () {
    config(['plans.trial' => ['enabled' => true]]);
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);

    expect($this->service->getUserPlan($user))->toBe('pro');
});

it('returns free plan when not on trial', function () {
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->getUserPlan($user))->toBe('free');
});

it('returns free plan when trial expired', function () {
    $user = User::factory()->create(['trial_ends_at' => now()->subDays(1)]);

    expect($this->service->getUserPlan($user))->toBe('free');
});

// ============================================
// getLimit() tests
// ============================================

it('returns configured limit for free plan', function () {
    config(['plans.free.limits.api_tokens' => 3]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->getLimit($user, 'api_tokens'))->toBe(3);
});

it('returns configured limit for pro plan', function () {
    config(['plans.pro.limits.api_tokens' => 100]);
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);

    expect($this->service->getLimit($user, 'api_tokens'))->toBe(100);
});

it('returns null for unconfigured limit', function () {
    config(['plans.free.limits' => []]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->getLimit($user, 'nonexistent_limit'))->toBeNull();
});

it('returns pro limits during trial', function () {
    config(['plans.free.limits.projects' => 5]);
    config(['plans.pro.limits.projects' => 50]);
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);

    expect($this->service->getLimit($user, 'projects'))->toBe(50);
});

// ============================================
// canPerform() tests
// ============================================

it('returns true when under limit', function () {
    config(['plans.free.limits.api_tokens' => 5]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->canPerform($user, 'api_tokens', 3))->toBeTrue();
});

it('returns false when at limit', function () {
    config(['plans.free.limits.api_tokens' => 5]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->canPerform($user, 'api_tokens', 5))->toBeFalse();
});

it('returns false when over limit', function () {
    config(['plans.free.limits.api_tokens' => 5]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->canPerform($user, 'api_tokens', 10))->toBeFalse();
});

it('returns true when limit is null meaning unlimited', function () {
    config(['plans.free.limits.api_tokens' => null]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->canPerform($user, 'api_tokens', 1000))->toBeTrue();
});

it('returns true with zero current count', function () {
    config(['plans.free.limits.api_tokens' => 5]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->canPerform($user, 'api_tokens', 0))->toBeTrue();
});

it('returns false with zero limit and zero count', function () {
    config(['plans.free.limits.api_tokens' => 0]);
    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($this->service->canPerform($user, 'api_tokens', 0))->toBeFalse();
});

it('uses pro limits during trial for canPerform', function () {
    config(['plans.free.limits.projects' => 2]);
    config(['plans.pro.limits.projects' => 20]);
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);

    expect($this->service->canPerform($user, 'projects', 15))->toBeTrue();

    $expiredUser = User::factory()->create(['trial_ends_at' => now()->subDays(1)]);
    expect($this->service->canPerform($expiredUser, 'projects', 15))->toBeFalse();
});

// ============================================
// Cashier integration tests
// ============================================

it('returns pro plan when subscribed and billing enabled', function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    $user = User::factory()->create(['trial_ends_at' => null]);
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    expect($this->service->getUserPlan($user->fresh()))->toBe('pro');
});

it('returns team plan when team subscribed', function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    $user = User::factory()->create(['trial_ends_at' => null]);
    createTeamSubscription($user, 5);

    expect($this->service->getUserPlan($user->fresh()))->toBe('team');
});

it('returns enterprise plan when enterprise subscribed', function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    $user = User::factory()->create(['trial_ends_at' => null]);
    createEnterpriseSubscription($user, 10);

    expect($this->service->getUserPlan($user->fresh()))->toBe('enterprise');
});

it('returns free plan when billing disabled even if subscribed', function () {
    config(['features.billing.enabled' => false]);
    ensureCashierTablesExist();
    $user = User::factory()->create(['trial_ends_at' => null]);
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    expect($this->service->getUserPlan($user->fresh()))->toBe('free');
});

it('returns correct tier during grace period', function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    $user = User::factory()->create(['trial_ends_at' => null]);
    createTeamSubscription($user, 5, ['ends_at' => now()->addDays(5)]);

    expect($this->service->getUserPlan($user->fresh()))->toBe('team');
});

it('returns free plan when subscription ended', function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    $user = User::factory()->create(['trial_ends_at' => null]);
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    expect($this->service->getUserPlan($user->fresh()))->toBe('free');
});

it('gives trial precedence over subscription check', function () {
    config(['features.billing.enabled' => true]);
    config(['plans.trial.tier' => 'pro']);
    ensureCashierTablesExist();
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);

    expect($this->service->getUserPlan($user))->toBe('pro');
});

it('gives team subscription user team tier limits', function () {
    config(['features.billing.enabled' => true]);
    config(['plans.team.limits.api_tokens' => 25]);
    config(['plans.pro.limits.api_tokens' => 10]);
    ensureCashierTablesExist();
    $user = User::factory()->create(['trial_ends_at' => null]);
    createTeamSubscription($user, 5);

    expect($this->service->getLimit($user->fresh(), 'api_tokens'))->toBe(25);
});
