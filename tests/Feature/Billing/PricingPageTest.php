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

it('pro description contains outcome language', function () {
    $response = $this->get('/pricing');

    $response->assertInertia(fn ($page) => $page
        ->where('tiers.pro.description', fn ($value) => str_contains($value, 'solo') || str_contains($value, 'product')
        )
    );
});
