<?php

/**
 * BILL-01: Stripe Tax integration in checkout and subscription flows.
 *
 * Verifies that automatic_tax is forwarded to Stripe when
 * config('features.billing.tax_enabled') is true, and omitted when false.
 */

use App\Models\User;
use App\Services\BillingService;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionBuilder;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['features.billing.coming_soon' => false]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

// --- createCheckoutSession: automatic_tax forwarding ---

it('includes automatic_tax in Stripe checkout options when tax is enabled', function () {
    config(['features.billing.tax_enabled' => true]);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $capturedOptions = null;

    // Mock the SubscriptionBuilder returned by User::newSubscription()
    $builderMock = Mockery::mock(SubscriptionBuilder::class);
    $builderMock->shouldReceive('quantity')->andReturnSelf();
    $builderMock->shouldReceive('checkout')
        ->once()
        ->andReturnUsing(function (array $opts) use (&$capturedOptions) {
            $capturedOptions = $opts;

            return (object) ['url' => 'https://checkout.stripe.com/pay/cs_test_tax'];
        });

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('newSubscription')->andReturn($builderMock);

    $service = new BillingService;
    $url = $service->createCheckoutSession(
        $userMock,
        'price_pro_monthly',
        1,
        'https://example.com/success',
        'https://example.com/cancel',
    );

    expect($url)->toBe('https://checkout.stripe.com/pay/cs_test_tax');
    expect($capturedOptions)->toHaveKey('automatic_tax');
    expect($capturedOptions['automatic_tax'])->toBe(['enabled' => true]);
});

it('omits automatic_tax from Stripe checkout options when tax is disabled', function () {
    config(['features.billing.tax_enabled' => false]);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $capturedOptions = null;

    $builderMock = Mockery::mock(SubscriptionBuilder::class);
    $builderMock->shouldReceive('quantity')->andReturnSelf();
    $builderMock->shouldReceive('checkout')
        ->once()
        ->andReturnUsing(function (array $opts) use (&$capturedOptions) {
            $capturedOptions = $opts;

            return (object) ['url' => 'https://checkout.stripe.com/pay/cs_test_notax'];
        });

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('newSubscription')->andReturn($builderMock);

    $service = new BillingService;
    $service->createCheckoutSession(
        $userMock,
        'price_pro_monthly',
        1,
        'https://example.com/success',
        'https://example.com/cancel',
    );

    expect($capturedOptions)->not->toHaveKey('automatic_tax');
});

it('preserves existing checkout options (success_url, cancel_url, discounts) when adding automatic_tax', function () {
    config(['features.billing.tax_enabled' => true]);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $capturedOptions = null;

    $builderMock = Mockery::mock(SubscriptionBuilder::class);
    $builderMock->shouldReceive('quantity')->andReturnSelf();
    $builderMock->shouldReceive('checkout')
        ->once()
        ->andReturnUsing(function (array $opts) use (&$capturedOptions) {
            $capturedOptions = $opts;

            return (object) ['url' => 'https://checkout.stripe.com/pay/cs_test_full'];
        });

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('newSubscription')->andReturn($builderMock);

    $service = new BillingService;
    $service->createCheckoutSession(
        $userMock,
        'price_pro_monthly',
        1,
        'https://example.com/success',
        'https://example.com/cancel',
        'SAVE20',
    );

    expect($capturedOptions)->toHaveKey('success_url');
    expect($capturedOptions)->toHaveKey('cancel_url');
    expect($capturedOptions)->toHaveKey('discounts');
    expect($capturedOptions)->toHaveKey('automatic_tax');
    expect($capturedOptions['automatic_tax'])->toBe(['enabled' => true]);
});

// --- createSubscription: automatic_tax forwarding ---

it('passes automatic_tax to SubscriptionBuilder::create when tax is enabled and paymentMethod is provided', function () {
    config(['features.billing.tax_enabled' => true]);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $capturedSubscriptionOptions = null;

    $builderMock = Mockery::mock(SubscriptionBuilder::class);
    $builderMock->shouldReceive('quantity')->andReturnSelf();
    $builderMock->shouldReceive('create')
        ->once()
        ->andReturnUsing(function ($paymentMethod, $customerParams, $subscriptionOptions) use (&$capturedSubscriptionOptions) {
            $capturedSubscriptionOptions = $subscriptionOptions;

            return Mockery::mock(Subscription::class);
        });

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('newSubscription')->andReturn($builderMock);

    $service = new BillingService;
    $service->createSubscription($userMock, 'price_pro_monthly', 'pm_test_card');

    expect($capturedSubscriptionOptions)->toBe(['automatic_tax' => ['enabled' => true]]);
});

it('passes automatic_tax to SubscriptionBuilder::create when tax is enabled and no paymentMethod', function () {
    config(['features.billing.tax_enabled' => true]);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $capturedSubscriptionOptions = null;
    $capturedPaymentMethod = 'NOT_CHECKED';

    $builderMock = Mockery::mock(SubscriptionBuilder::class);
    $builderMock->shouldReceive('quantity')->andReturnSelf();
    $builderMock->shouldReceive('create')
        ->once()
        ->andReturnUsing(function ($paymentMethod, $customerParams, $subscriptionOptions) use (&$capturedSubscriptionOptions, &$capturedPaymentMethod) {
            $capturedPaymentMethod = $paymentMethod;
            $capturedSubscriptionOptions = $subscriptionOptions;

            return Mockery::mock(Subscription::class);
        });

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('newSubscription')->andReturn($builderMock);

    $service = new BillingService;
    $service->createSubscription($userMock, 'price_pro_monthly');

    expect($capturedPaymentMethod)->toBeNull();
    expect($capturedSubscriptionOptions)->toBe(['automatic_tax' => ['enabled' => true]]);
});

it('passes empty subscriptionOptions to SubscriptionBuilder::create when tax is disabled', function () {
    config(['features.billing.tax_enabled' => false]);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $capturedSubscriptionOptions = null;

    $builderMock = Mockery::mock(SubscriptionBuilder::class);
    $builderMock->shouldReceive('quantity')->andReturnSelf();
    $builderMock->shouldReceive('create')
        ->once()
        ->andReturnUsing(function ($paymentMethod, $customerParams, $subscriptionOptions) use (&$capturedSubscriptionOptions) {
            $capturedSubscriptionOptions = $subscriptionOptions;

            return Mockery::mock(Subscription::class);
        });

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('newSubscription')->andReturn($builderMock);

    $service = new BillingService;
    $service->createSubscription($userMock, 'price_pro_monthly', 'pm_test_card');

    expect($capturedSubscriptionOptions)->toBe([]);
});

// --- config defaults ---

it('features.billing.tax_enabled defaults to false', function () {
    // Read the raw default directly from the config file, bypassing any runtime
    // mutations applied by other tests (e.g., config(['features.billing.tax_enabled' => true])).
    // This ensures the off-by-default guarantee holds at the source level so production
    // deployments cannot accidentally enable Stripe Tax before it is configured in
    // the Stripe Dashboard.
    $rawConfig = require base_path('config/features.php');
    expect($rawConfig['billing']['tax_enabled'])->toBeFalse();
});
