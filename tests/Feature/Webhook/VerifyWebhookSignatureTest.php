<?php

use App\Http\Middleware\VerifyWebhookSignature;
use App\Webhooks\Providers\CustomWebhookProvider;
use App\Webhooks\Providers\GithubWebhookProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    config([
        'webhooks.incoming.providers.github' => [
            'class' => GithubWebhookProvider::class,
            'secret' => 'github_test_secret',
            'signature_header' => 'X-Hub-Signature-256',
            'algorithm' => 'sha256',
        ],
        'webhooks.incoming.providers.custom' => [
            'class' => CustomWebhookProvider::class,
            'secret' => 'custom_test_secret',
            'signature_header' => 'X-Webhook-Signature',
            'algorithm' => 'sha256',
        ],
    ]);
});

function makeWebhookRequest(string $provider, string $path, string $body): Request
{
    $request = Request::create($path, 'POST', [], [], [], [], $body);
    $request->setRouteResolver(function () use ($provider, $path) {
        $route = new Route('POST', '/api/webhooks/incoming/{provider}', []);
        $route->bind(Request::create($path));
        $route->setParameter('provider', $provider);

        return $route;
    });

    return $request;
}

it('verifies github signature using payload-only hmac', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"action":"push"}';
    $secret = 'github_test_secret';
    $signature = hash_hmac('sha256', $payload, $secret);

    $request = makeWebhookRequest('github', '/api/webhooks/incoming/github', $payload);
    $request->headers->set('X-Hub-Signature-256', 'sha256='.$signature);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('rejects github webhook with wrong signature', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"action":"push"}';
    $request = makeWebhookRequest('github', '/api/webhooks/incoming/github', $payload);
    $request->headers->set('X-Hub-Signature-256', 'sha256=invalidsignature');

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(HttpException::class);

it('rejects missing signature header', function () {
    $middleware = new VerifyWebhookSignature;

    $request = makeWebhookRequest('github', '/api/webhooks/incoming/github', '{}');

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(HttpException::class);

it('verifies custom provider signature using configurable header', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"id":"evt_123","type":"user.created"}';
    $secret = 'custom_test_secret';
    $signature = hash_hmac('sha256', $payload, $secret);

    $request = makeWebhookRequest('custom', '/api/webhooks/incoming/custom', $payload);
    $request->headers->set('X-Webhook-Signature', $signature);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
});

it('rejects custom provider webhook with wrong signature', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"id":"evt_123","type":"user.created"}';
    $request = makeWebhookRequest('custom', '/api/webhooks/incoming/custom', $payload);
    $request->headers->set('X-Webhook-Signature', 'invalidsignature');

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(HttpException::class);

it('does not leak configuration details for unconfigured provider', function () {
    $middleware = new VerifyWebhookSignature;

    $request = makeWebhookRequest('unknown_provider', '/api/webhooks/incoming/unknown_provider', '{}');

    try {
        $middleware->handle($request, fn ($req) => response('OK'));
        test()->fail('Expected HttpException was not thrown');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
        expect($e->getMessage())->not->toContain('not configured');
        expect($e->getMessage())->not->toContain('provider');
    }
});

it('attaches provider instance to request attributes on success', function () {
    $middleware = new VerifyWebhookSignature;

    $payload = '{"action":"push"}';
    $secret = 'github_test_secret';
    $signature = hash_hmac('sha256', $payload, $secret);

    $capturedRequest = null;
    $request = makeWebhookRequest('github', '/api/webhooks/incoming/github', $payload);
    $request->headers->set('X-Hub-Signature-256', 'sha256='.$signature);

    $middleware->handle($request, function ($req) use (&$capturedRequest) {
        $capturedRequest = $req;

        return response('OK');
    });

    expect($capturedRequest->attributes->get('webhook_provider'))->toBeInstanceOf(GithubWebhookProvider::class);
});
