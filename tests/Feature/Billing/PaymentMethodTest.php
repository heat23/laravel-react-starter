<?php

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('allows updating payment method for subscribed user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('updatePaymentMethod')
        ->once()
        ->withArgs(fn ($u, $pm) => $pm === 'pm_new_card');
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->post('/billing/payment-method', [
        'payment_method' => 'pm_new_card',
    ]);

    $response->assertRedirect(route('billing.index'));
    $response->assertSessionHas('success');
});

it('validates payment_method is required', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/billing/payment-method', []);

    $response->assertSessionHasErrors('payment_method');
});

it('requires authentication to update payment method', function () {
    $response = $this->post('/billing/payment-method', [
        'payment_method' => 'pm_new_card',
    ]);

    $response->assertRedirect(route('login'));
});
