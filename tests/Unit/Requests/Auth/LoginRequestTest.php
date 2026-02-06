<?php

namespace Tests\Unit\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('test@example.com|127.0.0.1');
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('test@example.com|127.0.0.1');
        parent::tearDown();
    }

    // ============================================
    // Validation Rules Tests
    // ============================================

    public function test_rules_requires_email(): void
    {
        $response = $this->post('/login', [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_rules_requires_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_rules_email_must_be_string(): void
    {
        $response = $this->post('/login', [
            'email' => 12345, // Integer instead of string
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_rules_email_must_be_valid_format(): void
    {
        $invalidEmails = [
            'not-an-email',
            'test@',
            '@example.com',
            'test@.com',
            'test@example.',
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ]);

            $response->assertSessionHasErrors('email', "Email '$email' should fail validation");
        }
    }

    public function test_rules_password_must_be_string(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 12345, // Integer instead of string
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_rules_remember_must_be_boolean(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => 'not-a-boolean',
        ]);

        $response->assertSessionHasErrors('remember');
    }

    public function test_rules_remember_accepts_true(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertSessionDoesntHaveErrors('remember');
    }

    public function test_rules_remember_accepts_false(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => false,
        ]);

        $response->assertSessionDoesntHaveErrors('remember');
    }

    public function test_rules_remember_accepts_one_as_truthy(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertSessionDoesntHaveErrors('remember');
    }

    public function test_rules_remember_accepts_zero_as_falsy(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => '0',
        ]);

        $response->assertSessionDoesntHaveErrors('remember');
    }

    public function test_rules_remember_is_optional(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionDoesntHaveErrors('remember');
    }

    // ============================================
    // Authentication Tests
    // ============================================

    public function test_authenticate_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_authenticate_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticate_fails_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticate_updates_last_login_timestamp(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'last_login_at' => null,
        ]);

        $this->assertNull($user->last_login_at);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_authenticate_clears_rate_limiter_on_success(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Simulate some failed attempts
        RateLimiter::hit('test@example.com|127.0.0.1');
        RateLimiter::hit('test@example.com|127.0.0.1');
        RateLimiter::hit('test@example.com|127.0.0.1');

        $this->assertEquals(3, RateLimiter::attempts('test@example.com|127.0.0.1'));

        // Successful login should clear rate limiter
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertEquals(0, RateLimiter::attempts('test@example.com|127.0.0.1'));
    }

    public function test_authenticate_increments_rate_limiter_on_failure(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->assertEquals(0, RateLimiter::attempts('test@example.com|127.0.0.1'));

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertEquals(1, RateLimiter::attempts('test@example.com|127.0.0.1'));
    }

    // ============================================
    // Rate Limiting Tests
    // ============================================

    public function test_ensure_is_not_rate_limited_allows_under_limit(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        // Make 5 attempts (the limit)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // All 5 should have been processed (not blocked)
        $this->assertEquals(5, RateLimiter::attempts('test@example.com|127.0.0.1'));
    }

    public function test_ensure_is_not_rate_limited_blocks_at_6th_attempt(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be blocked
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors')->get('email');
        $this->assertTrue(
            str_contains($errors[0], 'Too many login attempts') ||
            str_contains($errors[0], 'seconds')
        );
    }

    public function test_ensure_is_not_rate_limited_fires_lockout_event(): void
    {
        Event::fake([Lockout::class]);

        User::factory()->create(['email' => 'test@example.com']);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should trigger lockout event
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        Event::assertDispatched(Lockout::class);
    }

    public function test_ensure_is_not_rate_limited_shows_seconds_in_message(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors')->get('email');

        // Message should contain seconds or minutes info
        $this->assertTrue(
            str_contains($errors[0], 'second') || str_contains($errors[0], 'minute'),
            'Rate limit message should contain time info'
        );
    }

    // ============================================
    // Throttle Key Tests
    // ============================================

    public function test_throttle_key_uses_lowercase_email(): void
    {
        $request = new LoginRequest;
        $request->merge(['email' => 'TEST@EXAMPLE.COM']);
        $request->setUserResolver(fn () => null);

        // Simulate the request having an IP
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $key = $request->throttleKey();

        $this->assertStringContainsString('test@example.com', $key);
        $this->assertStringNotContainsString('TEST@EXAMPLE.COM', $key);
    }

    public function test_throttle_key_includes_ip_address(): void
    {
        $request = new LoginRequest;
        $request->merge(['email' => 'test@example.com']);
        $request->setUserResolver(fn () => null);
        $request->server->set('REMOTE_ADDR', '192.168.1.100');

        $key = $request->throttleKey();

        $this->assertStringContainsString('192.168.1.100', $key);
    }

    public function test_throttle_key_format_is_email_pipe_ip(): void
    {
        $request = new LoginRequest;
        $request->merge(['email' => 'test@example.com']);
        $request->setUserResolver(fn () => null);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $key = $request->throttleKey();

        $this->assertEquals('test@example.com|127.0.0.1', $key);
    }

    public function test_throttle_key_handles_unicode_email(): void
    {
        $request = new LoginRequest;
        $request->merge(['email' => 'tëst@example.com']);
        $request->setUserResolver(fn () => null);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $key = $request->throttleKey();

        // Transliterate should convert ë to e
        $this->assertStringContainsString('test@example.com', $key);
    }

    public function test_throttle_key_handles_mixed_case_unicode(): void
    {
        $request = new LoginRequest;
        $request->merge(['email' => 'TËST@EXAMPLE.COM']);
        $request->setUserResolver(fn () => null);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $key = $request->throttleKey();

        // Should be lowercase and transliterated
        $this->assertEquals('test@example.com|127.0.0.1', $key);
    }

    // ============================================
    // Edge Cases
    // ============================================

    public function test_login_with_empty_email_fails(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_with_empty_password_fails(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_with_whitespace_only_email_fails(): void
    {
        $response = $this->post('/login', [
            'email' => '   ',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_with_very_long_email_fails(): void
    {
        $longEmail = str_repeat('a', 300).'@example.com';

        $response = $this->post('/login', [
            'email' => $longEmail,
            'password' => 'password',
        ]);

        // Should fail due to either validation or not finding user
        $this->assertGuest();
    }

    public function test_login_handles_user_without_password(): void
    {
        // Create user with null password (OAuth-only user)
        $user = User::factory()->create(['email' => 'oauth@example.com']);
        \DB::table('users')->where('id', $user->id)->update(['password' => null]);

        $response = $this->post('/login', [
            'email' => 'oauth@example.com',
            'password' => 'any-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    // Note: Email case sensitivity depends on database collation
    // SQLite: case-sensitive by default
    // MySQL: case-insensitive by default (utf8mb4_unicode_ci)
    // This is a database-level concern, not a LoginRequest concern

    public function test_rate_limiting_is_per_email_and_ip_combination(): void
    {
        User::factory()->create(['email' => 'user1@example.com']);
        User::factory()->create(['email' => 'user2@example.com']);

        // 5 failed attempts for user1
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'user1@example.com',
                'password' => 'wrong',
            ]);
        }

        // User2 should NOT be rate limited
        $response = $this->post('/login', [
            'email' => 'user2@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }
}
