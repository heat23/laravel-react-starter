<?php

namespace Tests\Feature\Auth;

use App\Enums\AdminCacheKey;
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

        $user = \App\Models\User::where('email', 'attacker@test.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_admin);
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
