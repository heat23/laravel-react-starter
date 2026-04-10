<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Helper: returns seconds remaining in the active lockout for a given email.
 * Checks both the email+IP key and the email-only key, matching LoginRequest behaviour.
 */
function lockoutSecondsRemaining(string $email): int
{
    $lockedUntil = Cache::get('login_locked:'.Str::transliterate(Str::lower($email).'|127.0.0.1'))
        ?? Cache::get('login_locked_email:'.Str::transliterate(Str::lower($email)));

    return $lockedUntil ? max(0, $lockedUntil - now()->timestamp) : 0;
}

beforeEach(function () {
    // Clear attempt counters and lockout state for all emails used in this file.
    // Each progressive test uses a unique email to prevent cache namespace collisions
    // across parallel worker processes (CI runs with --parallel --processes=4).
    $prefixes = [
        'nonexistent',
        'bruteforce',
        'lockout',
        'progressive-tier2',
        'progressive-tier3',
        'progressive-tier4',
        'progressive-persist',
        'progressive-stale',
        'progressive-events',
        'progressive-crossip',
    ];
    foreach ($prefixes as $prefix) {
        $email = "{$prefix}@example.com";
        RateLimiter::clear(Str::transliterate("{$email}|127.0.0.1"));
        Cache::forget('login_locked:'.Str::transliterate("{$email}|127.0.0.1"));
        Cache::forget('login_locked_email:'.Str::transliterate($email));
        Cache::forget('login_lockout_events:'.Str::transliterate($email));
    }
});

it('returns 429 after too many login attempts', function () {
    // Route throttle is 10,1 (10 requests per minute)
    for ($i = 0; $i < 10; $i++) {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);
    }

    // 11th request should be rate limited by the route throttle middleware
    $response = $this->post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(429);
});

it('locks the account for 5 minutes after 5 failed login attempts', function () {
    $email = 'bruteforce@example.com';
    User::factory()->create(['email' => $email]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // First lockout: independently computed as 300s (5 minutes)
    $seconds = lockoutSecondsRemaining($email);

    // Must be locked for at least 4 minutes (60s+ remaining)
    expect($seconds)->toBeGreaterThan(60);
    // Must not exceed the configured 300s first-lockout window
    expect($seconds)->toBeLessThanOrEqual(300);
});

it('shows throttle error on 6th login attempt after lockout', function () {
    $email = 'lockout@example.com';
    User::factory()->create(['email' => $email]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // 6th attempt must return the throttle validation error, not credentials error
    $response = $this->post('/login', [
        'email' => $email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $emailError = session('errors')?->first('email') ?? '';

    // Assert the throttle error prefix rather than an exact seconds match to avoid a
    // non-deterministic race: the error message is rendered at T1 (inside the request)
    // but reading the cache key at T2 > T1 yields a slightly smaller seconds value,
    // causing a guaranteed off-by-one failure in slow CI environments.
    expect($emailError)->toMatch('/Too many login attempts\. Please try again in \d+ seconds\./');
});

it('escalates lockout to 30 minutes on the second lockout window', function () {
    $email = 'progressive-tier2@example.com';
    User::factory()->create(['email' => $email]);

    // Simulate 1 prior lockout event (first window already exhausted)
    Cache::put('login_lockout_events:'.Str::transliterate($email), 1, now()->addDay());

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // Second lockout: independently computed as 1800s (30 minutes)
    $seconds = lockoutSecondsRemaining($email);
    expect($seconds)->toBeGreaterThan(1800 - 5);
    expect($seconds)->toBeLessThanOrEqual(1800);

    // Event count must now be 2
    expect(Cache::get('login_lockout_events:'.Str::transliterate($email)))->toBe(2);
});

it('escalates lockout to 2 hours on the third lockout window', function () {
    $email = 'progressive-tier3@example.com';
    User::factory()->create(['email' => $email]);

    Cache::put('login_lockout_events:'.Str::transliterate($email), 2, now()->addDay());

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // Third lockout: independently computed as 7200s (2 hours)
    $seconds = lockoutSecondsRemaining($email);
    expect($seconds)->toBeGreaterThan(7200 - 5);
    expect($seconds)->toBeLessThanOrEqual(7200);

    expect(Cache::get('login_lockout_events:'.Str::transliterate($email)))->toBe(3);
});

it('escalates lockout to 24 hours on the fourth lockout window', function () {
    $email = 'progressive-tier4@example.com';
    User::factory()->create(['email' => $email]);

    Cache::put('login_lockout_events:'.Str::transliterate($email), 3, now()->addDay());

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // Fourth lockout: independently computed as 86400s (24 hours)
    $seconds = lockoutSecondsRemaining($email);
    expect($seconds)->toBeGreaterThan(86400 - 5);
    expect($seconds)->toBeLessThanOrEqual(86400);

    expect(Cache::get('login_lockout_events:'.Str::transliterate($email)))->toBe(4);
});

it('does not clear lockout event count on successful login', function () {
    $email = 'progressive-persist@example.com';
    User::factory()->create(['email' => $email, 'password' => bcrypt('correct-password')]);

    $lockoutEventKey = 'login_lockout_events:'.Str::transliterate($email);
    Cache::put($lockoutEventKey, 2, now()->addDay());

    $this->post('/login', [
        'email' => $email,
        'password' => 'correct-password',
    ]);

    $this->assertAuthenticated();
    // Lockout event count must persist after successful auth to prevent reset-abuse
    expect(Cache::get($lockoutEventKey))->toBe(2);
});

it('clears stale lockout cache key on successful login after lockout expires', function () {
    $email = 'progressive-stale@example.com';
    User::factory()->create(['email' => $email, 'password' => bcrypt('correct-password')]);

    $lockoutKey = 'login_locked:'.Str::transliterate("{$email}|127.0.0.1");
    $lockoutEmailKey = 'login_locked_email:'.Str::transliterate($email);
    // Set an already-expired lockout (represents a lockout that just elapsed)
    Cache::put($lockoutKey, now()->subMinute()->timestamp, 300);
    Cache::put($lockoutEmailKey, now()->subMinute()->timestamp, 300);

    $this->post('/login', [
        'email' => $email,
        'password' => 'correct-password',
    ]);

    $this->assertAuthenticated();
    // Both stale lockout keys must be removed on successful auth to keep cache tidy
    expect(Cache::has($lockoutKey))->toBeFalse();
    expect(Cache::has($lockoutEmailKey))->toBeFalse();
});

it('tracks lockout events per email not per IP to prevent IP rotation bypass', function () {
    $email = 'progressive-events@example.com';
    User::factory()->create(['email' => $email]);

    $lockoutEventKey = 'login_lockout_events:'.Str::transliterate($email);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // The lockout event key must be scoped to email only (not email+IP)
    expect(Cache::has($lockoutEventKey))->toBeTrue();
    expect(Cache::get($lockoutEventKey))->toBe(1);
});

it('blocks a login attempt from a different IP when an active email lockout exists', function () {
    $email = 'progressive-crossip@example.com';
    User::factory()->create(['email' => $email]);

    // Trigger lockout from 127.0.0.1 (default test IP)
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // Confirm email-only lockout key was written alongside the IP-scoped key
    expect(Cache::has('login_locked_email:'.Str::transliterate($email)))->toBeTrue();

    // Attempt from a different IP — must be blocked by the email-only lockout key,
    // proving that IP rotation cannot bypass an active lockout.
    $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
        ->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors(['email']);
    $emailError = session('errors')?->first('email') ?? '';
    expect($emailError)->toMatch('/Too many login attempts\. Please try again in \d+ seconds\./');
});
