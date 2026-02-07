<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('audit:prune')->daily();
Schedule::command('sanctum:prune-expired')->daily();

if (config('features.billing.enabled', false)) {
    Schedule::command('subscriptions:check-incomplete')->everyFifteenMinutes();
}
