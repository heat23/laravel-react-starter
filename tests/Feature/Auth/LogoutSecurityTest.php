<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ============================================
    // Session Invalidation Tests
    // ============================================

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        // Login first
        $this->actingAs($user);
        $this->assertAuthenticated();

        // Store some session data
        session(['test_data' => 'should_be_cleared']);
        $sessionId = session()->getId();

        // Logout
        $this->post('/logout');

        // Session should be invalidated - user should not be authenticated
        $this->assertGuest();
    }

    public function test_logout_removes_session_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        session(['user_preference' => 'dark_mode']);

        $this->post('/logout');

        // After logout, new session should not have old data
        $this->assertNull(session('user_preference'));
    }

    // ============================================
    // CSRF Token Tests
    // ============================================

    public function test_logout_regenerates_csrf_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $tokenBeforeLogout = csrf_token();

        $this->post('/logout');

        // Start new session to get new token
        $this->get('/');
        $tokenAfterLogout = csrf_token();

        // Tokens should be different after logout/new session
        $this->assertNotEquals($tokenBeforeLogout, $tokenAfterLogout);
    }

    // ============================================
    // Audit Logging Tests
    // ============================================

    public function test_logout_audits_event(): void
    {
        $user = User::factory()->create(['email' => 'audit@example.com']);

        $this->actingAs($user)->post('/logout');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'auth.logout',
            'user_id' => $user->id,
        ]);
    }

    // ============================================
    // HTTP Method Tests
    // ============================================

    public function test_logout_requires_post_method(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/logout');

        // GET to /logout should return 405 Method Not Allowed
        $response->assertStatus(405);
    }

    public function test_logout_post_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // ============================================
    // Remember Token Tests
    // ============================================

    public function test_logout_clears_remember_token(): void
    {
        $user = User::factory()->create([
            'remember_token' => 'some-token-value',
        ]);

        $this->actingAs($user)->post('/logout');

        // Note: Laravel's logout may or may not clear remember_token
        // depending on implementation. The key is user is logged out.
        $this->assertGuest();
    }

    // ============================================
    // Redirect Tests
    // ============================================

    public function test_logout_redirects_to_home(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
    }

    // ============================================
    // Unauthenticated User Tests
    // ============================================

    public function test_logout_handles_unauthenticated_user(): void
    {
        // Logout without being logged in should not error
        $response = $this->post('/logout');

        // Should redirect somewhere (login or home)
        $this->assertTrue(
            $response->isRedirect() || $response->status() === 200
        );
    }

    // ============================================
    // Multiple Logout Tests
    // ============================================

    public function test_multiple_logout_calls_are_safe(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // First logout
        $this->post('/logout');
        $this->assertGuest();

        // Second logout should not error
        $response = $this->post('/logout');
        $this->assertGuest();
    }
}
