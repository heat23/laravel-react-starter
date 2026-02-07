<?php

use App\Models\User;
use Laravel\Cashier\Subscription;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['cashier.webhook.secret' => 'whsec_test']);
    config(['cashier.webhook.tolerance' => 300]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

function postStripeWebhook(array $payload): \Illuminate\Testing\TestResponse
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
