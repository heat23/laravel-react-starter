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
