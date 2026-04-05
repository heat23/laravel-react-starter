<?php

use App\Models\EmailSendLog;
use App\Models\User;
use App\Models\UserSetting;
use App\Notifications\WinBackNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('sends win-back email 1 to user canceled 4 days ago', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_test',
    ]);

    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertSentTo($user, WinBackNotification::class, function ($notification) {
        return $notification->emailNumber === 1;
    });
});

it('does not send when billing is disabled', function () {
    Notification::fake();
    config(['features.billing.enabled' => false]);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not send to users who have reactivated', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_reactivated',
    ]);

    // Canceled subscription that ended 4 days ago
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    // New active subscription
    createSubscription($user, ['stripe_status' => 'active']);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WinBackNotification::class);
});

it('does not send to users who opted out of marketing emails', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_optout',
    ]);

    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    UserSetting::setValue($user->id, 'marketing_emails', false);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WinBackNotification::class);
});

it('does not send duplicate win-back emails', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_dup',
    ]);

    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    // Use EmailSendLog to simulate having already sent win-back email 1
    EmailSendLog::record($user->id, 'win_back', 1);

    Notification::fake();

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertNotSentTo($user, WinBackNotification::class);
});

it('records win-back email in EmailSendLog after sending', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'stripe_id' => 'cus_winback_log',
    ]);

    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(4),
    ]);

    $this->artisan('emails:send-win-back')
        ->assertSuccessful();

    Notification::assertSentTo($user, WinBackNotification::class);
    expect(EmailSendLog::alreadySent($user->id, 'win_back', 1))->toBeTrue();
});
