<?php

use App\Services\AnalyticsGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Http::fake();
    $this->gateway = new AnalyticsGateway;
});

// ============================================
// No-op when not configured
// ============================================

test('send is a no-op when GA4 is not enabled', function () {
    config([
        'services.ga4.enabled' => false,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    $this->gateway->send('auth.login', [], 1);

    Http::assertNothingSent();
});

test('send is a no-op when measurement_id is not configured', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => null,
        'services.ga4.api_secret' => 'secret',
    ]);

    $this->gateway->send('auth.login', [], 1);

    Http::assertNothingSent();
});

// ============================================
// Happy path — correct POST structure
// ============================================

test('send posts to GA4 collect endpoint with correct structure', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'test_secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->send('billing.subscription_canceled', ['plan' => 'pro'], 42);

    Http::assertSent(function ($request) {
        $url = (string) $request->url();
        $body = $request->data();

        return str_contains($url, 'www.google-analytics.com/mp/collect')
            && str_contains($url, 'measurement_id=G-TEST123')
            && str_contains($url, 'api_secret=test_secret')
            && $body['client_id'] === 'server_42'
            && $body['user_id'] === '42'
            && $body['events'][0]['name'] === 'billing_subscription_canceled'
            && $body['events'][0]['params']['plan'] === 'pro'
            && isset($body['events'][0]['params']['engagement_time_msec']);
    });
});

test('send converts dots to underscores in event name', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->send('auth.social_login', ['provider' => 'google'], 1);

    Http::assertSent(function ($request) {
        return $request->data()['events'][0]['name'] === 'auth_social_login';
    });
});

test('send formats client_id as server_{userId}', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->send('auth.register', [], 99);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $body['client_id'] === 'server_99'
            && $body['user_id'] === '99';
    });
});

test('send appends engagement_time_msec to event params', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->send('subscription.created', [], 1);

    Http::assertSent(function ($request) {
        return isset($request->data()['events'][0]['params']['engagement_time_msec']);
    });
});

// ============================================
// Fire-and-forget — never throws
// ============================================

test('send does not throw when HTTP request fails with exception', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(fn () => throw new Exception('Connection refused'));

    Log::shouldReceive('warning')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'AnalyticsGateway')
                && $context['event'] === 'auth.login'
                && $context['user_id'] === 1;
        });

    // Must not throw
    $this->gateway->send('auth.login', [], 1);
})->throwsNoExceptions();

// ============================================
// PII sanitization
// ============================================

test('send strips PII keys from params before forwarding to GA4', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->send('subscription.created', [
        'plan' => 'pro',
        'email' => 'user@example.com',  // PII — must be stripped
        'ip' => '1.2.3.4',              // PII — must be stripped
        'token' => 'abc123',            // PII — must be stripped
        'amount' => 19,                 // safe — must be kept
    ], 1);

    Http::assertSent(function ($request) {
        $params = $request->data()['events'][0]['params'];

        return $params['plan'] === 'pro'
            && $params['amount'] === 19
            && ! array_key_exists('email', $params)
            && ! array_key_exists('ip', $params)
            && ! array_key_exists('token', $params);
    });
});

test('send strips expanded PII keys (username, first_name, last_name, company_name, mobile, billing_address, shipping_address)', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->send('profile.updated', [
        'plan' => 'pro',
        'username' => 'johndoe',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company_name' => 'Acme Inc',
        'mobile' => '+15551234567',
        'billing_address' => '123 Main St',
        'shipping_address' => '456 Oak Ave',
        'duration_ms' => 150,
    ], 1);

    Http::assertSent(function ($request) {
        $params = $request->data()['events'][0]['params'];

        return $params['plan'] === 'pro'
            && $params['duration_ms'] === 150
            && ! array_key_exists('username', $params)
            && ! array_key_exists('first_name', $params)
            && ! array_key_exists('last_name', $params)
            && ! array_key_exists('company_name', $params)
            && ! array_key_exists('mobile', $params)
            && ! array_key_exists('billing_address', $params)
            && ! array_key_exists('shipping_address', $params);
    });
});

