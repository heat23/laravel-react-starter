<?php

namespace Tests\Feature\Auth;

use App\Enums\AdminCacheKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_cannot_set_is_admin_via_registration(): void
    {
        $this->post('/register', [
            'name' => 'Attacker',
            'email' => 'attacker@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'is_admin' => true,
        ]);

        $user = User::where('email', 'attacker@test.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_admin);
    }

    public function test_cannot_set_email_verified_at_via_registration_form(): void
    {
        $this->post('/register', [
            'name' => 'Attacker',
            'email' => 'attacker2@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'email_verified_at' => now()->toDateTimeString(),
        ]);

        $user = User::where('email', 'attacker2@test.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
    }

    public function test_cannot_set_super_admin_via_registration_form(): void
    {
        $this->post('/register', [
            'name' => 'Attacker',
            'email' => 'superattacker@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'super_admin' => true,
        ]);

        $user = User::where('email', 'superattacker@test.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->super_admin);
    }

    public function test_registration_starts_trial_when_enabled(): void
    {
        config(['plans.trial.enabled' => true, 'plans.trial.days' => 14]);

        $response = $this->post('/register', [
            'name' => 'Trial User',
            'email' => 'trial@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect();

        $user = User::where('email', 'trial@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->trial_ends_at);
        $this->assertTrue($user->trial_ends_at->isFuture());
        $this->assertEqualsWithDelta(14, now()->diffInDays($user->trial_ends_at), 1);
    }

    public function test_registration_skips_trial_when_disabled(): void
    {
        config(['plans.trial.enabled' => false]);

        $this->post('/register', [
            'name' => 'No Trial User',
            'email' => 'notrial@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $user = User::where('email', 'notrial@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->trial_ends_at);
    }

    public function test_registration_invalidates_signup_chart_cache(): void
    {
        // Pre-populate the signup chart cache key
        Cache::put(AdminCacheKey::DASHBOARD_SIGNUP_CHART->value, ['stale' => 'data'], 3600);

        $this->assertTrue(Cache::has(AdminCacheKey::DASHBOARD_SIGNUP_CHART->value));

        $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertFalse(Cache::has(AdminCacheKey::DASHBOARD_SIGNUP_CHART->value));
    }
}
