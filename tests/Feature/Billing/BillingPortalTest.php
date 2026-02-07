<?php

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('redirects subscribed user to stripe billing portal', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_portal_test',
    ]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('getBillingPortalUrl')
        ->once()
        ->andReturn('https://billing.stripe.com/session/test_session');
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->get('/billing/portal');

    $response->assertRedirect('https://billing.stripe.com/session/test_session');
});

it('requires authentication for billing portal', function () {
    $response = $this->get('/billing/portal');

    $response->assertRedirect(route('login'));
});

it('returns error for non-subscribed user without stripe id accessing portal', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/billing/portal');

    $response->assertSessionHas('error');
});
