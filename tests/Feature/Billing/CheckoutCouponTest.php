<?php

/**
 * CCF-001: Coupon code passed through Stripe Checkout session.
 *
 * The checkout() controller method must extract and forward the coupon
 * field to BillingService::createCheckoutSession().
 */

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['features.billing.coming_soon' => false]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('passes coupon code to createCheckoutSession when provided', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $capturedCoupon = null;
    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resolveTierFromPrice')->andReturn('pro');
    $mock->shouldReceive('validateSeatCount')->andReturn(null);
    $mock->shouldReceive('createCheckoutSession')
        ->once()
        ->andReturnUsing(function ($u, $priceId, $qty, $successUrl, $cancelUrl, $coupon) use (&$capturedCoupon) {
            $capturedCoupon = $coupon;

            return 'https://checkout.stripe.com/pay/cs_test_123';
        });
    app()->instance(BillingService::class, $mock);

    $this->actingAs($user)->post('/billing/checkout', [
        'price_id' => 'price_pro_monthly',
        'coupon' => 'SAVE20',
    ]);

    expect($capturedCoupon)->toBe('SAVE20');
});

it('omits coupon when not provided', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $capturedCoupon = 'NOT_SET';
    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resolveTierFromPrice')->andReturn('pro');
    $mock->shouldReceive('validateSeatCount')->andReturn(null);
    $mock->shouldReceive('createCheckoutSession')
        ->once()
        ->andReturnUsing(function ($u, $priceId, $qty, $successUrl, $cancelUrl, $coupon) use (&$capturedCoupon) {
            $capturedCoupon = $coupon;

            return 'https://checkout.stripe.com/pay/cs_test_456';
        });
    app()->instance(BillingService::class, $mock);

    $this->actingAs($user)->post('/billing/checkout', [
        'price_id' => 'price_pro_monthly',
    ]);

    expect($capturedCoupon)->toBeNull();
});

it('rejects invalid coupon format in checkout', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/billing/checkout', [
        'price_id' => 'price_pro_monthly',
        'coupon' => 'INVALID COUPON WITH SPACES',
    ]);

    $response->assertSessionHasErrors('coupon');
});

it('rejects checkout when user already has active subscription', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resolveTierFromPrice')->andReturn('pro');
    $mock->shouldReceive('validateSeatCount')->andReturn(null);
    app()->instance(BillingService::class, $mock);

    // Simulate subscribed user by creating a subscription record
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $response = $this->actingAs($user)->post('/billing/checkout', [
        'price_id' => 'price_pro_monthly',
    ]);

    $response->assertSessionHas('error');
});
