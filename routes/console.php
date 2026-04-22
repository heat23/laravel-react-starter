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
// Floor is 60 days: win-back sequence looks back up to 33 days (day-3, day-7, day-14, day-33 windows).
// Do NOT lower below 60 days without auditing all lifecycle sequence max_days values.
Schedule::command('prune-read-notifications --days=60')->daily()->onOneServer();

// Lifecycle email sequences (welcome only — other stage-specific emails were
// removed; re-introduce purpose-built jobs if you need deeper lifecycle coverage).
Schedule::command('emails:send-welcome-sequence')->dailyAt('09:00');

if (config('features.billing.enabled', false)) {
    Schedule::command('subscriptions:check-incomplete')->hourly();
    Schedule::command('billing:enforce-grace-period')->dailyAt('01:00')->onOneServer();
}

if (config('features.admin.enabled', false)) {
    Schedule::command('admin:health-alert')->everyFifteenMinutes();
}

// Trial expiry is gated on plans.trial.enabled (config/plans.php), NOT features.billing.enabled.
// This is intentional: the trial system is independent of the billing feature flag.
// Trials can be active without the billing UI being enabled (e.g. when billing is coming soon),
// and billing can be enabled without offering trials (TRIAL_ENABLED=false).
// The trial expiry command handles stage transitions (trial → expired/converted) which must
// run regardless of whether the Stripe billing UI is currently accessible.
if (config('plans.trial.enabled', false)) {
    Schedule::command('trials:check-expired')->dailyAt('06:00');
}
