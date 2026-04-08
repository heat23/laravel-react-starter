<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    RateLimiter::clear('127.0.0.1');
});

it('includes X-RateLimit-Limit header on throttled API routes', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertHeader('X-RateLimit-Limit');
});

it('includes X-RateLimit-Remaining header on throttled API routes', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertHeader('X-RateLimit-Remaining');
});

it('decrements X-RateLimit-Remaining on successive requests', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $first = $this->getJson('/api/user');
    $second = $this->getJson('/api/user');

    $firstRemaining = (int) $first->headers->get('X-RateLimit-Remaining');
    $secondRemaining = (int) $second->headers->get('X-RateLimit-Remaining');

    expect($secondRemaining)->toBe($firstRemaining - 1);
});

it('includes X-RateLimit-Reset as valid unix timestamp when rate limited', function () {
    $user = User::factory()->create();

    // Exhaust rate limit on token endpoint (throttle:20,1)
    config(['features.api_tokens.enabled' => true]);
    Sanctum::actingAs($user, ['*']);

    for ($i = 0; $i < 20; $i++) {
        $this->getJson('/api/tokens');
    }

    $response = $this->getJson('/api/tokens');

    $response->assertStatus(429);
    $response->assertHeader('X-RateLimit-Reset');

    $resetTime = (int) $response->headers->get('X-RateLimit-Reset');
    expect($resetTime)->toBeGreaterThan(time());
    expect($resetTime)->toBeLessThanOrEqual(time() + 61);
});
