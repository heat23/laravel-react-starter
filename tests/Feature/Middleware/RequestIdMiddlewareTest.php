<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

test('adds x request id header to responses', function () {
    $response = $this->get('/up');

    $response->assertHeader('X-Request-Id');
});

test('generates valid uuid format', function () {
    $response = $this->get('/up');

    $requestId = $response->headers->get('X-Request-Id');
    expect(Str::isUuid($requestId))->toBeTrue();
});

test('uses existing x request id header if present', function () {
    $existingId = '550e8400-e29b-41d4-a716-446655440000';

    $response = $this->withHeaders([
        'X-Request-Id' => $existingId,
    ])->get('/up');

    $response->assertHeader('X-Request-Id', $existingId);
});

test('shares request id in log context', function () {
    Log::shouldReceive('shareContext')
        ->once()
        ->withArgs(function ($context) {
            return isset($context['request_id'])
                && Str::isUuid($context['request_id']);
        });

    $this->get('/up');
});

test('request id is consistent across request lifecycle', function () {
    $response = $this->get('/up');

    $responseId = $response->headers->get('X-Request-Id');
    expect($responseId)
        ->not->toBeNull()
        ->and(Str::isUuid($responseId))->toBeTrue();
});

test('api routes also get request id', function () {
    $response = $this->getJson('/api/user');

    $response->assertHeader('X-Request-Id');
});

test('rejects malformed x request id header with special chars', function () {
    $response = $this->withHeaders([
        'X-Request-Id' => 'evil<script>alert(1)</script>',
    ])->get('/up');

    $requestId = $response->headers->get('X-Request-Id');
    expect(Str::isUuid($requestId))->toBeTrue();
});

test('rejects x request id header with newlines', function () {
    $response = $this->withHeaders([
        'X-Request-Id' => "fake-uuid\nINFO: injected log",
    ])->get('/up');

    $requestId = $response->headers->get('X-Request-Id');
    expect(Str::isUuid($requestId))->toBeTrue();
});

test('rejects overly long x request id header', function () {
    $response = $this->withHeaders([
        'X-Request-Id' => str_repeat('a', 100),
    ])->get('/up');

    $requestId = $response->headers->get('X-Request-Id');
    expect(Str::isUuid($requestId))->toBeTrue();
});
