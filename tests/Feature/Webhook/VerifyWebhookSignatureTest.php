<?php

use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Http\Request;

beforeEach(function () {
    config([
        'webhooks.incoming.providers.stripe' => [
            'secret' => 'whsec_test_secret',
            'signature_header' => 'Stripe-Signature',
            'algorithm' => 'sha256',
        ],
        'webhooks.incoming.providers.github' => [
            'secret' => 'github_test_secret',
            'signature_header' => 'X-Hub-Signature-256',
            'algorithm' => 'sha256',
        ],
        'webhooks.incoming.replay_tolerance' => 300,
    ]);
});

it('verifies stripe signature using timestamp.payload format', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"type":"invoice.paid"}';
    $timestamp = time();
    $secret = 'whsec_test_secret';

    // Stripe signs: timestamp.payload
    $expectedSignature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    $signatureHeader = "t={$timestamp},v1={$expectedSignature}";

    $request = Request::create('/api/webhooks/incoming/stripe', 'POST', [], [], [], [], $payload);
    $request->headers->set('Stripe-Signature', $signatureHeader);
    $request->setRouteResolver(function () {
        $route = new \Illuminate\Routing\Route('POST', '/api/webhooks/incoming/{provider}', []);
        $route->bind(Request::create('/api/webhooks/incoming/stripe'));
        $route->setParameter('provider', 'stripe');

        return $route;
    });

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('rejects stripe signature computed without timestamp', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"type":"invoice.paid"}';
    $timestamp = time();
    $secret = 'whsec_test_secret';

    // Wrong: sign payload only (without timestamp prefix)
    $wrongSignature = hash_hmac('sha256', $payload, $secret);
    $signatureHeader = "t={$timestamp},v1={$wrongSignature}";

    $request = Request::create('/api/webhooks/incoming/stripe', 'POST', [], [], [], [], $payload);
    $request->headers->set('Stripe-Signature', $signatureHeader);
    $request->setRouteResolver(function () {
        $route = new \Illuminate\Routing\Route('POST', '/api/webhooks/incoming/{provider}', []);
        $route->bind(Request::create('/api/webhooks/incoming/stripe'));
        $route->setParameter('provider', 'stripe');

        return $route;
    });

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

it('rejects stripe webhook with expired timestamp', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"type":"invoice.paid"}';
    $timestamp = time() - 600; // 10 minutes ago, exceeds 5-min tolerance
    $secret = 'whsec_test_secret';

    $expectedSignature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    $signatureHeader = "t={$timestamp},v1={$expectedSignature}";

    $request = Request::create('/api/webhooks/incoming/stripe', 'POST', [], [], [], [], $payload);
    $request->headers->set('Stripe-Signature', $signatureHeader);
    $request->setRouteResolver(function () {
        $route = new \Illuminate\Routing\Route('POST', '/api/webhooks/incoming/{provider}', []);
        $route->bind(Request::create('/api/webhooks/incoming/stripe'));
        $route->setParameter('provider', 'stripe');

        return $route;
    });

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

it('verifies github signature using payload-only hmac', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"action":"push"}';
    $secret = 'github_test_secret';

    // GitHub signs: just the payload
    $signature = hash_hmac('sha256', $payload, $secret);

    $request = Request::create('/api/webhooks/incoming/github', 'POST', [], [], [], [], $payload);
    $request->headers->set('X-Hub-Signature-256', 'sha256='.$signature);
    $request->setRouteResolver(function () {
        $route = new \Illuminate\Routing\Route('POST', '/api/webhooks/incoming/{provider}', []);
        $route->bind(Request::create('/api/webhooks/incoming/github'));
        $route->setParameter('provider', 'github');

        return $route;
    });

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('rejects missing signature header', function () {
    $middleware = new VerifyWebhookSignature;

    $request = Request::create('/api/webhooks/incoming/stripe', 'POST', [], [], [], [], '{}');
    $request->setRouteResolver(function () {
        $route = new \Illuminate\Routing\Route('POST', '/api/webhooks/incoming/{provider}', []);
        $route->bind(Request::create('/api/webhooks/incoming/stripe'));
        $route->setParameter('provider', 'stripe');

        return $route;
    });

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

it('does not leak configuration details for unconfigured provider', function () {
    $middleware = new VerifyWebhookSignature;

    $request = Request::create('/api/webhooks/incoming/unknown_provider', 'POST', [], [], [], [], '{}');
    $request->setRouteResolver(function () {
        $route = new \Illuminate\Routing\Route('POST', '/api/webhooks/incoming/{provider}', []);
        $route->bind(Request::create('/api/webhooks/incoming/unknown_provider'));
        $route->setParameter('provider', 'unknown_provider');

        return $route;
    });

    try {
        $middleware->handle($request, fn ($req) => response('OK'));
        $this->fail('Expected HttpException was not thrown');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
        expect($e->getMessage())->not->toContain('not configured');
        expect($e->getMessage())->not->toContain('provider');
    }
});
