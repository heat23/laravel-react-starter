<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ============================================
    // Authentication Success Tests
    // ============================================

    public function test_login_with_remember_authenticates_user(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_login_without_remember_authenticates_user(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => false,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    // ============================================
    // Null/Empty Password Tests
    // ============================================

    public function test_login_handles_user_with_null_password(): void
    {
        // Create user with null password (OAuth-only user)
        $user = User::factory()->create(['email' => 'oauth@example.com']);
        \DB::table('users')->where('id', $user->id)->update(['password' => null]);

        $this->post('/login', [
            'email' => 'oauth@example.com',
            'password' => 'any-password',
        ]);

        $this->assertGuest();
    }

    public function test_login_handles_user_with_empty_password(): void
    {
        // Create user with empty string password
        $user = User::factory()->create(['email' => 'empty@example.com']);
        \DB::table('users')->where('id', $user->id)->update(['password' => '']);

        $this->post('/login', [
            'email' => 'empty@example.com',
            'password' => '',
        ]);

        // Should fail - empty password should not authenticate
        $this->assertGuest();
    }

    // ============================================
    // Authenticated User Redirect Tests
    // ============================================

    public function test_login_page_redirects_authenticated_users_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard'));
    }

    // ============================================
    // Invalid Credentials Tests
    // ============================================

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_login_fails_with_missing_email(): void
    {
        $this->post('/login', [
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_login_fails_with_missing_password(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $this->assertGuest();
    }

    // ============================================
    // Input Handling Tests
    // ============================================

    public function test_login_handles_xss_attempt_in_email(): void
    {
        $this->post('/login', [
            'email' => '<script>alert("xss")</script>@example.com',
            'password' => 'password',
        ]);

        // Should fail validation and not authenticate
        $this->assertGuest();
    }

    public function test_login_handles_sql_injection_attempt(): void
    {
        $this->post('/login', [
            'email' => "admin'--@example.com",
            'password' => "' OR '1'='1",
        ]);

        // Should fail authentication
        $this->assertGuest();
    }

    // ============================================
    // Session Tests
    // ============================================

    public function test_login_regenerates_session(): void
    {
        $user = User::factory()->create(['email' => 'session@example.com']);

        // Start session
        $this->get('/login');
        $initialSessionId = session()->getId();

        // Login
        $this->post('/login', [
            'email' => 'session@example.com',
            'password' => 'password',
        ]);

        // Session should be regenerated
        $this->assertNotEquals($initialSessionId, session()->getId());
    }

    // ============================================
    // Redirect Tests
    // ============================================

    public function test_login_redirects_after_successful_auth(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Should redirect after successful login
        $response->assertRedirect();
        $this->assertAuthenticated();
    }
}
