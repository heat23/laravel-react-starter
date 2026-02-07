<?php

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('resolves pro tier from monthly price', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('pro');
});

it('resolves pro tier from annual price', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_annual']);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('pro');
});

it('resolves team tier from monthly price', function () {
    $user = User::factory()->create();
    createTeamSubscription($user);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('team');
});

it('resolves team tier from annual price', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_team_annual', 'quantity' => 5]);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('team');
});

it('resolves enterprise tier from monthly price', function () {
    $user = User::factory()->create();
    createEnterpriseSubscription($user);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('enterprise');
});

it('resolves enterprise tier from annual price', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_enterprise_annual', 'quantity' => 10]);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('enterprise');
});

it('falls back to free for unknown stripe price', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_unknown_legacy']);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('free');
});

it('resolves free tier when no subscription exists', function () {
    $user = User::factory()->create();

    $tier = app(BillingService::class)->resolveUserTier($user);

    expect($tier)->toBe('free');
});

it('resolves free tier when subscription is canceled and expired', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('free');
});

it('resolves correct tier during grace period', function () {
    $user = User::factory()->create();
    createTeamSubscription($user, 5, [
        'ends_at' => now()->addDays(5),
    ]);

    $tier = app(BillingService::class)->resolveUserTier($user->fresh());

    expect($tier)->toBe('team');
});
