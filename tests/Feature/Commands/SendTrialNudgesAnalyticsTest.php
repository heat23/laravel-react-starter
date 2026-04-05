<?php

use App\Enums\AnalyticsEvent;
use App\Jobs\DispatchAnalyticsEvent;
use App\Models\EmailSendLog;
use App\Models\User;
use App\Notifications\TrialNudgeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('dispatches lifecycle.email_sent analytics event after sending trial nudge', function () {
    Queue::fake();
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(7),
    ]);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertSentTo($user, TrialNudgeNotification::class);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === AnalyticsEvent::LIFECYCLE_EMAIL_SENT->value
            && $job->userId === $user->id
            && $job->params['email_type'] === 'trial_nudge'
            && $job->params['email_number'] === 1;
    });
});

it('does not dispatch analytics event when trial nudge email was already sent', function () {
    Queue::fake();
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'trial_ends_at' => now()->addDays(7),
    ]);

    EmailSendLog::record($user->id, 'trial_nudge', 1);

    $this->artisan('emails:send-trial-nudges')->assertSuccessful();

    Notification::assertNothingSent();
    Queue::assertNotPushed(DispatchAnalyticsEvent::class);
});
