<?php

use App\Models\User;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['cashier.webhook.secret' => 'whsec_test']);
    config(['cashier.webhook.tolerance' => 300]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

function postTimestampWebhook(array $payload): TestResponse
{
    $secret = config('cashier.webhook.secret', 'whsec_test');
    $timestamp = time();
    $jsonPayload = json_encode($payload);
    $signature = hash_hmac('sha256', "{$timestamp}.{$jsonPayload}", $secret);

    return test()->postJson('/stripe/webhook', $payload, [
        'Stripe-Signature' => "t={$timestamp},v1={$signature}",
    ]);
}

it('processes second event when two events share an identical created timestamp', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ts_equal']);
    $sameTs = 1700000100;

    $subscription = createSubscription($user, [
        'stripe_id' => 'sub_ts_equal',
        // last_webhook_at is one second before so the first event sets it to sameTs.
        'last_webhook_at' => $sameTs - 1,
        'stripe_status' => 'active',
    ]);

    // First event at sameTs — processed and sets last_webhook_at = sameTs.
    $firstPayload = createStripeWebhookPayload('customer.subscription.updated', [
        'id' => 'sub_ts_equal',
        'customer' => 'cus_ts_equal',
        'status' => 'active',
        'items' => [
            'data' => [[
                'id' => 'si_ts_eq_1',
                'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
                'quantity' => 1,
            ]],
        ],
    ]);
    $firstPayload['created'] = $sameTs;
    postTimestampWebhook($firstPayload)->assertOk();
    $subscription->refresh();
    expect($subscription->last_webhook_at)->toBe($sameTs);

    // Second event with the exact same created timestamp as last_webhook_at.
    // With the old (<= guard) this would be rejected as out-of-order; the corrected
    // (< guard) must treat equal-timestamp events as legitimate cascading events.
    $secondPayload = createStripeWebhookPayload('customer.subscription.updated', [
        'id' => 'sub_ts_equal',
        'customer' => 'cus_ts_equal',
        'status' => 'active',
        'items' => [
            'data' => [[
                'id' => 'si_ts_eq_1',
                'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
                'quantity' => 1,
            ]],
        ],
    ]);
    $secondPayload['created'] = $sameTs;

    // Must return 200 (always does) — the key assertion is that last_webhook_at is
    // written again (== sameTs), proving the event was not rejected by the guard.
    $response = postTimestampWebhook($secondPayload);
    $response->assertOk();

    // last_webhook_at stays at sameTs after the second equal-timestamp event
    $subscription->refresh();
    expect($subscription->last_webhook_at)->toBe($sameTs);
});

it('rejects webhook whose created timestamp is strictly less than last_webhook_at', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_ts_stale']);
    $storedTs = 1700000200;

    $subscription = createSubscription($user, [
        'stripe_id' => 'sub_ts_stale',
        'last_webhook_at' => $storedTs,
        'stripe_status' => 'active',
    ]);

    $payload = createStripeWebhookPayload('customer.subscription.updated', [
        'id' => 'sub_ts_stale',
        'customer' => 'cus_ts_stale',
        'status' => 'canceled',
        'items' => [
            'data' => [[
                'id' => 'si_ts_stale_1',
                'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
                'quantity' => 1,
            ]],
        ],
    ]);
    // 50 seconds before stored timestamp — genuinely out-of-order
    $payload['created'] = $storedTs - 50;

    $response = postTimestampWebhook($payload);

    // Returns 200 to Stripe (we never want to trigger retries) but must not process.
    $response->assertOk();

    // Subscription status must remain active — the stale event was correctly rejected.
    $subscription->refresh();
    expect($subscription->stripe_status)->toBe('active');
});
