<?php

use App\Services\WebhookService;

it('generates a webhook secret with correct prefix', function () {
    $service = new WebhookService;

    $secret = $service->generateSecret();

    expect($secret)->toStartWith('whsec_');
    expect(strlen($secret))->toBeGreaterThan(32);
});

it('signs payload with HMAC-SHA256', function () {
    $service = new WebhookService;

    $payload = '{"event":"test"}';
    $secret = 'test-secret';

    $signature = $service->sign($payload, $secret);

    expect($signature)->toBe(hash_hmac('sha256', $payload, $secret));
});

it('produces different signatures for different payloads', function () {
    $service = new WebhookService;

    $sig1 = $service->sign('payload-1', 'secret');
    $sig2 = $service->sign('payload-2', 'secret');

    expect($sig1)->not->toBe($sig2);
});

it('produces different signatures for different secrets', function () {
    $service = new WebhookService;

    $sig1 = $service->sign('payload', 'secret-1');
    $sig2 = $service->sign('payload', 'secret-2');

    expect($sig1)->not->toBe($sig2);
});
