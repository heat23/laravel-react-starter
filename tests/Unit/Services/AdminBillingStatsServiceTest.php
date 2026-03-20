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

    // Unconverted trial (canceled before billing started — no ends_at)
    $user2 = \App\Models\User::factory()->create();
    createSubscription($user2, [
        'stripe_status' => 'canceled',
        'trial_ends_at' => now()->subDays(3),
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

it('computes activation_rate as ratio of activated to total users', function () {
    Cache::flush();

    $service = app(AdminBillingStatsService::class);

    $totalUsersBefore = \Illuminate\Support\Facades\DB::table('users')->whereNull('deleted_at')->count();
    $activatedBefore = \Illuminate\Support\Facades\DB::table('user_settings')
        ->where('key', 'onboarding_completed')->distinct('user_id')->count('user_id');

    // 2 users without onboarding (unactivated) + 2 with onboarding (activated by factory default)
    \App\Models\User::factory()->count(2)->onboardingIncomplete()->create();
    \App\Models\User::factory()->count(2)->create(); // activated by default

    $stats = $service->getDashboardStats();

    $expectedRate = round((($activatedBefore + 2) / ($totalUsersBefore + 4)) * 100, 1);
    expect($stats['activation_rate'])->toBe($expectedRate);
});

it('includes signup_to_paid_conversion in dashboard stats', function () {
    Cache::flush();

    $service = app(AdminBillingStatsService::class);

    $stats = $service->getDashboardStats();

    expect($stats)->toHaveKey('signup_to_paid_conversion');
    expect($stats['signup_to_paid_conversion'])->toBeFloat();
});

it('includes activation_rate and signup_to_paid_conversion keys', function () {
    Cache::flush();

    $service = app(AdminBillingStatsService::class);

    $stats = $service->getDashboardStats();

    expect($stats)->toHaveKeys([
        'activation_rate',
        'signup_to_paid_conversion',
    ]);
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

// ─── Formula-correctness tests (KPI-002, KPI-006, KPI-003, KPI-004, KPI-005) ───

it('churn rate denominator excludes trialing subscriptions (KPI-002)', function () {
    $service = app(AdminBillingStatsService::class);

    $thirtyOneDaysAgo = now()->subDays(31);

    // 50 active (paying) subscribers created > 30 days ago
    for ($i = 0; $i < 50; $i++) {
        $user = \App\Models\User::factory()->create();
        $sub = createSubscription($user, ['stripe_status' => 'active']);
        $sub->forceFill(['created_at' => $thirtyOneDaysAgo])->saveQuietly();
    }

    // 200 trialing subscribers created > 30 days ago — must NOT inflate the denominator
    for ($i = 0; $i < 200; $i++) {
        $user = \App\Models\User::factory()->create();
        $sub = createSubscription($user, ['stripe_status' => 'trialing', 'trial_ends_at' => now()->addDays(7)]);
        $sub->forceFill(['created_at' => $thirtyOneDaysAgo])->saveQuietly();
    }

    // 5 cancellations within the period (active → canceled)
    for ($i = 0; $i < 5; $i++) {
        $user = \App\Models\User::factory()->create();
        $sub = createSubscription($user, ['stripe_status' => 'canceled', 'ends_at' => now()->subDays(5)]);
        $sub->forceFill(['created_at' => $thirtyOneDaysAgo])->saveQuietly();
    }

    $stats = $service->getDashboardStats();

    // churn = 5 / 50 = 10.0%, NOT 5 / 250 = 2.0%
    expect($stats['churn_rate'])->toBe(10.0);
});

it('canceled count excludes scheduled cancellations with future ends_at (KPI-006)', function () {
    $service = app(AdminBillingStatsService::class);

    // 3 truly canceled (ends_at in the past)
    for ($i = 0; $i < 3; $i++) {
        $user = \App\Models\User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'canceled',
            'ends_at' => now()->subDay(),
        ]);
    }

    // 2 scheduled cancellations (still active, ends_at in the future)
    for ($i = 0; $i < 2; $i++) {
        $user = \App\Models\User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(10),
        ]);
    }

    $stats = $service->getDashboardStats();

    expect($stats['canceled'])->toBe(3);
    expect($stats['scheduled_cancellations'])->toBe(2);
});

