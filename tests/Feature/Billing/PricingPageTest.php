<?php

use App\Models\User;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['features.billing.coming_soon' => false]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('renders pricing page for guests', function () {
    $response = $this->get('/pricing');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Pricing')
        ->has('tiers')
        ->has('trialEnabled')
        ->has('trialDays')
        ->where('currentPlan', null)
        ->where('trial', null)
    );
});

it('returns all configured tiers', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->has('tiers.free')
        ->has('tiers.pro')
        ->has('tiers.team')
        ->has('tiers.enterprise')
    );
});

it('includes tier details in response', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.free.name', 'Free')
        ->where('tiers.free.price', 0)
        ->where('tiers.pro.name', 'Pro')
        ->whereType('tiers.pro.price', 'integer')
        ->has('tiers.pro.features')
        ->has('tiers.pro.limits')
    );
});

it('returns current plan for authenticated users', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('currentPlan', 'free')
    );
});

it('returns trial info for users on trial', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(10),
    ]);

    $response = $this->actingAs($user)->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('trial.active', true)
        ->where('trial.daysRemaining', fn ($value) => $value >= 9 && $value <= 10)
        ->whereType('trial.endsAt', 'string')
    );
});

it('returns trial config flags', function () {
    config(['plans.trial.enabled' => true]);
    config(['plans.trial.days' => 7]);

    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('trialEnabled', true)
        ->where('trialDays', 7)
    );
});

it('includes per-seat config for team plans', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.team.per_seat', true)
        ->where('tiers.team.min_seats', 2)
        ->where('tiers.enterprise.per_seat', true)
        ->where('tiers.enterprise.min_seats', 10)
    );
});

it('pro tier is marked as popular', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.popular', true)
        ->where('tiers.free.popular', false)
        ->where('tiers.team.popular', false)
        ->where('tiers.enterprise.popular', false)
    );
});

it('pro annual price reflects 20 percent discount', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.price', 19)
        ->where('tiers.pro.price_annual', 182)
    );

    // Verify savings percent rounds to 20%
    $proMonthly = 19 * 12;
    $proAnnual = 182;
    $savingsPercent = (int) round(($proMonthly - $proAnnual) / $proMonthly * 100);
    expect($savingsPercent)->toBe(20);
});

it('team annual price reflects 20 percent discount', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.team.price_annual', 470)
    );

    $teamMonthly = 49 * 12;
    $teamAnnual = 470;
    $savingsPercent = (int) round(($teamMonthly - $teamAnnual) / $teamMonthly * 100);
    expect($savingsPercent)->toBe(20);
});

it('enterprise tier passes null price to hide rack rate', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.enterprise.price', null)
        ->where('tiers.enterprise.price_annual', null)
    );
});

it('shows control monthly price and Stripe ID when variant Stripe price is absent', function () {
    // Simulate cohort 1 (variant) visitor but no stripe_price_monthly_variant configured.
    // display price and Stripe price ID must both remain at control values.
    config(['plans.pro.price_monthly_variant' => 15]);
    // stripe_price_monthly_variant intentionally NOT set

    // Use a session ID that hashes to cohort 1 (isVariantCohort = true).
    // crc32 of 'variant-seed-1' => abs(crc32('variant-seed-1')) % 2 === 1
    $cohortOneSeed = collect(range(1, 10000))->first(fn ($i) => (abs(crc32((string) $i)) % 2) === 1);

    $user = User::factory()->create(['id' => $cohortOneSeed, 'email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/pricing');

    $controlMonthly = config('plans.pro.price_monthly');
    $controlStripeId = config('plans.pro.stripe_price_monthly');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.price', $controlMonthly)
        ->where('tiers.pro.stripe_price_id', $controlStripeId)
    );
});

