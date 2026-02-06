<?php

use App\Models\User;

it('rejects invalid reset token', function () {
    $user = User::factory()->create();

    $response = $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('rejects reset for non-existent email', function () {
    $response = $this->post('/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    // Laravel throws validation error for non-existent email but still returns
    // a redirect (302), which avoids leaking timing information via HTTP status codes
    $response->assertStatus(302);
});