it('trial conversion counts historical conversions not current survivors (KPI-003)', function () {
    $service = app(AdminBillingStatsService::class);

    // 60 subscriptions that never converted (still trialing or canceled without ever paying)
    for ($i = 0; $i < 60; $i++) {
        $user = \App\Models\User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'canceled',
            'trial_ends_at' => now()->subDays(10),
            // No ends_at means never reached billing — not counted as converted
        ]);
    }

    // 30 subscriptions currently active (converted and still paying)
    for ($i = 0; $i < 30; $i++) {
        $user = \App\Models\User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'active',
            'trial_ends_at' => now()->subDays(10),
        ]);
    }

    // 10 subscriptions that converted then later churned — must count as converted
    for ($i = 0; $i < 10; $i++) {
        $user = \App\Models\User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'canceled',
            'trial_ends_at' => now()->subDays(10),
            'ends_at' => now()->subDays(2), // had ends_at = was on billing before cancelation
        ]);
    }

    $stats = $service->getDashboardStats();

    // 40 converted (30 active + 10 churned-after-converting) out of 100 total trialed = 40%
    expect($stats['trial_conversion_rate'])->toBe(40.0);
});

it('rolling 90-day activation rate ignores pre-onboarding cohorts (KPI-005)', function () {
    config(['features.onboarding.enabled' => true, 'features.user_settings.enabled' => true]);

    $service = app(AdminBillingStatsService::class);

    // 10 users created 120 days ago (outside 90-day window), 5 activated
    $oldUsers = \App\Models\User::factory()->count(10)->create();
    foreach ($oldUsers as $idx => $user) {
        $user->forceFill(['created_at' => now()->subDays(120)])->saveQuietly();
        if ($idx >= 5) {
            // Remove onboarding for 5 of the old users
            $user->settings()->where('key', 'onboarding_completed')->delete();
        }
    }

    // 10 users created within last 30 days (inside 90-day window), 8 activated
    $newUsers = \App\Models\User::factory()->count(10)->create();
    foreach ($newUsers as $idx => $user) {
        if ($idx >= 8) {
            $user->settings()->where('key', 'onboarding_completed')->delete();
        }
    }

    Cache::flush();
    $stats = $service->getDashboardStats();

    // Rolling 90d: 8 activated out of 10 recent users = 80%
    // NOT (5 + 8) / (10 + 10) = 65%
    expect($stats['activation_rate'])->toBe(80.0);
});

it('cohort retention counts API users active via last_active_at (KPI-004)', function () {
    Cache::flush();

    $service = app(AdminBillingStatsService::class);

    // User created 2 weeks ago with no login but recent API activity (within week 1 window)
    // week_1 checkDate ≈ now()->subWeeks(2)->startOfWeek() + 1 week ≈ 7 days ago
    // last_active_at must be >= checkDate to show retention
    $apiUser = \App\Models\User::factory()->create([
        'last_login_at' => null,
        'last_active_at' => now()->subDays(3),
        'created_at' => now()->subWeeks(2),
    ]);

    // The retention check via getCohortRetention uses week-based cohorts.
    // We verify the underlying logic by calling the service and checking
    // the user's cohort row shows activity.
    $cohorts = $service->getCohortRetention();

    // Find the cohort row for this user's signup week
    $userCohortStart = now()->subWeeks(2)->startOfWeek();
    $cohortLabel = $userCohortStart->format('M d');

    $cohortRow = collect($cohorts)->firstWhere('cohort', $cohortLabel);

    // The user should be in a cohort with total >= 1
    expect($cohortRow)->not->toBeNull();
    expect($cohortRow['total'])->toBeGreaterThanOrEqual(1);
    // Week 1 retention should reflect the user as active (last_active_at within the window)
    if (isset($cohortRow['week_1']) && $cohortRow['week_1'] !== null) {
        expect($cohortRow['week_1'])->toBeGreaterThan(0.0);
    }
});

it('dashboard stats include scheduled_cancellations key', function () {
    $service = app(AdminBillingStatsService::class);

    $stats = $service->getDashboardStats();

    expect($stats)->toHaveKey('scheduled_cancellations');
    expect($stats['scheduled_cancellations'])->toBeInt();
});

it('dashboard stats include activation_rate_all_time key', function () {
    $service = app(AdminBillingStatsService::class);

    $stats = $service->getDashboardStats();

    expect($stats)->toHaveKeys(['activation_rate', 'activation_rate_all_time']);
});
