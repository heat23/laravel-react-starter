<?php

use App\Models\User;
use App\Notifications\RefundProcessedNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['features.billing.coming_soon' => false]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

function postRefundWebhook(array $payload): \Illuminate\Testing\TestResponse
{
    $secret = config('cashier.webhook.secret', 'whsec_test');
    $timestamp = time();
    $jsonPayload = json_encode($payload);
    $signature = hash_hmac('sha256', "{$timestamp}.{$jsonPayload}", $secret);

    return test()->postJson('/stripe/webhook', $payload, [
        'Stripe-Signature' => "t={$timestamp},v1={$signature}",
    ]);
}

it('sends refund notification when charge.refunded webhook received', function () {
    Notification::fake();
    Notification::assertNothingSent();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_test123',
    ]);

    $payload = [
        'id' => 'evt_test123',
        'type' => 'charge.refunded',
        'data' => [
            'object' => [
                'id' => 'ch_test123',
                'customer' => 'cus_test123',
                'amount_refunded' => 2999, // $29.99
                'currency' => 'usd',
                'refunds' => [
                    'data' => [
                        [
                            'reason' => 'requested_by_customer',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = postRefundWebhook($payload);

    $response->assertOk();

    Notification::assertSentTo(
        $user,
        RefundProcessedNotification::class,
        function ($notification) {
            return $notification->chargeId === 'ch_test123'
                && $notification->amountRefunded === 2999
                && $notification->currency === 'usd'
                && $notification->reason === 'requested_by_customer';
        }
    );
});

it('handles refund notification when customer not found', function () {
    Notification::fake();

    $payload = [
        'id' => 'evt_test123',
        'type' => 'charge.refunded',
        'data' => [
            'object' => [
                'id' => 'ch_test123',
                'customer' => 'cus_nonexistent',
                'amount_refunded' => 2999,
                'currency' => 'usd',
                'refunds' => [
                    'data' => [
                        [
                            'reason' => 'duplicate',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = postRefundWebhook($payload);

    // Should still return 200 (acknowledge webhook) even if customer not found
    $response->assertOk();

    Notification::assertNothingSent();
});

it('includes all refund notification properties', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_test123',
    ]);

    $payload = [
        'id' => 'evt_test123',
        'type' => 'charge.refunded',
        'data' => [
            'object' => [
                'id' => 'ch_test456',
                'customer' => 'cus_test123',
                'amount_refunded' => 5000, // $50.00
                'currency' => 'eur',
                'refunds' => [
                    'data' => [
                        [
                            'reason' => 'fraudulent',
                        ],
                    ],
                ],
            ],
        ],
    ];

    postRefundWebhook($payload);

    Notification::assertSentTo(
        $user,
        RefundProcessedNotification::class,
        function ($notification) {
            expect($notification->chargeId)->toBe('ch_test456');
            expect($notification->amountRefunded)->toBe(5000);
            expect($notification->currency)->toBe('eur');
            expect($notification->reason)->toBe('fraudulent');

            return true;
        }
    );
});

it('handles refund without reason gracefully', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_test123',
    ]);

    $payload = [
        'id' => 'evt_test123',
        'type' => 'charge.refunded',
        'data' => [
            'object' => [
                'id' => 'ch_test789',
                'customer' => 'cus_test123',
                'amount_refunded' => 1999,
                'currency' => 'usd',
                'refunds' => [
                    'data' => [
                        [
                            // No reason provided
                        ],
                    ],
                ],
            ],
        ],
    ];

    postRefundWebhook($payload);

    Notification::assertSentTo(
        $user,
        RefundProcessedNotification::class,
        function ($notification) {
            expect($notification->chargeId)->toBe('ch_test789');
            expect($notification->amountRefunded)->toBe(1999);
            expect($notification->currency)->toBe('usd');
            expect($notification->reason)->toBeNull();

            return true;
        }
    );
});

it('queues refund notification for async processing', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_test123',
    ]);

    $payload = [
        'id' => 'evt_test123',
        'type' => 'charge.refunded',
        'data' => [
            'object' => [
                'id' => 'ch_test999',
                'customer' => 'cus_test123',
                'amount_refunded' => 7500,
                'currency' => 'usd',
                'refunds' => [
                    'data' => [
                        [
                            'reason' => 'requested_by_customer',
                        ],
                    ],
                ],
            ],
        ],
    ];

    postRefundWebhook($payload);

    Notification::assertSentTo($user, RefundProcessedNotification::class);

    // Verify notification implements ShouldQueue
    $reflection = new ReflectionClass(RefundProcessedNotification::class);
    expect($reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class))->toBeTrue();
});
