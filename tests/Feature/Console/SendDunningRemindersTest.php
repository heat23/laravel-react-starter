<?php

use App\Models\EmailSendLog;
use App\Models\User;
use App\Notifications\DunningReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('skips execution when billing feature is disabled', function () {
    Notification::fake();
    config(['features.billing.enabled' => false]);

    $user = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_past_due',
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(4),
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    $this->artisan('notifications:send-dunning')
        ->expectsOutputToContain('Billing feature is disabled')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('sends dunning reminder to users with past-due subscriptions in the correct window', function () {
    Notification::fake();
    config(['features.billing.enabled' => true]);
    config(['email-sequences.dunning' => [1 => ['days' => 3, 'max_days' => 5]]]);

    $user = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_past_due',
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(4),
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    $this->artisan('notifications:send-dunning')
        ->expectsOutputToContain('Sent 1 dunning reminders')
        ->assertExitCode(0);

    Notification::assertSentTo($user, DunningReminderNotification::class, function ($n) {
        return $n->emailNumber === 1;
    });
});

it('does not send if the subscription is not past_due', function () {
    Notification::fake();
    config(['features.billing.enabled' => true]);
    config(['email-sequences.dunning' => [1 => ['days' => 3, 'max_days' => 5]]]);

    $user = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_active',
        'stripe_status' => 'active',
        'past_due_since' => null,
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    $this->artisan('notifications:send-dunning')
        ->expectsOutputToContain('Sent 0 dunning reminders')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('skips users when their email number was already sent (idempotency)', function () {
    Notification::fake();
    config(['features.billing.enabled' => true]);
    config(['email-sequences.dunning' => [1 => ['days' => 3, 'max_days' => 5]]]);

    $user = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_past_due',
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(4),
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    EmailSendLog::record($user->id, 'dunning_reminder', 1);

    $this->artisan('notifications:send-dunning')
        ->expectsOutputToContain('Sent 0 dunning reminders')
        ->assertExitCode(0);

    Notification::assertNotSentTo($user, DunningReminderNotification::class);
});

it('skips subscriptions outside the time window', function () {
    Notification::fake();
    config(['features.billing.enabled' => true]);
    config(['email-sequences.dunning' => [1 => ['days' => 3, 'max_days' => 5]]]);

    $user = User::factory()->create();
    // past_due for 10 days — outside the 3-5 day window
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_past_due_old',
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(10),
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    $this->artisan('notifications:send-dunning')
        ->expectsOutputToContain('Sent 0 dunning reminders')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('records the send in EmailSendLog after notification is dispatched', function () {
    Notification::fake();
    config(['features.billing.enabled' => true]);
    config(['email-sequences.dunning' => [1 => ['days' => 3, 'max_days' => 5]]]);

    $user = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_past_due',
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(4),
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    $this->artisan('notifications:send-dunning')->assertExitCode(0);

    expect(EmailSendLog::alreadySent($user->id, 'dunning_reminder', 1))->toBeTrue();
});

it('sends multiple email numbers in sequence', function () {
    Notification::fake();
    config(['features.billing.enabled' => true]);
    config(['email-sequences.dunning' => [
        1 => ['days' => 3, 'max_days' => 5],
        2 => ['days' => 7, 'max_days' => 10],
    ]]);

    $user1 = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user1->id,
        'type' => 'default',
        'stripe_id' => 'sub_email1',
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(4),
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    $user2 = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user2->id,
        'type' => 'default',
        'stripe_id' => 'sub_email2',
        'stripe_status' => 'past_due',
        'past_due_since' => now()->subDays(8),
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    $this->artisan('notifications:send-dunning')
        ->expectsOutputToContain('Sent 2 dunning reminders')
        ->assertExitCode(0);

    Notification::assertSentTo($user1, DunningReminderNotification::class, fn ($n) => $n->emailNumber === 1);
    Notification::assertSentTo($user2, DunningReminderNotification::class, fn ($n) => $n->emailNumber === 2);
});