it('swaps both monthly display price and Stripe ID atomically for variant cohort', function () {
    $variantPrice = 15;
    $variantStripeId = 'price_test_variant_monthly';

    config(['plans.pro.price_monthly_variant' => $variantPrice]);
    config(['plans.pro.stripe_price_monthly_variant' => $variantStripeId]);

    // Pick user ID in cohort 1 (variant cohort).
    $cohortOneSeed = collect(range(1, 10000))->first(fn ($i) => (abs(crc32((string) $i)) % 2) === 1);

    $user = User::factory()->create(['id' => $cohortOneSeed, 'email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.price', $variantPrice)
        ->where('tiers.pro.stripe_price_id', $variantStripeId)
    );
});

it('shows control annual price and Stripe ID when annual variant Stripe price is absent', function () {
    // Annual variant display price is set but stripe_price_annual_variant is not — no swap should occur.
    config(['plans.pro.price_annual_variant' => 120]);
    // stripe_price_annual_variant intentionally NOT set

    $cohortOneSeed = collect(range(1, 10000))->first(fn ($i) => (abs(crc32((string) $i)) % 2) === 1);

    $user = User::factory()->create(['id' => $cohortOneSeed, 'email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/pricing');

    $controlAnnual = config('plans.pro.price_annual');
    $controlStripeAnnualId = config('plans.pro.stripe_price_annual');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.price_annual', $controlAnnual)
        ->where('tiers.pro.stripe_price_id_annual', $controlStripeAnnualId)
    );
});

it('swaps both annual display price and Stripe ID atomically for variant cohort', function () {
    $variantAnnualPrice = 120;
    $variantAnnualStripeId = 'price_test_variant_annual';

    config(['plans.pro.price_annual_variant' => $variantAnnualPrice]);
    config(['plans.pro.stripe_price_annual_variant' => $variantAnnualStripeId]);

    $cohortOneSeed = collect(range(1, 10000))->first(fn ($i) => (abs(crc32((string) $i)) % 2) === 1);

    $user = User::factory()->create(['id' => $cohortOneSeed, 'email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.price_annual', $variantAnnualPrice)
        ->where('tiers.pro.stripe_price_id_annual', $variantAnnualStripeId)
    );
});

it('trial tier is driven by plans.trial.tier config', function () {
    config(['plans.trial.tier' => 'team']);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(7),
    ]);

    $response = $this->actingAs($user)->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('currentPlan', 'team')
    );
});

it('shows control monthly price when only Stripe monthly variant ID is set without display price', function () {
    // Only stripe_price_monthly_variant is configured; price_monthly_variant is absent.
    // The controller must NOT do a partial swap — control values must be returned.
    config(['plans.pro.stripe_price_monthly_variant' => 'price_test_stripe_only']);
    // price_monthly_variant intentionally NOT set

    $cohortOneSeed = collect(range(1, 10000))->first(fn ($i) => (abs(crc32((string) $i)) % 2) === 1);

    $user = User::factory()->create(['id' => $cohortOneSeed, 'email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/pricing');

    $controlMonthly = config('plans.pro.price_monthly');
    $controlStripeId = config('plans.pro.stripe_price_monthly');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.price', $controlMonthly)
        ->where('tiers.pro.stripe_price_id', $controlStripeId)
    );
});

it('shows control annual price when only Stripe annual variant ID is set without display price', function () {
    // Only stripe_price_annual_variant is configured; price_annual_variant is absent.
    // The controller must NOT do a partial swap — control values must be returned.
    config(['plans.pro.stripe_price_annual_variant' => 'price_test_annual_stripe_only']);
    // price_annual_variant intentionally NOT set

    $cohortOneSeed = collect(range(1, 10000))->first(fn ($i) => (abs(crc32((string) $i)) % 2) === 1);

    $user = User::factory()->create(['id' => $cohortOneSeed, 'email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/pricing');

    $controlAnnual = config('plans.pro.price_annual');
    $controlStripeAnnualId = config('plans.pro.stripe_price_annual');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.price_annual', $controlAnnual)
        ->where('tiers.pro.stripe_price_id_annual', $controlStripeAnnualId)
    );
});

it('pro description contains outcome language', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.description', fn ($value) => str_contains($value, 'solo') || str_contains($value, 'product')
        )
    );
});
