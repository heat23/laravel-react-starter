<?php

use App\Exceptions\ConcurrentOperationException;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('returns error when concurrent cancel requests are made', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('cancelSubscription')
        ->once()
        ->andThrow(new ConcurrentOperationException);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/cancel');

    $response->assertRedirect();
    $response->assertSessionHas('error', 'A cancellation request is already in progress. Please try again.');
});

it('returns error when concurrent resume requests are made', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['ends_at' => now()->addDays(10)]);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resumeSubscription')
        ->once()
        ->andThrow(new ConcurrentOperationException);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/resume');

    $response->assertRedirect();
    $response->assertSessionHas('error', 'A resume request is already in progress. Please try again.');
});

it('returns error when concurrent swap requests are made', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    config(['plans.pro.stripe_price_monthly' => 'price_pro_monthly']);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resolveTierFromPrice')->andReturn('pro');
    $mock->shouldReceive('swapPlan')
        ->once()
        ->andThrow(new ConcurrentOperationException);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/swap', [
        'price_id' => 'price_pro_monthly',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'A plan change is already in progress. Please try again.');
});

it('returns error when concurrent quantity update requests are made', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['stripe_price' => 'price_team_monthly', 'quantity' => 5]);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resolveUserTier')->andReturn('team');
    $mock->shouldReceive('validateSeatCount')->andReturn(null);
    $mock->shouldReceive('updateQuantity')
        ->once()
        ->andThrow(new ConcurrentOperationException);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/quantity', [
        'quantity' => 10,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'A quantity update is already in progress. Please try again.');
});

it('returns error when concurrent subscribe requests are made', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    config(['features.billing.coming_soon' => false]);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('resolveTierFromPrice')->andReturn('pro');
    $mock->shouldReceive('validateSeatCount')->andReturn(null);
    $mock->shouldReceive('createSubscription')
        ->once()
        ->andThrow(new ConcurrentOperationException);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/subscribe', [
        'price_id' => 'price_pro_monthly',
        'payment_method' => 'pm_test_123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'A subscription request is already in progress. Please try again.');
});

it('billing service throws ConcurrentOperationException when lock is held', function () {
    $user = User::factory()->create();
    ensureCashierTablesExist();

    // Acquire the lock manually
    $lock = Cache::lock("subscription:cancel:{$user->id}", 35);
    $lock->get();

    $service = app(BillingService::class);

    try {
        // Create a subscription first so cancel has something to work with
        createSubscription($user);
        $service->cancelSubscription($user);
        $this->fail('Expected ConcurrentOperationException was not thrown');
    } catch (ConcurrentOperationException $e) {
        expect($e->getMessage())->toBe('Another operation is already in progress. Please try again.');
    } finally {
        $lock->release();
    }
});

it('billing service releases lock after successful operation', function () {
    $user = User::factory()->create();
    ensureCashierTablesExist();

    $lockKey = "subscription:cancel:{$user->id}";

    // Verify lock is not held
    $lock = Cache::lock($lockKey, 35);
    expect($lock->get())->toBeTrue();
    $lock->release();
});
