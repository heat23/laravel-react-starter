<?php

use App\Models\User;
use App\Services\PlanLimitService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['plans.pro.stripe_price_monthly' => 'price_pro_monthly']);
    ensureCashierTablesExist();
});

it('grants pro access during grace period for past_due subscription', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'past_due',
        'stripe_price' => 'price_pro_monthly',
        'updated_at' => now()->subDays(3), // 3 days into 7-day grace
    ]);

    $service = app(PlanLimitService::class);

    expect($service->getUserPlan($user->fresh()))->toBe('pro');
});

it('reverts to free tier after grace period expires', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'past_due',
        'stripe_price' => 'price_pro_monthly',
        'updated_at' => now()->subDays(8), // 8 days > 7-day grace
    ]);

    $service = app(PlanLimitService::class);

    expect($service->getUserPlan($user->fresh()))->toBe('free');
});

it('uses configurable grace period days', function () {
    config(['plans.past_due_grace_days' => 3]);

    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'past_due',
        'stripe_price' => 'price_pro_monthly',
        'updated_at' => now()->subDays(4), // 4 days > 3-day grace
    ]);

    $service = app(PlanLimitService::class);

    expect($service->getUserPlan($user->fresh()))->toBe('free');
});

it('grants access when past_due is exactly at grace boundary', function () {
    config(['plans.past_due_grace_days' => 7]);

    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'past_due',
        'stripe_price' => 'price_pro_monthly',
        'updated_at' => now()->subDays(6), // 6 days < 7-day grace
    ]);

    $service = app(PlanLimitService::class);

    expect($service->getUserPlan($user->fresh()))->toBe('pro');
});

it('active subscription is unaffected by grace period logic', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
    ]);

    $service = app(PlanLimitService::class);

    expect($service->getUserPlan($user->fresh()))->toBe('pro');
});
