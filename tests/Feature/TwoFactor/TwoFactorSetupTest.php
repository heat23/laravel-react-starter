<?php

use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config(['features.two_factor.enabled' => true]);
    Queue::fake();
});

it('returns 404 for security page when feature disabled', function () {
    config(['features.two_factor.enabled' => false]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/security');

    $response->assertNotFound();
});

it('renders security page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/security');

    $response->assertOk();
});

it('shows not-enabled state by default', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/security');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Settings/Security')
        ->where('enabled', false)
        ->where('qr_code', null)
    );
});

it('enables 2FA and shows QR code', function () {
    $user = User::factory()->create();

    // Enable 2FA
    $this->actingAs($user)->post('/settings/security/enable');

    // Now get the page - should show QR
    $response = $this->actingAs($user)->get('/settings/security');

    $response->assertInertia(fn ($page) => $page
        ->component('Settings/Security')
        ->where('enabled', false)
        ->whereNot('qr_code', null)
        ->whereNot('secret', null)
    );
});

it('confirms 2FA with valid code', function () {
    $user = User::factory()->create();

    $user->createTwoFactorAuth();
    $code = $user->twoFactorAuth->makeCode();

    $response = $this->actingAs($user)->post('/settings/security/confirm', [
        'code' => $code,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

it('rejects invalid confirmation code', function () {
    $user = User::factory()->create();
    $user->createTwoFactorAuth();

    $response = $this->actingAs($user)->post('/settings/security/confirm', [
        'code' => '000000',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('code');
    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

it('disables 2FA with correct password', function () {
    $user = User::factory()->withTwoFactor()->create();

    expect($user->hasTwoFactorEnabled())->toBeTrue();

    $response = $this->actingAs($user)->delete('/settings/security/disable', [
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

it('rejects disable with wrong password', function () {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->actingAs($user)->delete('/settings/security/disable', [
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('password');
    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

it('returns recovery codes when enabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->actingAs($user)->getJson('/settings/security/recovery-codes');

    $response->assertOk();
    $codes = $response->json('recovery_codes');
    expect($codes)->toHaveCount(10);
});

it('denies recovery codes when not enabled', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/settings/security/recovery-codes');

    $response->assertForbidden();
});

it('regenerates recovery codes', function () {
    $user = User::factory()->withTwoFactor()->create();

    $original = $this->actingAs($user)->getJson('/settings/security/recovery-codes')
        ->json('recovery_codes');

    $this->actingAs($user)->post('/settings/security/recovery-codes');

    $regenerated = $this->actingAs($user)->getJson('/settings/security/recovery-codes')
        ->json('recovery_codes');

    expect($regenerated)->not->toEqual($original);
    expect($regenerated)->toHaveCount(10);
});
