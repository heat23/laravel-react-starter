<?php

use App\Services\AdminBillingStatsService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    Cache::flush();
});

it('calculates MRR from active subscriptions', function () {
    $service = app(AdminBillingStatsService::class);

    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.price_monthly' => 29,
    ]);

    $user = \App\Models\User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $stats = $service->getDashboardStats();
    expect($stats['mrr'])->toBe(29.0);
});

it('calculates MRR with annual pricing divided by 12', function () {
    $service = app(AdminBillingStatsService::class);

    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.stripe_price_annual' => 'price_pro_annual',
        'plans.pro.price_annual' => 240,
    ]);

    $user = \App\Models\User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_annual']);

    $stats = $service->getDashboardStats();
    expect($stats['mrr'])->toBe(20.0);
});

it('calculates MRR with quantity multiplier', function () {
    $service = app(AdminBillingStatsService::class);

    config([
        'plans.team.stripe_price_monthly' => 'price_team_monthly',
        'plans.team.price_monthly' => 15,
    ]);

    $user = \App\Models\User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_team_monthly', 'quantity' => 5]);

    $stats = $service->getDashboardStats();
    expect($stats['mrr'])->toBe(75.0);
});

it('returns zero MRR when no active subscriptions', function () {
    $service = app(AdminBillingStatsService::class);

    $stats = $service->getDashboardStats();
    expect($stats['mrr'])->toBe(0.0);
});

it('excludes canceled subscriptions from MRR', function () {
    $service = app(AdminBillingStatsService::class);

    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.price_monthly' => 29,
    ]);

    $user = \App\Models\User::factory()->create();
    createSubscription($user, [
        'stripe_price' => 'price_pro_monthly',
        'ends_at' => now()->addDays(5),
    ]);

    $stats = $service->getDashboardStats();
    expect($stats['mrr'])->toBe(0.0);
});

it('calculates churn rate over 30 days', function () {
    $service = app(AdminBillingStatsService::class);

    // Create a subscription that was active 30 days ago, now canceled
    $user1 = \App\Models\User::factory()->create();
    $sub1 = createSubscription($user1);
    $sub1->forceFill([
        'created_at' => now()->subDays(60),
        'ends_at' => now()->subDays(10),
    ])->saveQuietly();

    // Create a subscription still active (created before the period)
    $user2 = \App\Models\User::factory()->create();
    $sub2 = createSubscription($user2);
    $sub2->forceFill(['created_at' => now()->subDays(60)])->saveQuietly();

    // 1 canceled out of 2 active at start = 50%
    $stats = $service->getDashboardStats();
    expect($stats['churn_rate'])->toBe(50.0);
});

it('returns zero churn rate with no subscriptions', function () {
    $service = app(AdminBillingStatsService::class);

    $stats = $service->getDashboardStats();
    expect($stats['churn_rate'])->toBe(0.0);
});

it('calculates trial conversion rate', function () {
    $service = app(AdminBillingStatsService::class);

    // Converted trial (now active)
    $user1 = \App\Models\User::factory()->create();
    createSubscription($user1, [
        'stripe_status' => 'active',
        'trial_ends_at' => now()->subDays(5),
    ]);

    // Unconverted trial (canceled)
    $user2 = \App\Models\User::factory()->create();
    createSubscription($user2, [
        'stripe_status' => 'canceled',
        'trial_ends_at' => now()->subDays(3),
        'ends_at' => now()->subDay(),
    ]);

    // 1 converted out of 2 trialed = 50%
    $stats = $service->getDashboardStats();
    expect($stats['trial_conversion_rate'])->toBe(50.0);
});

it('returns zero trial conversion with no trials', function () {
    $service = app(AdminBillingStatsService::class);

    $stats = $service->getDashboardStats();
    expect($stats['trial_conversion_rate'])->toBe(0.0);
});

it('returns dashboard stats with all metrics', function () {
    $service = app(AdminBillingStatsService::class);

    $user = \App\Models\User::factory()->create();
    createSubscription($user);

    $stats = $service->getDashboardStats();

    expect($stats)->toHaveKeys([
        'active_subscriptions',
        'trialing',
        'past_due',
        'canceled',
        'total_ever',
        'mrr',
        'churn_rate',
        'trial_conversion_rate',
    ]);
    expect($stats['active_subscriptions'])->toBe(1);
    expect($stats['total_ever'])->toBe(1);
});

it('computes activation_rate correctly', function () {
    $service = app(AdminBillingStatsService::class);

    // Create 4 users, 2 with onboarding completed
    \App\Models\User::factory()->count(2)->create();

    $activatedUser1 = \App\Models\User::factory()->create();
    \Illuminate\Support\Facades\DB::table('user_settings')->insert([
        'user_id' => $activatedUser1->id,
        'key' => 'onboarding_completed',
        'value' => now()->toISOString(),
    ]);

    $activatedUser2 = \App\Models\User::factory()->create();
    \Illuminate\Support\Facades\DB::table('user_settings')->insert([
        'user_id' => $activatedUser2->id,
        'key' => 'onboarding_completed',
        'value' => now()->toISOString(),
    ]);

    $stats = $service->getDashboardStats();

    // 2 activated out of 4 total = 50%
    expect($stats['activation_rate'])->toBe(50.0);
});

it('computes signup_to_paid_conversion correctly', function () {
    $service = app(AdminBillingStatsService::class);

    // Create 4 users, 1 with active subscription
    \App\Models\User::factory()->count(3)->create();

    $paidUser = \App\Models\User::factory()->create();
    createSubscription($paidUser);

    $stats = $service->getDashboardStats();

    // 1 paid out of 4 total = 25%
    expect($stats['signup_to_paid_conversion'])->toBe(25.0);
});

it('returns zero activation rate with no users', function () {
    // Delete all users first
    \App\Models\User::query()->forceDelete();

    $service = app(AdminBillingStatsService::class);

    $stats = $service->getDashboardStats();

    expect($stats['activation_rate'])->toBe(0.0);
    expect($stats['signup_to_paid_conversion'])->toBe(0.0);
});

it('caches dashboard stats', function () {
    $service = app(AdminBillingStatsService::class);

    $stats1 = $service->getDashboardStats();

    // Create another subscription — should not appear due to cache
    $user = \App\Models\User::factory()->create();
    createSubscription($user);

    $stats2 = $service->getDashboardStats();

    expect($stats2['total_ever'])->toBe($stats1['total_ever']);
});
