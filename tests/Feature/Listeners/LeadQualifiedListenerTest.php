<?php

use App\Events\LeadQualifiedEvent;
use App\Listeners\LeadQualifiedListener;
use App\Models\User;
use App\Notifications\UpgradeNudgeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends UpgradeNudgeNotification for MQL stage when user has not opted out', function () {
    Notification::fake();
    Cache::flush();

    $user = User::factory()->create(['marketing_opt_out' => false]);
    $event = new LeadQualifiedEvent($user, 80, 'mql');

    (new LeadQualifiedListener)->handle($event);

    Notification::assertSentTo($user, UpgradeNudgeNotification::class);
});

it('does not send UpgradeNudgeNotification when user has opted out of marketing emails', function () {
    Notification::fake();
    Cache::flush();

    $user = User::factory()->create(['marketing_opt_out' => true]);
    $event = new LeadQualifiedEvent($user, 85, 'mql');

    (new LeadQualifiedListener)->handle($event);

    Notification::assertNothingSent();
});

it('does not send UpgradeNudgeNotification when already suppressed by cache', function () {
    Notification::fake();

    $user = User::factory()->create(['marketing_opt_out' => false]);
    Cache::put("lead_qualified_nudge:{$user->id}:mql", true, now()->addDays(30));

    $event = new LeadQualifiedEvent($user, 75, 'mql');
    (new LeadQualifiedListener)->handle($event);

    Notification::assertNothingSent();
});

it('suppresses subsequent MQL nudges within 30 days after first send', function () {
    Notification::fake();
    Cache::flush();

    $user = User::factory()->create(['marketing_opt_out' => false]);

    // First event — should send
    $event = new LeadQualifiedEvent($user, 80, 'mql');
    (new LeadQualifiedListener)->handle($event);
    Notification::assertSentTo($user, UpgradeNudgeNotification::class);

    // Second event — should be suppressed
    Notification::fake();
    (new LeadQualifiedListener)->handle($event);
    Notification::assertNothingSent();
});

it('records sql_qualified_at for SQL stage', function () {
    $user = User::factory()->create(['sql_qualified_at' => null]);
    $event = new LeadQualifiedEvent($user, 90, 'sql');

    (new LeadQualifiedListener)->handle($event);

    expect($user->fresh()->sql_qualified_at)->not->toBeNull();
});

it('does not overwrite sql_qualified_at if already set', function () {
    $existingDate = now()->subDays(5);
    $user = User::factory()->create(['sql_qualified_at' => $existingDate]);
    $event = new LeadQualifiedEvent($user, 95, 'sql');

    (new LeadQualifiedListener)->handle($event);

    expect($user->fresh()->sql_qualified_at)->toBe($existingDate->toDateTimeString());
});

it('marketing opt-out takes precedence over cache check for MQL', function () {
    Notification::fake();
    // Clear any existing cache so the cache-suppression guard is not the reason for skip
    $user = User::factory()->create(['marketing_opt_out' => true]);
    Cache::forget("lead_qualified_nudge:{$user->id}:mql");

    $event = new LeadQualifiedEvent($user, 78, 'mql');
    (new LeadQualifiedListener)->handle($event);

    Notification::assertNothingSent();
    // Cache key should NOT have been set since we returned early
    expect(Cache::has("lead_qualified_nudge:{$user->id}:mql"))->toBeFalse();
});
