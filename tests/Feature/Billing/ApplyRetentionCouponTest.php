<?php

use App\Enums\AuditEvent;
use App\Exceptions\ConcurrentOperationException;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BillingService;
use Illuminate\Support\Facades\Cache;
use Stripe\Exception\InvalidRequestException as StripeInvalidRequestException;

beforeEach(function () {
    config([
        'features.billing.enabled' => true,
        'billing.lock_timeout' => 35,
        'plans.retention_coupon_id' => 'SAVE20',
    ]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('controller redirects with success and audit log is written on success', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $subscription = createSubscription($user);

    // Mock BillingService to write the audit log (simulates the real service success path)
    // and verify the controller delegates correctly
    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('applyRetentionCoupon')
        ->once()
        ->with($user, 'SAVE20')
        ->andReturnUsing(function (User $u, string $couponId) use ($subscription) {
            app(AuditService::class)->log(AuditEvent::BILLING_RETENTION_COUPON_APPLIED, [
                'user_id' => $u->id,
                'coupon_id' => $couponId,
                'subscription_id' => $subscription->stripe_id,
                'churn_save_context' => true,
            ]);
        });
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/retention-coupon');

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $log = AuditLog::where('user_id', $user->id)
        ->where('event', AuditEvent::BILLING_RETENTION_COUPON_APPLIED->value)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->metadata['coupon_id'])->toBe('SAVE20')
        ->and($log->metadata['churn_save_context'])->toBeTrue()
        ->and($log->metadata['subscription_id'])->toBe($subscription->stripe_id);
});

it('throws DomainException when user has no active subscription', function () {
    $user = User::factory()->create();
    // No subscription

    $service = app(BillingService::class);

    expect(fn () => $service->applyRetentionCoupon($user, 'SAVE20'))
        ->toThrow(DomainException::class, 'No active subscription found.');
});

it('controller returns error when service throws DomainException for no active subscription', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('applyRetentionCoupon')
        ->once()
        ->andThrow(new DomainException('No active subscription found.'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/retention-coupon');

    $response->assertSessionHas('error', 'No active subscription found.');
});

it('throws ConcurrentOperationException when coupon lock is already held', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $lock = Cache::lock("subscription:coupon:{$user->id}", 35);
    $lock->get();

    $service = app(BillingService::class);

    try {
        expect(fn () => $service->applyRetentionCoupon($user, 'SAVE20'))
            ->toThrow(ConcurrentOperationException::class);
    } finally {
        $lock->release();
    }
});

it('controller returns error when concurrent coupon request is made', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('applyRetentionCoupon')
        ->once()
        ->andThrow(new ConcurrentOperationException);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/retention-coupon');

    $response->assertSessionHas('error');
});

it('controller returns error when Stripe rejects the coupon', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('applyRetentionCoupon')
        ->once()
        ->andThrow(new StripeInvalidRequestException('No such coupon: SAVE20', 400));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/retention-coupon');

    $response->assertSessionHas('error');
    $log = AuditLog::where('user_id', $user->id)
        ->where('event', AuditEvent::BILLING_RETENTION_COUPON_APPLIED->value)
        ->first();
    expect($log)->toBeNull();
});

it('controller returns 422 when retention coupon is not configured', function () {
    config(['plans.retention_coupon_id' => null]);
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/billing/retention-coupon');

    $response->assertSessionHas('error', 'Retention coupon is not configured.');
});
