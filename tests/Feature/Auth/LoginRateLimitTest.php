<?php

use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('127.0.0.1');
});

it('returns 429 after too many login attempts', function () {
    // Route throttle is 10,1 (10 requests per minute)
    for ($i = 0; $i < 10; $i++) {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);
    }

    // 11th request should be rate limited
    $response = $this->post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(429);
});
