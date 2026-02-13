<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
