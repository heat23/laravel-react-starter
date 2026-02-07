<?php

use App\Models\User;
use App\Notifications\IncompletePaymentReminder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('sends reminder for subscriptions at 1 hour mark', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'incomplete',
        'created_at' => now()->subHour(),
    ]);

    $this->artisan('subscriptions:check-incomplete')
        ->assertExitCode(0);

    Notification::assertSentTo($user, IncompletePaymentReminder::class, function ($notification) {
        return $notification->hoursRemaining === 22;
    });
});

it('sends reminder for subscriptions at 12 hour mark', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'incomplete',
        'created_at' => now()->subHours(12),
    ]);

    $this->artisan('subscriptions:check-incomplete')
        ->assertExitCode(0);

    Notification::assertSentTo($user, IncompletePaymentReminder::class, function ($notification) {
        return $notification->hoursRemaining === 11;
    });
});

it('does not send reminder for subscriptions less than 30 minutes old', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'incomplete',
        'created_at' => now()->subMinutes(15),
    ]);

    $this->artisan('subscriptions:check-incomplete')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('does not send reminder for subscriptions older than 23 hours', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'incomplete',
        'created_at' => now()->subHours(24),
    ]);

    $this->artisan('subscriptions:check-incomplete')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('does not send duplicate reminders within 30 minutes', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'incomplete',
        'created_at' => now()->subHour(),
    ]);

    // Simulate a previously sent notification
    $user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => IncompletePaymentReminder::class,
        'data' => ['type' => 'incomplete_payment_reminder'],
        'created_at' => now()->subMinutes(10),
    ]);

    $this->artisan('subscriptions:check-incomplete')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('skips non-incomplete subscriptions', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => now()]);
    createSubscription($user, [
        'stripe_status' => 'active',
        'created_at' => now()->subHour(),
    ]);

    $this->artisan('subscriptions:check-incomplete')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('reports no incomplete payments when none exist', function () {
    $this->artisan('subscriptions:check-incomplete')
        ->expectsOutput('No incomplete payments found.')
        ->assertExitCode(0);
});
