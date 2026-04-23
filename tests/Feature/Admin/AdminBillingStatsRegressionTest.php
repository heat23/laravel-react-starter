<?php

use App\Models\User;
use App\Services\AdminBillingStatsService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Regression tests ensuring AdminBillingStatsService returns correct
 * shapes after the Sitting-5 refactor (per-metric calculator extraction).
 *
 * Each test verifies the public method returns the expected type and key
 * structure against a seeded dataset. Zero behavior change from the
 * pre-refactor monolith is the invariant.
 */
beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    Cache::flush();
});

it('getDashboardStats returns all required keys with correct types', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $service = app(AdminBillingStatsService::class);
    $stats = $service->getDashboardStats();

    expect($stats)->toHaveKeys([
        'active_subscriptions',
        'trialing',
        'past_due',
        'canceled',
        'scheduled_cancellations',
        'total_ever',
        'mrr',
        'churn_rate',
        'trial_conversion_rate',
        'activation_rate',
        'activation_rate_all_time',
        'signup_to_paid_conversion',
        'cohort_conversion_30d',
        'cached_at',
    ]);

    expect($stats['active_subscriptions'])->toBeInt();
    expect($stats['total_ever'])->toBeInt();
    expect($stats['mrr'])->toBeFloat();
    expect($stats['churn_rate'])->toBeFloat();
    expect($stats['trial_conversion_rate'])->toBeFloat();
    expect($stats['activation_rate'])->toBeFloat();
    expect($stats['activation_rate_all_time'])->toBeFloat();
    expect($stats['signup_to_paid_conversion'])->toBeFloat();
    expect($stats['cohort_conversion_30d'])->toBeFloat();
    expect($stats['active_subscriptions'])->toBe(1);
    expect($stats['total_ever'])->toBe(1);
});

it('getTierDistribution returns array of tier/count maps', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $service = app(AdminBillingStatsService::class);
    $result = $service->getTierDistribution();

    expect($result)->toBeArray();
    if (count($result) > 0) {
        expect($result[0])->toHaveKeys(['tier', 'count']);
        expect($result[0]['count'])->toBeInt();
    }
});

it('getStatusBreakdown returns array of status/count maps', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $service = app(AdminBillingStatsService::class);
    $result = $service->getStatusBreakdown();

    expect($result)->toBeArray();
    expect($result)->not->toBeEmpty();
    expect($result[0])->toHaveKeys(['status', 'count']);
    expect($result[0]['count'])->toBeInt();
});

it('getGrowthChart returns array of date/count maps', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $service = app(AdminBillingStatsService::class);
    $result = $service->getGrowthChart();

    expect($result)->toBeArray();
    if (count($result) > 0) {
        expect($result[0])->toHaveKeys(['date', 'count']);
        expect($result[0]['count'])->toBeInt();
    }
});

it('getTrialStats returns active_trials and expiring_soon integers', function () {
    $service = app(AdminBillingStatsService::class);
    $result = $service->getTrialStats();

    expect($result)->toHaveKeys(['active_trials', 'expiring_soon']);
    expect($result['active_trials'])->toBeInt();
    expect($result['expiring_soon'])->toBeInt();
});

it('getChurnBreakdown returns voluntary and involuntary integer counts', function () {
    $service = app(AdminBillingStatsService::class);
    $result = $service->getChurnBreakdown();

    expect($result)->toHaveKeys(['voluntary', 'involuntary']);
    expect($result['voluntary'])->toBeInt();
    expect($result['involuntary'])->toBeInt();
});

it('getCohortRetention returns array of cohort rows', function () {
    $user = User::factory()->create();

    $service = app(AdminBillingStatsService::class);
    $result = $service->getCohortRetention();

    expect($result)->toBeArray();
    if (count($result) > 0) {
        expect($result[0])->toHaveKeys(['cohort', 'total']);
        expect($result[0]['total'])->toBeInt();
    }
});

it('getFilteredSubscriptions returns paginator with expected shape', function () {
    $user = User::factory()->create(['name' => 'RegressionUser', 'email' => 'regression@example.com']);
    createSubscription($user);

    $service = app(AdminBillingStatsService::class);
    $result = $service->getFilteredSubscriptions([]);

    expect($result->total())->toBeGreaterThanOrEqual(1);
    $item = $result->items()[0];
    expect($item)->toHaveKeys([
        'id', 'user_id', 'user_name', 'user_email',
        'stripe_status', 'tier', 'quantity',
        'trial_ends_at', 'ends_at', 'created_at',
    ]);
});

it('buildSubscriptionQuery returns a query Builder instance', function () {
    $service = app(AdminBillingStatsService::class);
    $result = $service->buildSubscriptionQuery([]);

    expect($result)->toBeInstanceOf(Builder::class);
});

it('getDashboardStats is served from cache on second call', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $service = app(AdminBillingStatsService::class);
    $first = $service->getDashboardStats();

    // Add another subscription — should not appear due to cache
    $user2 = User::factory()->create();
    createSubscription($user2);

    $second = $service->getDashboardStats();

    expect($second['total_ever'])->toBe($first['total_ever']);
});
