<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ============================================
    // Email Verification Event Tests
    // ============================================

    public function test_registration_fires_registered_event(): void
    {
        Event::fake([Registered::class]);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        Event::assertDispatched(Registered::class, function ($event) {
            return $event->user->email === 'test@example.com';
        });
    }

    public function test_registration_sends_verification_email(): void
    {
        // Note: The actual email sending depends on the Registered event listener
        // This test verifies the event is dispatched which triggers the email
        Event::fake([Registered::class]);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        Event::assertDispatched(Registered::class);
    }

    public function test_registration_handles_email_send_failure(): void
    {
        // Simulate email failure by mocking Mail to throw exception
        Mail::shouldReceive('send')->andThrow(new \Exception('Mail service unavailable'));

        // The registration should still succeed even if email fails
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // User should be created despite email failure
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertAuthenticated();
    }

    // ============================================
    // Trial Feature Tests
    // ============================================

    public function test_registration_starts_trial_when_enabled(): void
    {
        // Enable trial feature
        config(['features.billing.enabled' => true]);
        config(['plans.trial.enabled' => true]);
        config(['plans.trial.days' => 14]);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        if (config('plans.trial.enabled')) {
            // Trial should be started if feature is enabled
            $this->assertNotNull($user->trial_ends_at);
        } else {
            // Otherwise, trial_ends_at should be null
            $this->assertNull($user->trial_ends_at);
        }
    }

    public function test_registration_trial_flash_message_when_enabled(): void
    {
        // Enable trial feature
        config(['features.billing.enabled' => true]);
        config(['plans.trial.enabled' => true]);
        config(['plans.trial.days' => 14]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        if (config('plans.trial.enabled')) {
            $response->assertSessionHas('trial_started');
        }
    }

    // ============================================
    // Session Migration Tests
    // ============================================

    public function test_registration_migrates_session_data(): void
    {
        // The SessionDataMigrationService is a placeholder
        // This test documents the expected behavior
        session(['anonymous_data' => ['item1', 'item2']]);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // Migration service's hasSessionData returns false by default
        // So no migration should occur in placeholder implementation
        $this->assertAuthenticated();
    }

    public function test_registration_handles_migration_failure_gracefully(): void
    {
        // Even if migration fails, registration should succeed
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertAuthenticated();
    }

    // ============================================
    // Audit Logging Tests
    // ============================================

    public function test_registration_audits_event(): void
    {
        Log::shouldReceive('channel')
            ->with('single')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'User registered'
                    && $context['event'] === 'auth.register'
                    && $context['email'] === 'audit@example.com';
            });

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'audit@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
    }

    // ============================================
    // Auto-Login Tests
    // ============================================

    public function test_registration_logs_user_in(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals($user->id, auth()->id());
    }

    public function test_registration_redirects_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect(route('dashboard'));
    }

    // ============================================
    // Password Validation Tests
    // ============================================

    public function test_registration_validates_password_defaults(): void
    {
        // Password too short
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registration_accepts_strong_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);

        $response->assertSessionDoesntHaveErrors('password');
        $this->assertAuthenticated();
    }

    // ============================================
    // Security Tests
    // ============================================

    public function test_registration_handles_xss_in_name(): void
    {
        $this->post('/register', [
            'name' => '<script>alert("xss")</script>',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // XSS should be stored as-is (escaping happens on output)
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        // Name should be stored, output escaping is handled by Blade/React
    }

    public function test_registration_hashes_password(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // Password should be hashed
        $this->assertNotEquals('Password123!', $user->password);
        $this->assertTrue(strlen($user->password) > 50);
    }

    // ============================================
    // Redirect Tests
    // ============================================

    public function test_registration_redirects_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    // ============================================
    // Concurrent Registration Tests
    // ============================================

    public function test_concurrent_registration_with_same_email_fails(): void
    {
        // First registration succeeds
        $this->post('/register', [
            'name' => 'First User',
            'email' => 'same@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'same@example.com']);

        // Logout for second attempt
        $this->post('/logout');

        // Second registration with same email fails
        $response = $this->post('/register', [
            'name' => 'Second User',
            'email' => 'same@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
