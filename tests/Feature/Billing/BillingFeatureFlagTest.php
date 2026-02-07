<?php

use App\Models\User;

beforeEach(function () {
    ensureCashierTablesExist();
});

it('returns 404 for billing routes when feature disabled', function () {
    // When billing is disabled (default), routes are not registered at boot time.
    // Since billing is off by default in tests, these URLs should 404.
    config(['features.billing.enabled' => false]);
    $user = User::factory()->create(['email_verified_at' => now()]);

    // Actual billing routes should 404 because routes were never registered (config was false at boot)
    $this->actingAs($user)->post('/billing/subscribe')->assertNotFound();
    $this->actingAs($user)->post('/billing/cancel')->assertNotFound();
    $this->actingAs($user)->post('/billing/swap')->assertNotFound();
    $this->actingAs($user)->get('/pricing')->assertNotFound();
});

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
