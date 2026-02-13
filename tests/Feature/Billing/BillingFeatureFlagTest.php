<?php

use App\Models\User;

beforeEach(function () {
    ensureCashierTablesExist();
});

it('returns 404 for billing routes when feature disabled', function () {
    // NOTE: This test is skipped because phpunit.xml enables FEATURE_BILLING=true at boot time
    // for route registration. Routes registered at boot cannot be unregistered dynamically.
    // To test boot-time route registration behavior, run tests with FEATURE_BILLING=false in env.
    $this->markTestSkipped('Cannot test boot-time route registration when billing is enabled in phpunit.xml');
})->skip('Boot-time feature flag behavior');

it('billing routes exist when billing enabled and routes registered', function () {
    config(['features.billing.enabled' => true]);
    registerBillingRoutes();
    $user = User::factory()->create(['email_verified_at' => now()]);

    // The subscribe route should not be 404 — it should redirect or validate
    $response = $this->actingAs($user)->post('/billing/subscribe', []);

    // We expect validation error (422 redirect), not 404
    $response->assertSessionHasErrors('price_id');
});

it('shares billing feature flag in inertia props', function () {
    config(['features.billing.enabled' => true]);
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertInertia(fn ($page) => $page->where('features.billing', true));
});

it('does not share subscription data when billing disabled', function () {
    config(['features.billing.enabled' => false]);
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertInertia(fn ($page) => $page
        ->where('features.billing', false)
        ->where('auth.user.subscription', null)
    );
});
