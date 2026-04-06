<?php

use App\Enums\AnalyticsEvent;
use App\Jobs\DispatchAnalyticsEvent;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Laravel\Cashier\Subscription;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['cashier.webhook.secret' => 'whsec_test']);
    config(['cashier.webhook.tolerance' => 300]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

function postStripeWebhook(array $payload): TestResponse
{
    $secret = config('cashier.webhook.secret', 'whsec_test');
    $timestamp = time();
    $jsonPayload = json_encode($payload);
    $signature = hash_hmac('sha256', "{$timestamp}.{$jsonPayload}", $secret);

    return test()->postJson('/stripe/webhook', $payload, [
        'Stripe-Signature' => "t={$timestamp},v1={$signature}",
    ]);
}

// ============================================
// Subscription event handling
// ============================================

it('handles customer.subscription.created webhook and creates subscription', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_test_123']);

    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_webhook_test',
        'customer' => 'cus_test_123',
        'status' => 'active',
        'items' => [
            'data' => [[
                'id' => 'si_test_1',
                'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
                'quantity' => 1,
            ]],
        ],
        'current_period_end' => now()->addMonth()->timestamp,
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'stripe_id' => 'sub_webhook_test',
        'stripe_status' => 'active',
    ]);
});

it('handles customer.subscription.updated webhook and updates subscription', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_test_456']);
    createSubscription($user, ['stripe_id' => 'sub_existing_123']);

    $payload = createStripeWebhookPayload('customer.subscription.updated', [
        'id' => 'sub_existing_123',
        'customer' => 'cus_test_456',
        'status' => 'active',
        'items' => [
            'data' => [[
                'id' => 'si_test_2',
                'price' => ['id' => 'price_team_monthly', 'product' => 'prod_test'],
                'quantity' => 5,
            ]],
        ],
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
    $subscription = Subscription::where('stripe_id', 'sub_existing_123')->first();
    expect($subscription->stripe_status)->toBe('active');
});

it('handles customer.subscription.deleted webhook and marks subscription canceled', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_test_789']);
    createSubscription($user, ['stripe_id' => 'sub_to_delete']);

    $payload = createStripeWebhookPayload('customer.subscription.deleted', [
        'id' => 'sub_to_delete',
        'customer' => 'cus_test_789',
        'status' => 'canceled',
        'items' => [
            'data' => [[
                'id' => 'si_test_3',
                'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
                'quantity' => 1,
            ]],
        ],
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
    $subscription = Subscription::where('stripe_id', 'sub_to_delete')->first();
    expect($subscription->stripe_status)->toBe('canceled');
});

it('handles customer.subscription.trial_will_end webhook', function () {
    $payload = createStripeWebhookPayload('customer.subscription.trial_will_end', [
        'id' => 'sub_trial_end',
        'customer' => 'cus_trial_test',
        'trial_end' => now()->addDays(3)->timestamp,
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
});

// ============================================
// Invoice events
// ============================================

it('handles invoice.payment_succeeded webhook', function () {
    $payload = createStripeWebhookPayload('invoice.payment_succeeded', [
        'id' => 'in_success_123',
        'customer' => 'cus_invoice_test',
        'amount_paid' => 1900,
        'currency' => 'usd',
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
});

it('handles invoice.payment_failed webhook', function () {
    $payload = createStripeWebhookPayload('invoice.payment_failed', [
        'id' => 'in_failed_123',
        'customer' => 'cus_invoice_test',
        'amount_due' => 1900,
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
});

it('handles invoice.payment_action_required webhook for SCA', function () {
    $payload = createStripeWebhookPayload('invoice.payment_action_required', [
        'id' => 'in_sca_123',
        'customer' => 'cus_sca_test',
        'payment_intent' => ['id' => 'pi_sca_test', 'status' => 'requires_action'],
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
});

// ============================================
// Customer events
// ============================================

it('handles customer.updated webhook', function () {
    $payload = createStripeWebhookPayload('customer.updated', [
        'id' => 'cus_updated_test',
        'email' => 'updated@example.com',
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
});

// ============================================
// Security & edge cases
// ============================================

it('rejects webhook with invalid stripe signature', function () {
    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_bad_sig',
        'customer' => 'cus_bad_sig',
    ]);

    $response = $this->postJson('/stripe/webhook', $payload, [
        'Stripe-Signature' => 't='.time().',v1=invalid_signature_here',
    ]);

    $response->assertForbidden();
});

it('rejects webhook with missing signature header', function () {
    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_no_sig',
    ]);

    $response = $this->postJson('/stripe/webhook', $payload);

    $response->assertForbidden();
});

it('gracefully handles unknown webhook event types', function () {
    $payload = createStripeWebhookPayload('unknown.event.type', [
        'id' => 'obj_unknown',
    ]);

    $response = postStripeWebhook($payload);

    $response->assertOk();
});

it('rejects webhook with expired timestamp', function () {
    $secret = config('cashier.webhook.secret', 'whsec_test');
    $expiredTimestamp = time() - 600; // 10 minutes ago
    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_expired_ts',
    ]);
    $jsonPayload = json_encode($payload);
    $signature = hash_hmac('sha256', "{$expiredTimestamp}.{$jsonPayload}", $secret);

    $response = $this->postJson('/stripe/webhook', $payload, [
        'Stripe-Signature' => "t={$expiredTimestamp},v1={$signature}",
    ]);

    $response->assertForbidden();
});

// ============================================
// Webhook sequence validation
// ============================================

it('rejects out-of-order subscription updated webhook', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_seq_test']);
    $subscription = createSubscription($user, [
        'stripe_id' => 'sub_seq_test',
        'last_webhook_at' => 1700000100,
    ]);

    // Send an event with an older timestamp
    $payload = createStripeWebhookPayload('customer.subscription.updated', [
        'id' => 'sub_seq_test',
        'customer' => 'cus_seq_test',
        'status' => 'canceled',
        'items' => [
            'data' => [[
                'id' => 'si_seq_1',
                'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
                'quantity' => 1,
            ]],
        ],
    ]);
    // Override the created timestamp to be older
    $payload['created'] = 1700000050;

    $response = postStripeWebhook($payload);

    $response->assertOk(); // Return 200 to Stripe but skip processing

    // Verify subscription was NOT updated (still active, not canceled)
    $subscription->refresh();
    expect($subscription->stripe_status)->toBe('active');
});

it('processes newer webhook and updates last_webhook_at', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_seq_test_2']);
    $subscription = createSubscription($user, [
        'stripe_id' => 'sub_seq_test_2',
        'last_webhook_at' => 1700000050,
    ]);

    $newerTimestamp = 1700000100;
    $payload = createStripeWebhookPayload('customer.subscription.updated', [
        'id' => 'sub_seq_test_2',
        'customer' => 'cus_seq_test_2',
        'status' => 'active',
        'items' => [
            'data' => [[
                'id' => 'si_seq_2',
                'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
                'quantity' => 1,
            ]],
        ],
    ]);
    $payload['created'] = $newerTimestamp;

    $response = postStripeWebhook($payload);

    $response->assertOk();

    $subscription->refresh();
    expect($subscription->last_webhook_at)->toBe($newerTimestamp);
});

it('processes first webhook when last_webhook_at is null', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_seq_test_3']);
    $subscription = createSubscription($user, [
        'stripe_id' => 'sub_seq_test_3',
        'last_webhook_at' => null,
    ]);

    $eventTimestamp = 1700000100;
    $payload = createStripeWebhookPayload('customer.subscription.updated', [
        'id' => 'sub_seq_test_3',
        'customer' => 'cus_seq_test_3',
        'status' => 'active',
        'items' => [
            'data' => [[
                'id' => 'si_seq_3',
                'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
                'quantity' => 1,
            ]],
        ],
    ]);
    $payload['created'] = $eventTimestamp;

    $response = postStripeWebhook($payload);

    $response->assertOk();

    $subscription->refresh();
    expect($subscription->last_webhook_at)->toBe($eventTimestamp);
});

// ============================================
// Cache invalidation
// ============================================

it('invalidates plan cache on customer.subscription.created', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_created']);
    Cache::put("user:{$user->id}:plan_tier", 'free', 300);

    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_ci_created',
        'customer' => 'cus_ci_created',
        'status' => 'active',
        'items' => ['data' => [['id' => 'si_ci1', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
        'current_period_end' => now()->addMonth()->timestamp,
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeFalse();
});

it('invalidates plan cache on customer.subscription.updated', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_updated']);
    createSubscription($user, ['stripe_id' => 'sub_ci_updated']);
    Cache::put("user:{$user->id}:plan_tier", 'pro', 300);

    $payload = createStripeWebhookPayload('customer.subscription.updated', [
        'id' => 'sub_ci_updated',
        'customer' => 'cus_ci_updated',
        'status' => 'active',
        'items' => ['data' => [['id' => 'si_ci2', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeFalse();
});

it('invalidates plan cache on customer.subscription.deleted', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_deleted']);
    createSubscription($user, ['stripe_id' => 'sub_ci_deleted']);
    Cache::put("user:{$user->id}:plan_tier", 'pro', 300);

    $payload = createStripeWebhookPayload('customer.subscription.deleted', [
        'id' => 'sub_ci_deleted',
        'customer' => 'cus_ci_deleted',
        'status' => 'canceled',
        'items' => ['data' => [['id' => 'si_ci3', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeFalse();
});

it('does not invalidate plan cache on customer.subscription.trial_will_end', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_trial']);
    Cache::put("user:{$user->id}:plan_tier", 'pro', 300);

    $payload = createStripeWebhookPayload('customer.subscription.trial_will_end', [
        'id' => 'sub_ci_trial',
        'customer' => 'cus_ci_trial',
        'trial_end' => now()->addDays(3)->timestamp,
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeTrue();
});

it('does not invalidate plan cache on invoice.payment_succeeded', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_inv_ok']);
    Cache::put("user:{$user->id}:plan_tier", 'pro', 300);

    $payload = createStripeWebhookPayload('invoice.payment_succeeded', [
        'id' => 'in_ci_ok',
        'customer' => 'cus_ci_inv_ok',
        'amount_paid' => 1900,
        'currency' => 'usd',
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeTrue();
});

it('does not invalidate plan cache on invoice.payment_failed', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_inv_fail']);
    Cache::put("user:{$user->id}:plan_tier", 'pro', 300);

    $payload = createStripeWebhookPayload('invoice.payment_failed', [
        'id' => 'in_ci_fail',
        'customer' => 'cus_ci_inv_fail',
        'amount_due' => 1900,
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeTrue();
});

it('does not invalidate plan cache on invoice.payment_action_required', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_inv_sca']);
    Cache::put("user:{$user->id}:plan_tier", 'pro', 300);

    $payload = createStripeWebhookPayload('invoice.payment_action_required', [
        'id' => 'in_ci_sca',
        'customer' => 'cus_ci_inv_sca',
        'hosted_invoice_url' => 'https://invoice.stripe.com/test',
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeTrue();
});

it('does not invalidate plan cache on charge.refunded', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_refund']);
    Cache::put("user:{$user->id}:plan_tier", 'pro', 300);

    $payload = createStripeWebhookPayload('charge.refunded', [
        'id' => 'ch_ci_refund',
        'customer' => 'cus_ci_refund',
        'amount_refunded' => 1900,
        'currency' => 'usd',
        'refunds' => ['data' => []],
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeTrue();
});

it('does not invalidate plan cache on customer.updated', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ci_meta']);
    Cache::put("user:{$user->id}:plan_tier", 'pro', 300);

    $payload = createStripeWebhookPayload('customer.updated', [
        'id' => 'cus_ci_meta',
        'email' => 'updated@example.com',
    ]);

    postStripeWebhook($payload)->assertOk();

    expect(Cache::has("user:{$user->id}:plan_tier"))->toBeTrue();
});

it('does not throw when subscription customer does not exist in the database', function () {
    // No user with this stripe_id — invalidatePlanCache must handle null user gracefully
    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_no_db_user',
        'customer' => 'cus_nonexistent_xyz',
        'status' => 'active',
        'items' => ['data' => [['id' => 'si_nc', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
        'current_period_end' => now()->addMonth()->timestamp,
    ]);

    postStripeWebhook($payload)->assertOk();
});

// ============================================
// Analytics event dispatch (ANA-002)
// ============================================

it('dispatches SUBSCRIPTION_CREATED analytics event on customer.subscription.created', function () {
    Queue::fake();

    $user = User::factory()->create(['stripe_id' => 'cus_ana_created']);

    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_ana_created',
        'customer' => 'cus_ana_created',
        'status' => 'active',
        'items' => ['data' => [['id' => 'si_ana1', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
        'current_period_end' => now()->addMonth()->timestamp,
    ]);

    postStripeWebhook($payload)->assertOk();

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === AnalyticsEvent::SUBSCRIPTION_CREATED->value
            && $job->userId === $user->id;
    });
});

it('dispatches SUBSCRIPTION_CANCELED analytics event on customer.subscription.deleted', function () {
    Queue::fake();

    $user = User::factory()->create(['stripe_id' => 'cus_ana_deleted']);
    createSubscription($user, ['stripe_id' => 'sub_ana_deleted']);

    $payload = createStripeWebhookPayload('customer.subscription.deleted', [
        'id' => 'sub_ana_deleted',
        'customer' => 'cus_ana_deleted',
        'status' => 'canceled',
        'items' => ['data' => [['id' => 'si_ana2', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
    ]);

    postStripeWebhook($payload)->assertOk();

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === AnalyticsEvent::SUBSCRIPTION_CANCELED->value
            && $job->userId === $user->id;
    });
});

it('dispatches BILLING_PAYMENT_FAILED analytics event on invoice.payment_failed', function () {
    Queue::fake();

    $user = User::factory()->create(['stripe_id' => 'cus_ana_failed']);

    $payload = createStripeWebhookPayload('invoice.payment_failed', [
        'id' => 'in_ana_failed',
        'customer' => 'cus_ana_failed',
        'amount_due' => 1900,
    ]);

    postStripeWebhook($payload)->assertOk();

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === AnalyticsEvent::BILLING_PAYMENT_FAILED->value
            && $job->userId === $user->id;
    });
});

it('does not dispatch analytics event when stripe customer has no local user', function () {
    Queue::fake();

    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_ana_no_user',
        'customer' => 'cus_no_local_user_xyz',
        'status' => 'active',
        'items' => ['data' => [['id' => 'si_ana3', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
        'current_period_end' => now()->addMonth()->timestamp,
    ]);

    postStripeWebhook($payload)->assertOk();

    Queue::assertNotPushed(DispatchAnalyticsEvent::class, function ($job) {
        return $job->eventName === AnalyticsEvent::SUBSCRIPTION_CREATED->value;
    });
});

it('dispatches BILLING_PAYMENT_RECOVERED analytics event when invoice.payment_succeeded clears past_due_since', function () {
    Queue::fake();

    $user = User::factory()->create(['stripe_id' => 'cus_ana_recovered']);
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'stripe_id' => 'sub_ana_recovered',
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDay(),
    ]);

    $payload = createStripeWebhookPayload('invoice.payment_succeeded', [
        'id' => 'in_ana_recovered',
        'customer' => 'cus_ana_recovered',
        'subscription' => 'sub_ana_recovered',
        'amount_paid' => 1900,
        'currency' => 'usd',
    ]);

    postStripeWebhook($payload)->assertOk();

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === AnalyticsEvent::BILLING_PAYMENT_RECOVERED->value
            && $job->userId === $user->id;
    });

    expect($subscription->fresh()->past_due_since)->toBeNull();
});

it('does not dispatch BILLING_PAYMENT_RECOVERED when invoice.payment_succeeded is not a recovery', function () {
    Queue::fake();

    $user = User::factory()->create(['stripe_id' => 'cus_ana_no_recovery']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'stripe_id' => 'sub_ana_no_recovery',
        'stripe_status' => 'active',
        'past_due_since' => null,
    ]);

    $payload = createStripeWebhookPayload('invoice.payment_succeeded', [
        'id' => 'in_ana_no_recovery',
        'customer' => 'cus_ana_no_recovery',
        'subscription' => 'sub_ana_no_recovery',
        'amount_paid' => 1900,
        'currency' => 'usd',
    ]);

    postStripeWebhook($payload)->assertOk();

    Queue::assertNotPushed(DispatchAnalyticsEvent::class, function ($job) {
        return $job->eventName === AnalyticsEvent::BILLING_PAYMENT_RECOVERED->value;
    });
});

// ============================================
// Analytics audit_log persistence (ANA-002 regression)
// These tests run without Queue::fake() so PersistAuditLog executes synchronously
// (QUEUE_CONNECTION=sync in phpunit.xml) and we can assert on audit_logs directly.
// Previously dispatchWebhookAnalyticsEvent dispatched DispatchAnalyticsEvent directly
// (GA4 only, no audit_logs write), making webhook events invisible to
// ProductAnalyticsService::getSubscriptionEvents() queries.
// ============================================

it('persists subscription.created to audit_logs when customer.subscription.created fires', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_pers_sub_created']);

    $payload = createStripeWebhookPayload('customer.subscription.created', [
        'id' => 'sub_pers_created',
        'customer' => 'cus_pers_sub_created',
        'status' => 'active',
        'items' => ['data' => [['id' => 'si_pers1', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
        'current_period_end' => now()->addMonth()->timestamp,
    ]);

    postStripeWebhook($payload)->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'event' => AnalyticsEvent::SUBSCRIPTION_CREATED->value,
        'user_id' => $user->id,
    ]);
});

it('persists subscription.canceled to audit_logs when customer.subscription.deleted fires', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_pers_sub_deleted']);
    createSubscription($user, ['stripe_id' => 'sub_pers_deleted']);

    $payload = createStripeWebhookPayload('customer.subscription.deleted', [
        'id' => 'sub_pers_deleted',
        'customer' => 'cus_pers_sub_deleted',
        'status' => 'canceled',
        'items' => ['data' => [['id' => 'si_pers2', 'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'], 'quantity' => 1]]],
    ]);

    postStripeWebhook($payload)->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'event' => AnalyticsEvent::SUBSCRIPTION_CANCELED->value,
        'user_id' => $user->id,
    ]);
});

it('persists billing.payment_failed to audit_logs when invoice.payment_failed fires', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_pers_pay_failed']);

    $payload = createStripeWebhookPayload('invoice.payment_failed', [
        'id' => 'in_pers_failed',
        'customer' => 'cus_pers_pay_failed',
        'amount_due' => 1900,
    ]);

    postStripeWebhook($payload)->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'event' => AnalyticsEvent::BILLING_PAYMENT_FAILED->value,
        'user_id' => $user->id,
    ]);
});
