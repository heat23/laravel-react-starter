<?php

use App\Models\User;
use App\Services\BillingService;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('cancels active subscription when user deletes account', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    createSubscription($user, ['stripe_status' => 'active']);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('cancelSubscription')
        ->once()
        ->withArgs(fn ($u, $immediately) => $u->id === $user->id && $immediately === true);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('allows account deletion for non-subscribed user', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('handles grace period subscription on account deletion', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    createSubscription($user, [
        'stripe_status' => 'active',
        'ends_at' => now()->addDays(10),
    ]);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('cancelSubscription')
        ->once()
        ->withArgs(fn ($u, $immediately) => $u->id === $user->id && $immediately === true);
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('still deletes account when subscription cancellation fails and dispatches cleanup job', function () {
    Illuminate\Support\Facades\Queue::fake();

    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'stripe_id' => 'cus_test_orphan',
    ]);
    createSubscription($user, ['stripe_status' => 'active']);

    $mock = Mockery::mock(BillingService::class)->makePartial();
    $mock->shouldReceive('cancelSubscription')
        ->once()
        ->andThrow(new \RuntimeException('Stripe API error'));
    app()->instance(BillingService::class, $mock);

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    expect(User::withTrashed()->find($user->id))->toBeNull();

    Illuminate\Support\Facades\Queue::assertPushed(
        \App\Jobs\CancelOrphanedStripeSubscription::class,
        fn ($job) => true,
    );
});

it('skips subscription cancellation for ended subscription', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    // No mock needed — subscribed() returns false for ended subscriptions,
    // so cancelSubscription should not be called

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('allows account deletion when billing feature is disabled', function () {
    config(['features.billing.enabled' => false]);
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    expect(User::withTrashed()->find($user->id))->toBeNull();
});
