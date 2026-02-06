<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config()->set('features.email_verification.enabled', true);
});

it('sends verification notification for unverified user', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertRedirect();
    $response->assertSessionHas('status', 'verification-link-sent');
});

it('redirects verified user to dashboard', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertRedirect(route('dashboard', absolute: false));
});
