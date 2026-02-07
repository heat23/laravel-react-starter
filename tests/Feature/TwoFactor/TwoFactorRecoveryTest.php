<?php

use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config(['features.two_factor.enabled' => true]);
    Queue::fake();
});

it('completes login with valid recovery code', function () {
    $user = User::factory()->withTwoFactor()->create();

    $codes = $user->getRecoveryCodes();
    $recoveryCode = $codes->first()['code'];

    $response = $this->withSession(['login.id' => $user->id, 'login.remember' => false])
        ->post('/two-factor-challenge', ['recovery_code' => $recoveryCode]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('marks recovery code as single-use', function () {
    $user = User::factory()->withTwoFactor()->create();

    $codes = $user->getRecoveryCodes();
    $recoveryCode = $codes->first()['code'];

    // Use the code
    $this->withSession(['login.id' => $user->id, 'login.remember' => false])
        ->post('/two-factor-challenge', ['recovery_code' => $recoveryCode]);

    // Log out
    auth()->logout();
    session()->flush();

    // Try to use the same code again
    $response = $this->withSession(['login.id' => $user->id, 'login.remember' => false])
        ->post('/two-factor-challenge', ['recovery_code' => $recoveryCode]);

    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});

it('accepts all 10 recovery codes', function () {
    $user = User::factory()->withTwoFactor()->create();

    $codes = $user->getRecoveryCodes()->pluck('code')->toArray();
    expect($codes)->toHaveCount(10);

    foreach ($codes as $i => $code) {
        auth()->logout();
        session()->flush();

        $response = $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class)
            ->withSession(['login.id' => $user->id, 'login.remember' => false])
            ->post('/two-factor-challenge', ['recovery_code' => $code]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
});

it('rejects invalid recovery code', function () {
    $user = User::factory()->withTwoFactor()->create();

    $response = $this->withSession(['login.id' => $user->id])
        ->post('/two-factor-challenge', ['recovery_code' => 'INVALID-CODE']);

    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});
