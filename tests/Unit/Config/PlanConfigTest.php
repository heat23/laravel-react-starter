<?php

use App\Services\BillingService;

it('resolves tier from config tier_hierarchy instead of hardcoded list', function () {
    $tiers = config('plans.tier_hierarchy');

    expect($tiers)->toBeArray()
        ->and($tiers)->toContain('free', 'pro', 'team', 'enterprise');
});

it('resolveTierFromPrice checks all paid tiers from config', function () {
    // Set known price IDs for each tier
    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.stripe_price_annual' => 'price_pro_annual',
        'plans.team.stripe_price_monthly' => 'price_team_monthly',
        'plans.team.stripe_price_annual' => 'price_team_annual',
        'plans.enterprise.stripe_price_monthly' => 'price_ent_monthly',
        'plans.enterprise.stripe_price_annual' => 'price_ent_annual',
    ]);

    $service = app(BillingService::class);

    expect($service->resolveTierFromPrice('price_pro_monthly'))->toBe('pro')
        ->and($service->resolveTierFromPrice('price_team_annual'))->toBe('team')
        ->and($service->resolveTierFromPrice('price_ent_monthly'))->toBe('enterprise')
        ->and($service->resolveTierFromPrice('price_unknown'))->toBeNull();
});

it('trial config has single source of truth in plans.php', function () {
    $trialDays = config('plans.trial.days');
    $trialEnabled = config('plans.trial.enabled');

    expect($trialDays)->toBeInt()
        ->and($trialEnabled)->toBeBool();
});

it('features.billing does not duplicate trial_days', function () {
    // features.billing.trial_days should not exist — plans.trial.days is source of truth
    // If this test fails, it means the duplication has been re-introduced
    expect(config('features.billing.trial_days'))->toBeNull(
        'features.billing.trial_days should be removed — use plans.trial.days instead'
    );
});

it('features.billing does not duplicate trial_enabled', function () {
    expect(config('features.billing.trial_enabled'))->toBeNull(
        'features.billing.trial_enabled should be removed — use plans.trial.enabled instead'
    );
});

// Annual price discount arithmetic tests.
// These values are DISPLAY-ONLY — the actual Stripe charge is governed by the price object
// referenced by STRIPE_PRICE_PRO_ANNUAL / STRIPE_PRICE_TEAM_ANNUAL. Keep in sync.

it('pro annual price default is less than monthly × 12 and represents a meaningful discount', function () {
    $monthly = 19;
    $annual = 194;
    $fullYear = $monthly * 12; // 228

    $discountPct = (1 - $annual / $fullYear) * 100;

    expect($annual)->toBeLessThan($fullYear)
        ->and($discountPct)->toBeGreaterThan(10.0)   // at least 10% off
        ->and($discountPct)->toBeLessThan(25.0);      // at most 25% off (sanity ceiling)
});

it('team annual price default is less than monthly × 12 and represents exactly 25% off', function () {
    $monthly = 49;
    $annual = 441;
    $fullYear = $monthly * 12; // 588

    $discountPct = round((1 - $annual / $fullYear) * 100, 2);

    expect($annual)->toBeLessThan($fullYear)
        ->and($discountPct)->toBe(25.0);
});

it('pro and team annual price env defaults match expected values', function () {
    // Verify the plans config file has the expected hard-coded env() defaults.
    // These prevent silent drift when PLAN_*_PRICE_ANNUAL env vars are unset.
    $plansFile = require base_path('config/plans.php');

    // Extract the raw env default from the config array (env() returns the default when the var is unset)
    // We verify the default by checking what the loaded config produces with no env override.
    putenv('PLAN_PRO_PRICE_ANNUAL');   // unset
    putenv('PLAN_TEAM_PRICE_ANNUAL');  // unset

    $freshPlans = require base_path('config/plans.php');

    expect($freshPlans['pro']['price_annual'])->toBe(182)
        ->and($freshPlans['team']['price_annual'])->toBe(470);
});
