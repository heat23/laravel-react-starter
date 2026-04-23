<?php

use App\Exceptions\ConcurrentOperationException;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
});

it('throws ConcurrentOperationException when checkout lock cannot be acquired', function () {
    Log::spy();

    $user = User::factory()->create();
    $service = new BillingService;

    // Hold the lock so createCheckoutSession cannot acquire it
    $lockKey = $service->lockKey('checkout', $user);
    $heldLock = Cache::lock($lockKey, 35);
    $heldLock->get();

    try {
        expect(fn () => $service->createCheckoutSession(
            user: $user,
            priceId: 'price_pro_monthly',
            quantity: 1,
            successUrl: 'https://example.com/success',
            cancelUrl: 'https://example.com/cancel',
        ))->toThrow(ConcurrentOperationException::class);
    } finally {
        $heldLock->release();
    }
});

it('checkout lock key contains the operation name and user id', function () {
    $user = User::factory()->create();
    $service = new BillingService;

    $key = $service->lockKey('checkout', $user);

    expect($key)->toContain('checkout');
    expect($key)->toContain((string) $user->id);
});
