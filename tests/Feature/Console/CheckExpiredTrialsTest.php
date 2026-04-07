<?php

use App\Enums\AnalyticsEvent;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('skips execution when trials are disabled', function () {
    config(['plans.trial.enabled' => false]);

    User::factory()->create(['trial_ends_at' => now()->subDays(2)]);

    $this->artisan('trials:check-expired')
        ->expectsOutputToContain('Trials are disabled')
        ->assertExitCode(0);

    expect(AuditLog::where('event', AnalyticsEvent::TRIAL_EXPIRED->value)->count())->toBe(0);
});

it('logs TRIAL_EXPIRED for users whose trial has ended without an active subscription', function () {
    config(['plans.trial.enabled' => true]);

    $user = User::factory()->create([
        'trial_ends_at' => now()->subDays(3),
        'email_verified_at' => now()->subDays(10),
    ]);

    $this->artisan('trials:check-expired')->assertExitCode(0);

    expect(AuditLog::where('event', AnalyticsEvent::TRIAL_EXPIRED->value)
        ->where('user_id', $user->id)
        ->exists()
    )->toBeTrue();
});

it('skips users whose trial has not yet expired', function () {
    config(['plans.trial.enabled' => true]);

    $user = User::factory()->create([
        'trial_ends_at' => now()->addDays(2),
    ]);

    $this->artisan('trials:check-expired')->assertExitCode(0);

    expect(AuditLog::where('event', AnalyticsEvent::TRIAL_EXPIRED->value)
        ->where('user_id', $user->id)
        ->exists()
    )->toBeFalse();
});

it('skips users with an active subscription', function () {
    config(['plans.trial.enabled' => true]);

    $user = User::factory()->create(['trial_ends_at' => now()->subDays(3)]);

    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_active',
        'stripe_status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('trials:check-expired')->assertExitCode(0);

    expect(AuditLog::where('event', AnalyticsEvent::TRIAL_EXPIRED->value)
        ->where('user_id', $user->id)
        ->exists()
    )->toBeFalse();
});

it('is idempotent — does not log TRIAL_EXPIRED twice for the same user', function () {
    config(['plans.trial.enabled' => true]);

    $user = User::factory()->create(['trial_ends_at' => now()->subDays(3)]);

    $this->artisan('trials:check-expired')->assertExitCode(0);
    $this->artisan('trials:check-expired')->assertExitCode(0);

    expect(AuditLog::where('event', AnalyticsEvent::TRIAL_EXPIRED->value)
        ->where('user_id', $user->id)
        ->count()
    )->toBe(1);
});

it('skips users with no trial_ends_at set', function () {
    config(['plans.trial.enabled' => true]);

    $user = User::factory()->create(['trial_ends_at' => null]);

    $this->artisan('trials:check-expired')->assertExitCode(0);

    expect(AuditLog::where('event', AnalyticsEvent::TRIAL_EXPIRED->value)
        ->where('user_id', $user->id)
        ->exists()
    )->toBeFalse();
});
