<?php

use App\Enums\AuditEvent;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('routes orphaned local subscription cancel through BillingService cancelSubscription', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_orphan_local_1']);
    createSubscription($user, ['stripe_id' => 'sub_local_001', 'stripe_status' => 'active']);

    $fakeStripeSub = (object) ['id' => 'sub_local_001'];
    $fakeList = (object) ['data' => [$fakeStripeSub]];

    $fakeSubscriptions = Mockery::mock();
    $fakeSubscriptions->shouldReceive('all')
        ->with(['customer' => 'cus_orphan_local_1', 'status' => 'active'])
        ->once()
        ->andReturn($fakeList);
    // Should NOT call cancel() on Stripe for local subs — BillingService handles it.
    $fakeSubscriptions->shouldNotReceive('cancel');

    $fakeStripe = Mockery::mock(StripeClient::class);
    $fakeStripe->subscriptions = $fakeSubscriptions;

    // Partial mock: override stripeClient() and cancelSubscription() to avoid real Stripe calls.
    $billing = Mockery::mock(BillingService::class)->shouldAllowMockingProtectedMethods()->makePartial();
    $billing->shouldReceive('stripeClient')->andReturn($fakeStripe);
    $billing->shouldReceive('cancelSubscription')
        ->once()
        ->with(Mockery::on(fn ($u) => $u->id === $user->id), true);

    $count = $billing->cancelOrphanedStripeSubscription('cus_orphan_local_1', $user->id);

    expect($count)->toBe(1);
});

it('calls Stripe SDK directly for truly orphaned subscriptions with no local record', function () {
    Log::spy();

    $auditService = Mockery::mock(AuditService::class);
    $auditService->shouldReceive('log')
        ->once()
        ->with(AuditEvent::ADMIN_STRIPE_ORPHAN_CANCELED, Mockery::on(fn ($ctx) => $ctx['stripe_customer_id'] === 'cus_orphan_true_1' &&
            $ctx['stripe_subscription_id'] === 'sub_orphaned_999'
        ));
    app()->instance(AuditService::class, $auditService);

    $fakeStripeSub = (object) ['id' => 'sub_orphaned_999'];
    $fakeList = (object) ['data' => [$fakeStripeSub]];

    $fakeSubscriptions = Mockery::mock();
    $fakeSubscriptions->shouldReceive('all')
        ->with(['customer' => 'cus_orphan_true_1', 'status' => 'active'])
        ->once()
        ->andReturn($fakeList);
    $fakeSubscriptions->shouldReceive('cancel')
        ->once()
        ->with('sub_orphaned_999');

    $fakeStripe = Mockery::mock(StripeClient::class);
    $fakeStripe->subscriptions = $fakeSubscriptions;

    $billing = Mockery::mock(BillingService::class)->shouldAllowMockingProtectedMethods()->makePartial();
    $billing->shouldReceive('stripeClient')->andReturn($fakeStripe);

    $count = $billing->cancelOrphanedStripeSubscription('cus_orphan_true_1', 42);

    expect($count)->toBe(1);

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Canceled orphaned Stripe subscription', Mockery::on(fn ($ctx) => $ctx['stripe_customer_id'] === 'cus_orphan_true_1' &&
            $ctx['stripe_subscription_id'] === 'sub_orphaned_999'
        ));
});

it('returns 0 when there are no active Stripe subscriptions', function () {
    $fakeList = (object) ['data' => []];

    $fakeSubscriptions = Mockery::mock();
    $fakeSubscriptions->shouldReceive('all')
        ->with(['customer' => 'cus_no_subs', 'status' => 'active'])
        ->once()
        ->andReturn($fakeList);

    $fakeStripe = Mockery::mock(StripeClient::class);
    $fakeStripe->subscriptions = $fakeSubscriptions;

    $billing = Mockery::mock(BillingService::class)->shouldAllowMockingProtectedMethods()->makePartial();
    $billing->shouldReceive('stripeClient')->andReturn($fakeStripe);

    $count = $billing->cancelOrphanedStripeSubscription('cus_no_subs');

    expect($count)->toBe(0);
});
