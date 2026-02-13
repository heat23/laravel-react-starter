<?php

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['features.billing.coming_soon' => false]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('renders billing page for authenticated user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/billing');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Billing/Index')
        ->has('subscription')
        ->has('platformTrial')
        ->has('incompletePayment')
        ->has('invoices')
        ->has('graceDays')
    );
});

it('returns null subscription when user has no subscription', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/billing');

    $response->assertInertia(fn ($page) => $page
        ->where('subscription', null)
        ->where('platformTrial', null)
        ->where('incompletePayment', null)
        ->where('invoices', [])
    );
});

it('returns subscription info when user is subscribed', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
    ]);

    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.name' => 'Pro',
    ]);

    $response = $this->actingAs($user)->get('/billing');

    $response->assertInertia(fn ($page) => $page
        ->where('subscription.name', 'Pro')
        ->where('subscription.status', 'active')
        ->where('subscription.active', true)
        ->where('subscription.canceled', false)
        ->where('subscription.onGracePeriod', false)
    );
});

it('returns platform trial info when user is on trial', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(7),
    ]);

    $response = $this->actingAs($user)->get('/billing');

    $response->assertInertia(fn ($page) => $page
        ->where('platformTrial.daysRemaining', fn ($value) => $value >= 6 && $value <= 7)
        ->whereType('platformTrial.endsAt', 'string')
    );
});

it('returns grace days from config', function () {
    config(['plans.past_due_grace_days' => 14]);
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/billing');

    $response->assertInertia(fn ($page) => $page
        ->where('graceDays', 14)
    );
});

it('requires authentication to access billing page', function () {
    $response = $this->get('/billing');

    $response->assertRedirect(route('login'));
});

it('sanitizes invoice URLs to only allow stripe.com https links', function () {
    $service = new \App\Http\Controllers\Billing\BillingController(
        app(BillingService::class),
        app(\App\Services\PlanLimitService::class),
    );

    $method = new ReflectionMethod($service, 'sanitizeInvoiceUrl');

    expect($method->invoke($service, 'https://invoice.stripe.com/i/abc123'))->toBe('https://invoice.stripe.com/i/abc123');
    expect($method->invoke($service, 'http://invoice.stripe.com/i/abc123'))->toBeNull();
    expect($method->invoke($service, 'https://evil.com/invoice'))->toBeNull();
    expect($method->invoke($service, null))->toBeNull();
    expect($method->invoke($service, ''))->toBeNull();
});
