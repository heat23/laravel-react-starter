<?php

use App\Models\EmailSendLog;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\TrialEndingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('sends trial-ending reminder to user whose trial ends within 3 days', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(2),
    ]);

    $this->artisan('trial:send-reminders')
        ->assertSuccessful();

    Notification::assertSentTo($user, TrialEndingNotification::class, function ($notification) {
        return $notification->daysRemaining === 2;
    });
});

it('does not send when billing is disabled', function () {
    Notification::fake();
    config(['features.billing.enabled' => false]);

    User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(2),
    ]);

    $this->artisan('trial:send-reminders')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not send to already-subscribed users', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(2),
        'stripe_id' => 'cus_trial_subscribed',
    ]);

    createSubscription($user, ['stripe_status' => 'active']);

    $this->artisan('trial:send-reminders')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, TrialEndingNotification::class);
});

it('does not send duplicate reminder when already recorded in EmailSendLog', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(2),
    ]);

    EmailSendLog::record($user->id, 'trial_ending_reminder', 1);

    $this->artisan('trial:send-reminders')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, TrialEndingNotification::class);
});

it('records EmailSendLog entry after sending trial-ending reminder', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(2),
    ]);

    $this->artisan('trial:send-reminders')
        ->assertSuccessful();

    expect(EmailSendLog::alreadySent($user->id, 'trial_ending_reminder', 1))->toBeTrue();
});

it('does not send to users who opted out of marketing emails', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(2),
    ]);

    UserSetting::setValue($user->id, 'marketing_emails', false);

    $this->artisan('trial:send-reminders')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, TrialEndingNotification::class);
});

it('does not send to users with expired trials', function () {
    Notification::fake();

    User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->subDay(),
    ]);

    $this->artisan('trial:send-reminders')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not send to unverified users', function () {
    Notification::fake();

    User::factory()->unverified()->create([
        'trial_ends_at' => now()->addDays(2),
    ]);

    $this->artisan('trial:send-reminders')
        ->assertSuccessful();

    Notification::assertNothingSent();
});
