<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_receives_remember_days_config(): void
    {
        config(['auth.remember.duration' => 14]);

        $response = $this->get(route('login'));

        $response->assertInertia(fn ($page) => $page->where('rememberDays', 14)
        );
    }

    public function test_login_page_receives_default_remember_days(): void
    {
        // Use default config (30 days)
        $response = $this->get(route('login'));

        $response->assertInertia(fn ($page) => $page->where('rememberDays', 30)
        );
    }

    public function test_register_page_receives_remember_days_config(): void
    {
        config(['auth.remember.duration' => 14]);

        $response = $this->get(route('register'));

        $response->assertInertia(fn ($page) => $page->where('rememberDays', 14)
        );
    }

    public function test_register_page_receives_default_remember_days(): void
    {
        // Use default config (30 days)
        $response = $this->get(route('register'));

        $response->assertInertia(fn ($page) => $page->where('rememberDays', 30)
        );
    }

    public function test_registration_with_remember_me_logs_in_with_remember(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'remember' => true,
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertAuthenticatedAs($user);

        // When remember is true, Laravel sets a remember token on the user
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_registration_without_remember_me_does_not_set_remember_token(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'remember' => false,
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertAuthenticatedAs($user);

        // When remember is false, no remember token is set
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_registration_without_remember_field_defaults_to_no_remember(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertAuthenticatedAs($user);

        // Without remember field, no remember token is set
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_remember_validation_accepts_boolean_true(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'remember' => true,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasNoErrors();
    }

    public function test_remember_validation_accepts_boolean_false(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'remember' => false,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasNoErrors();
    }
}
