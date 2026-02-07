<?php

use App\Models\User;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\RefundProcessedNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['cashier.webhook.secret' => 'whsec_test']);
    config(['cashier.webhook.tolerance' => 300]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

function postSignedWebhook(array $payload): \Illuminate\Testing\TestResponse
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
// Payment failed notifications
// ============================================

it('sends payment failed notification when invoice fails', function () {
    Notification::fake();

    $user = User::factory()->create([
        'stripe_id' => 'cus_payment_fail',
        'email_verified_at' => now(),
    ]);

    $payload = createStripeWebhookPayload('invoice.payment_failed', [
        'id' => 'in_fail_test',
        'customer' => 'cus_payment_fail',
        'subscription' => 'sub_test_123',
        'amount_due' => 1900,
    ]);

    $response = postSignedWebhook($payload);
    $response->assertOk();

    Notification::assertSentTo($user, PaymentFailedNotification::class, function ($notification) {
        return $notification->invoiceId === 'in_fail_test'
            && $notification->subscriptionId === 'sub_test_123';
    });
});

it('does not send payment failed notification when customer not found', function () {
    Notification::fake();

    $payload = createStripeWebhookPayload('invoice.payment_failed', [
        'id' => 'in_fail_unknown',
        'customer' => 'cus_nonexistent',
        'amount_due' => 1900,
    ]);

    $response = postSignedWebhook($payload);
    $response->assertOk();

    Notification::assertNothingSent();
});

it('sends payment failed notification via database channel for unverified users', function () {
    Notification::fake();

    $user = User::factory()->create([
        'stripe_id' => 'cus_unverified',
        'email_verified_at' => null,
    ]);

    $payload = createStripeWebhookPayload('invoice.payment_failed', [
        'id' => 'in_fail_unverified',
        'customer' => 'cus_unverified',
        'subscription' => 'sub_test_456',
        'amount_due' => 1900,
    ]);

    postSignedWebhook($payload);

    Notification::assertSentTo($user, PaymentFailedNotification::class, function ($notification, $channels) {
        return in_array('database', $channels) && ! in_array('mail', $channels);
    });
});

it('sends payment failed notification via both channels for verified users', function () {
    Notification::fake();

    $user = User::factory()->create([
        'stripe_id' => 'cus_verified',
        'email_verified_at' => now(),
    ]);

    $payload = createStripeWebhookPayload('invoice.payment_failed', [
        'id' => 'in_fail_verified',
        'customer' => 'cus_verified',
        'subscription' => 'sub_test_789',
        'amount_due' => 1900,
    ]);

    postSignedWebhook($payload);

    Notification::assertSentTo($user, PaymentFailedNotification::class, function ($notification, $channels) {
        return in_array('database', $channels) && in_array('mail', $channels);
    });
});

// ============================================
// Refund notifications
// ============================================

it('sends refund notification on charge.refunded webhook', function () {
    Notification::fake();

    $user = User::factory()->create([
        'stripe_id' => 'cus_refund_test',
        'email_verified_at' => now(),
    ]);

    $payload = createStripeWebhookPayload('charge.refunded', [
        'id' => 'ch_refund_123',
        'customer' => 'cus_refund_test',
        'amount_refunded' => 1900,
        'currency' => 'usd',
        'refunds' => [
            'data' => [
                ['reason' => 'requested_by_customer'],
            ],
        ],
    ]);

    $response = postSignedWebhook($payload);
    $response->assertOk();

    Notification::assertSentTo($user, RefundProcessedNotification::class, function ($notification) {
        return $notification->chargeId === 'ch_refund_123'
            && $notification->amountRefunded === 1900
            && $notification->currency === 'usd'
            && $notification->reason === 'requested_by_customer';
    });
});

it('does not send refund notification when customer not found', function () {
    Notification::fake();

    $payload = createStripeWebhookPayload('charge.refunded', [
        'id' => 'ch_refund_unknown',
        'customer' => 'cus_nonexistent',
        'amount_refunded' => 500,
        'currency' => 'usd',
    ]);

    $response = postSignedWebhook($payload);
    $response->assertOk();

    Notification::assertNothingSent();
});
