<?php

/**
 * RCT-001: Retention coupon applied to active subscription.
 *
 * The applyRetentionCoupon() controller action reads the coupon ID from config,
 * validates the user has an active subscription, then calls Stripe to apply the
 * discount. Tests use a fake Stripe HTTP client (ApiRequestor::setHttpClient)
 * installed in beforeEach and reset in afterEach for per-test isolation.
 *
 * A $stripeWasCalled flag (shared via object reference) asserts the fake was
 * actually invoked for tests that reach the Stripe call path, preventing silent
 * real-network calls from escaping the test environment undetected.
 */

use App\Enums\AnalyticsEvent;
use App\Models\User;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;

// Shared call-tracking state. Using an object (not a scalar reference) avoids
// constructor-promotion reference limitations and works cleanly across closures.
$stripeCallTracker = (object) ['called' => false];

beforeEach(function () use ($stripeCallTracker) {
    $stripeCallTracker->called = false;

    config(['features.billing.enabled' => true]);
    config(['features.billing.coming_soon' => false]);
    config(['plans.retention_coupon_id' => 'RETENTION20']);
    ensureCashierTablesExist();
    registerBillingRoutes();

    // Install the fake HTTP client for every test in this file.
    // Tests that do not reach the Stripe call path will never trigger it,
    // but installing it unconditionally in beforeEach guarantees isolation:
    // regardless of future guard-ordering changes, no real Stripe HTTP call
    // can escape from any test in this file.
    ApiRequestor::setHttpClient(new class($stripeCallTracker) implements ClientInterface
    {
        public function __construct(private readonly object $tracker) {}

        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
        {
            $this->tracker->called = true;

            // Coupon retrieve: GET /v1/coupons/{id}
            if (str_contains($absUrl, '/coupons/')) {
                $body = json_encode([
                    'id' => 'RETENTION20',
                    'object' => 'coupon',
                    'amount_off' => null,
                    'created' => 1_700_000_000,
                    'currency' => null,
                    'duration' => 'once',
                    'duration_in_months' => null,
                    'livemode' => false,
                    'max_redemptions' => null,
                    'metadata' => new stdClass,
                    'name' => '20% Off',
                    'percent_off' => 20.0,
                    'redeem_by' => null,
                    'times_redeemed' => 0,
                    'valid' => true,
                ]);

                return [$body, 200, ['request-id' => 'req_fake_coupon']];
            }

            // Subscription update: POST /v1/subscriptions/{id}
            $body = json_encode([
                'id' => 'sub_fake',
                'object' => 'subscription',
                'status' => 'active',
                'items' => [
                    'object' => 'list',
                    'data' => [],
                    'has_more' => false,
                    'url' => '/v1/subscription_items',
                ],
                'discounts' => [['coupon' => 'RETENTION20']],
            ]);

            return [$body, 200, ['request-id' => 'req_fake_sub']];
        }
    });
});

afterEach(function () {
    // Always reset the global static HTTP client after each test so that
    // subsequent test files (including those running in the same process)
    // are not affected by the fake.
    ApiRequestor::setHttpClient(null);
});

it('logs billing.retention_coupon_applied using AnalyticsEvent enum value', function () use ($stripeCallTracker) {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, ['stripe_id' => 'sub_fake']);

    $this->actingAs($user)
        ->post('/billing/retention-coupon')
        ->assertRedirect();

    // Confirm the fake HTTP client was invoked — if this fails, a real Stripe
    // network call escaped the test environment or the fake was not wired up.
    expect($stripeCallTracker->called)->toBeTrue(
        'Stripe HTTP fake was not called: a real network call may have escaped.'
    );

    // Verify the event is stored using the typed enum value, not a raw string.
    $this->assertDatabaseHas('audit_logs', [
        'event' => AnalyticsEvent::BILLING_RETENTION_COUPON_APPLIED->value,
        'user_id' => $user->id,
    ]);

    // Confirm the old raw-string form is NOT what gets stored.
    $this->assertDatabaseMissing('audit_logs', [
        'event' => 'retention_coupon_applied',
        'user_id' => $user->id,
    ]);
});

it('returns 422 when retention coupon is not configured', function () use ($stripeCallTracker) {
    config(['plans.retention_coupon_id' => null]);

    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->postJson('/billing/retention-coupon')
        ->assertStatus(422)
        ->assertJson(['message' => 'Retention coupon is not configured.']);

    // No Stripe call should be made — guard exits before reaching the API.
    expect($stripeCallTracker->called)->toBeFalse(
        'Stripe HTTP client was called despite the coupon not being configured.'
    );
});

it('returns 400 when user has no active subscription', function () use ($stripeCallTracker) {
    $user = User::factory()->create(['email_verified_at' => now()]);
    // No subscription created.

    $this->actingAs($user)
        ->postJson('/billing/retention-coupon')
        ->assertStatus(400)
        ->assertJson(['message' => 'No active subscription found.']);

    // No Stripe call should be made — guard exits before reaching the API.
    expect($stripeCallTracker->called)->toBeFalse(
        'Stripe HTTP client was called despite user having no active subscription.'
    );
});