test('send logs warning on HTTP failure', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(fn () => throw new RuntimeException('timeout'));

    Log::shouldReceive('warning')
        ->once()
        ->withArgs(function ($message, $context) {
            return isset($context['error'])
                && isset($context['event'])
                && isset($context['user_id']);
        });

    $this->gateway->send('subscription.canceled', [], 5);
});

// ============================================
// sendBatch() tests
// ============================================

test('sendBatch is a no-op when GA4 is not enabled', function () {
    config([
        'services.ga4.enabled' => false,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    $this->gateway->sendBatch([
        ['name' => 'auth.login', 'params' => []],
    ], 1);

    Http::assertNothingSent();
});

test('sendBatch is a no-op when measurement_id is empty despite enabled=true', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => '',
        'services.ga4.api_secret' => 'secret',
    ]);

    $this->gateway->sendBatch([
        ['name' => 'auth.login', 'params' => ['provider' => 'email']],
    ], 1);

    Http::assertNothingSent();
});

test('sendBatch is a no-op for empty events array', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    $this->gateway->sendBatch([], 1);

    Http::assertNothingSent();
});

test('sendBatch sends multiple events in a single request', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->sendBatch([
        ['name' => 'auth.login', 'params' => ['provider' => 'email']],
        ['name' => 'subscription.created', 'params' => ['plan' => 'pro']],
        ['name' => 'onboarding.completed', 'params' => []],
    ], 42);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return count($body['events']) === 3
            && $body['client_id'] === 'server_42'
            && $body['events'][0]['name'] === 'auth_login'
            && $body['events'][1]['name'] === 'subscription_created'
            && $body['events'][2]['name'] === 'onboarding_completed'
            && $body['events'][0]['params']['provider'] === 'email'
            && $body['events'][1]['params']['plan'] === 'pro';
    });
});

test('sendBatch splits events exceeding 25 into multiple requests', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    // Create 27 events — should result in 2 requests (25 + 2)
    $events = [];
    for ($i = 0; $i < 27; $i++) {
        $events[] = ['name' => "event.{$i}", 'params' => ['index' => $i]];
    }

    $this->gateway->sendBatch($events, 1);

    Http::assertSentCount(2);
});

test('sendBatch strips PII keys from event params', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->sendBatch([
        ['name' => 'auth.register', 'params' => ['email' => 'user@example.com', 'plan' => 'free']],
    ], 1);

    Http::assertSent(function ($request) {
        $params = $request->data()['events'][0]['params'];

        return $params['plan'] === 'free'
            && ! array_key_exists('email', $params);
    });
});

test('sendBatch appends engagement_time_msec to each event', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(['https://www.google-analytics.com/*' => Http::response('', 204)]);

    $this->gateway->sendBatch([
        ['name' => 'auth.login', 'params' => []],
        ['name' => 'subscription.created', 'params' => []],
    ], 1);

    Http::assertSent(function ($request) {
        $events = $request->data()['events'];

        return isset($events[0]['params']['engagement_time_msec'])
            && isset($events[1]['params']['engagement_time_msec']);
    });
});

test('sendBatch does not throw on HTTP failure', function () {
    config([
        'services.ga4.enabled' => true,
        'services.ga4.measurement_id' => 'G-TEST123',
        'services.ga4.api_secret' => 'secret',
    ]);

    Http::fake(fn () => throw new Exception('Connection refused'));

    Log::shouldReceive('warning')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'batch')
                && $context['event_count'] === 2
                && $context['user_id'] === 1;
        });

    $this->gateway->sendBatch([
        ['name' => 'auth.login', 'params' => []],
        ['name' => 'auth.register', 'params' => []],
    ], 1);
})->throwsNoExceptions();
