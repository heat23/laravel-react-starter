<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('requires authentication', function () {
    $response = $this->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertRedirect('/login');
});

it('updates the password with valid data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertSessionHasNoErrors();

    $user->refresh();
    expect(Hash::check('new-password-123', $user->password))->toBeTrue();
});

it('rejects wrong current password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertSessionHasErrors(['current_password']);
});

it('rejects weak password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/password', [
        'current_password' => 'password',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors(['password']);
});

it('rejects mismatched password confirmation', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'different-password-123',
    ]);

    $response->assertSessionHasErrors(['password']);
});
