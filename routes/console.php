<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('audit:prune --days=90')->daily()->onOneServer();
Schedule::command('sanctum:prune-expired')->daily()->onOneServer();
Schedule::command('webhooks:prune-stale')->daily()->onOneServer();
Schedule::command('prune-read-notifications')->daily()->onOneServer();

// Lifecycle email sequences
Schedule::command('emails:send-welcome-sequence')->dailyAt('09:00');
Schedule::command('notifications:send-onboarding')->dailyAt('11:00');
Schedule::command('emails:send-re-engagement')->weekly()->mondays()->at('09:00');
Schedule::command('emails:qualify-leads')->dailyAt('07:00')->onOneServer();

if (config('features.billing.enabled', false)) {
    Schedule::command('subscriptions:check-incomplete')->hourly();
    // emails:send-trial-nudges owns all trial windows (7-day, 3-day, expired) via EmailSendLog dedup.
    // trial:send-reminders was removed from the schedule to prevent double-sends in the 3-day window.
    Schedule::command('emails:send-trial-nudges')->dailyAt('10:00');
    Schedule::command('notifications:send-dunning')->daily();
    Schedule::command('billing:enforce-grace-period')->dailyAt('01:00')->onOneServer();
    Schedule::command('emails:send-win-back')->dailyAt('10:30');
}

Schedule::command('users:compute-scores')->dailyAt('02:00');

if (config('features.admin.enabled', false)) {
    Schedule::command('admin:health-alert')->everyFifteenMinutes();
}

if (config('plans.trial.enabled', false)) {
    Schedule::command('trials:check-expired')->dailyAt('06:00');
}
