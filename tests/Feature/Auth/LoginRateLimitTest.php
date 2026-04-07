<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

beforeEach(function () {
    // Clear the correct email-keyed throttle keys used across this file.
    // LoginRequest::throttleKey() produces Str::transliterate(email.'|'.ip()),
    // so clearing the bare IP was never effective.
    RateLimiter::clear(Str::transliterate('nonexistent@example.com|127.0.0.1'));
    RateLimiter::clear(Str::transliterate('bruteforce@example.com|127.0.0.1'));
    RateLimiter::clear(Str::transliterate('lockout@example.com|127.0.0.1'));
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

it('locks the account for 5 minutes after 5 failed login attempts', function () {
    $email = 'bruteforce@example.com';
    User::factory()->create(['email' => $email]);

    // Make 5 failed attempts with the wrong password
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // The throttle key mirrors LoginRequest::throttleKey()
    $throttleKey = Str::transliterate(Str::lower($email).'|127.0.0.1');

    // After 5 failures, the key should be rate-limited with a 300-second decay
    // (not the old 60-second default), preventing rapid retry after lockout.
    // Independently: 5 attempts × decay=300s → availableIn should be close to 300s.
    $availableIn = RateLimiter::availableIn($throttleKey);

    // Must be locked for longer than 60s (old behaviour) — i.e. at least 4 minutes remain
    expect($availableIn)->toBeGreaterThan(60);
    // And the lock should not exceed the configured 300s window
    expect($availableIn)->toBeLessThanOrEqual(300);
});

it('shows throttle error on 6th login attempt after lockout', function () {
    $email = 'lockout@example.com';
    User::factory()->create(['email' => $email]);

    // Exhaust the 5-attempt allowance
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // 6th attempt should return a validation error about throttling, not credentials
    $response = $this->post('/login', [
        'email' => $email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $errors = session('errors');
    $emailError = $errors?->first('email') ?? '';
    // Assert against the translated throttle message (locale-safe — avoids hardcoded English 'seconds').
    $throttleKey = Str::transliterate(Str::lower($email).'|127.0.0.1');
    $availableIn = RateLimiter::availableIn($throttleKey);
    expect($emailError)->toEqual(trans('auth.throttle', [
        'seconds' => $availableIn,
        'minutes' => ceil($availableIn / 60),
    ]));
});
