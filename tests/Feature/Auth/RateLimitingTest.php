<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('127.0.0.1');
    }

    public function test_registration_is_rate_limited(): void
    {
        // Make 5 requests (the limit) - use invalid data to not create users
        // but still hit the rate limiter
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', [
                'name' => '',
                'email' => 'invalid',
                'password' => 'pass',
                'password_confirmation' => 'pass',
            ]);
        }

        // 6th request should be rate limited
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(429);
    }

    public function test_password_reset_request_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        // Make 3 requests (the limit)
        for ($i = 0; $i < 3; $i++) {
            $this->post('/forgot-password', [
                'email' => 'test@example.com',
            ]);
        }

        // 4th request should be rate limited
        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_password_reset_store_is_rate_limited(): void
    {
        // Make 5 requests (the limit)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/reset-password', [
                'token' => 'invalid-token',
                'email' => 'test@example.com',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);
        }

        // 6th request should be rate limited
        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertStatus(429);
    }
}
