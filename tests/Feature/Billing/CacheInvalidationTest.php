<?php

use App\Models\User;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['plans.pro.stripe_price_monthly' => 'price_pro_monthly']);
    ensureCashierTablesExist();
});

it('caches user plan tier', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
    ]);

    $service = app(PlanLimitService::class);
    $user = $user->fresh();

    // First call resolves and caches
    $plan = $service->getUserPlan($user);
    expect($plan)->toBe('pro');

    // Verify it's cached
    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeTrue();
});

it('invalidates plan cache', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
    ]);

    $service = app(PlanLimitService::class);
    $user = $user->fresh();

    // Populate cache
    $service->getUserPlan($user);
    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeTrue();

    // Invalidate
    $service->invalidateUserPlanCache($user);
    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeFalse();
});

it('returns fresh tier after cache invalidation', function () {
    $user = User::factory()->create();
    $subscription = createSubscription($user, [
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
    ]);

    $service = app(PlanLimitService::class);
    $user = $user->fresh();

    // Cache as pro
    expect($service->getUserPlan($user))->toBe('pro');

    // Simulate subscription cancellation (expired grace)
    $subscription->update([
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    // Still cached as pro
    expect($service->getUserPlan($user->fresh()))->toBe('pro');

    // Invalidate cache
    $service->invalidateUserPlanCache($user);

    // Now returns free (subscription ended)
    expect($service->getUserPlan($user->fresh()))->toBe('free');
});
