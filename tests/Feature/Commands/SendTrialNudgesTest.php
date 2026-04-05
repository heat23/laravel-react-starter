<?php

use App\Models\EmailSendLog;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\TrialNudgeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('sends 7-day nudge to user with trial ending in ~7 days', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(7),
    ]);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertSentTo($user, TrialNudgeNotification::class, function ($n) {
        return $n->emailNumber === 1;
    });
});

it('sends 3-day nudge to user with trial ending in ~3 days', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(3),
    ]);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertSentTo($user, TrialNudgeNotification::class, function ($n) {
        return $n->emailNumber === 2;
    });
});

it('sends expired nudge to user whose trial just expired', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->subHour(),
    ]);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertSentTo($user, TrialNudgeNotification::class, function ($n) {
        return $n->emailNumber === 3;
    });
});

it('does not send when billing is disabled', function () {
    Notification::fake();
    config(['features.billing.enabled' => false]);

    User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(3),
    ]);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not send to already-subscribed users', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(3),
        'stripe_id' => 'cus_trial_subscribed',
    ]);

    createSubscription($user, ['stripe_status' => 'active']);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertNotSentTo($user, TrialNudgeNotification::class);
});

it('does not send duplicate via EmailSendLog dedup', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(3),
    ]);

    EmailSendLog::record($user->id, 'trial_nudge', 2);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertNotSentTo($user, TrialNudgeNotification::class);
});

it('does not send to users who opted out of marketing emails', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(3),
    ]);

    UserSetting::setValue($user->id, 'marketing_emails', false);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertNotSentTo($user, TrialNudgeNotification::class);
});

it('does not send to unverified users', function () {
    Notification::fake();

    User::factory()->unverified()->create([
        'trial_ends_at' => now()->addDays(3),
    ]);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertNothingSent();
});
