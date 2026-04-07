<?php

use App\Enums\AdminCacheKey;
use App\Models\User;
use App\Notifications\AdminHealthAlertNotification;
use App\Services\AdminBillingStatsService;
use App\Services\HealthCheckService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('notification implements ShouldQueue to prevent blocking the scheduler thread', function () {
    expect(AdminHealthAlertNotification::class)
        ->toImplement(ShouldQueue::class);
});

it('sends no alerts when all thresholds are within limits', function () {
    Notification::fake();
    User::factory()->admin()->create();

    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('No alerts')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('sends alert when failed jobs exceed threshold', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    // Seed failed jobs above threshold (default 10)
    for ($i = 0; $i < 11; $i++) {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'TestJob']),
            'exception' => 'Test exception',
            'failed_at' => now(),
        ]);
    }

    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('Alert sent')
        ->assertExitCode(0);

    Notification::assertSentTo($admin, AdminHealthAlertNotification::class);
});

it('sends alert when health checks are unhealthy', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    // Mock HealthCheckService to return unhealthy
    $mock = Mockery::mock(HealthCheckService::class);
    $mock->shouldReceive('runAllChecks')->andReturn([
        'status' => 'unhealthy',
        'checks' => [
            'database' => ['status' => 'error', 'message' => 'Connection failed', 'response_time_ms' => 0],
        ],
        'timestamp' => now()->toISOString(),
    ]);
    $this->app->instance(HealthCheckService::class, $mock);

    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('Alert sent')
        ->assertExitCode(0);

    Notification::assertSentTo($admin, AdminHealthAlertNotification::class);
});

it('does not send to non-admin users', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    // Trigger alert
    for ($i = 0; $i < 11; $i++) {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'TestJob']),
            'exception' => 'Test exception',
            'failed_at' => now(),
        ]);
    }

    $this->artisan('admin:health-alert')->assertExitCode(0);

    Notification::assertSentTo($admin, AdminHealthAlertNotification::class);
    Notification::assertNotSentTo($user, AdminHealthAlertNotification::class);
});

it('respects custom threshold from config', function () {
    Notification::fake();
    User::factory()->admin()->create();

    // Set high threshold
    config(['health.alert_thresholds.failed_jobs' => 100]);

    for ($i = 0; $i < 11; $i++) {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'TestJob']),
            'exception' => 'Test exception',
            'failed_at' => now(),
        ]);
    }

    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('No alerts')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('sends alert when churn rate exceeds warning threshold', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    Cache::forget(AdminCacheKey::BILLING_STATS->value);
    config(['analytics-thresholds.churn_rate.warning' => 10, 'analytics-thresholds.churn_rate.critical' => 20]);

    // Active subscription older than 30 days — counts as the denominator
    $user1 = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user1->id,
        'type' => 'default',
        'stripe_id' => 'sub_old_active',
        'stripe_status' => 'active',
        'ends_at' => null,
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(40),
    ]);

    // Canceled subscription in last 30 days — counts as the numerator
    $user2 = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user2->id,
        'type' => 'default',
        'stripe_id' => 'sub_canceled',
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDays(5),
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(5),
    ]);

    // churn_rate = 1 canceled / 1 active = 100%, exceeds warning threshold of 10%
    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('Alert sent')
        ->assertExitCode(0);

    Notification::assertSentTo($admin, AdminHealthAlertNotification::class);
});

it('sends alert when MRR drops by critical threshold since last run', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    Cache::forget(AdminCacheKey::BILLING_STATS->value);
    Cache::forget(AdminCacheKey::METRICS_MRR_SNAPSHOT->value);

    config(['analytics-thresholds.mrr_drop_percent.warning' => 10, 'analytics-thresholds.mrr_drop_percent.critical' => 25]);

    // Seed previous MRR snapshot: $1000
    // No subscriptions inserted → current MRR = $0 → drop = 100% (exceeds critical 25%)
    Cache::put(AdminCacheKey::METRICS_MRR_SNAPSHOT->value, 1000.0, now()->addDays(7));

    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('Alert sent')
        ->assertExitCode(0);

    Notification::assertSentTo($admin, AdminHealthAlertNotification::class, function ($notification, $channel) {
        $data = $notification->toArray(new stdClass);

        return isset($data['alerts']['mrr_drop']) && $data['alerts']['mrr_drop']['severity'] === 'critical';
    });
});

it('sends alert when MRR drops by warning threshold since last run', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    Cache::forget(AdminCacheKey::BILLING_STATS->value);
    Cache::forget(AdminCacheKey::METRICS_MRR_SNAPSHOT->value);

    config(['analytics-thresholds.mrr_drop_percent.warning' => 10, 'analytics-thresholds.mrr_drop_percent.critical' => 25]);

    // Previous MRR = $1000; mock current MRR = $880 → 12% drop (above warning, below critical)
    Cache::put(AdminCacheKey::METRICS_MRR_SNAPSHOT->value, 1000.0, now()->addDays(7));

    $mock = Mockery::mock(AdminBillingStatsService::class);
    $mock->shouldReceive('getDashboardStats')->andReturn([
        'active_subscriptions' => 10,
        'trialing' => 0,
        'past_due' => 0,
        'canceled' => 0,
        'scheduled_cancellations' => 0,
        'total_ever' => 10,
        'mrr' => 880.0,
        'churn_rate' => 0.0,
        'trial_conversion_rate' => 0.0,
        'activation_rate' => 0.0,
        'activation_rate_all_time' => 0.0,
        'signup_to_paid_conversion' => 0.0,
        'cohort_conversion_30d' => 0.0,
        'cached_at' => now()->toISOString(),
    ]);
    $this->app->instance(AdminBillingStatsService::class, $mock);

    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('Alert sent')
        ->assertExitCode(0);

    Notification::assertSentTo($admin, AdminHealthAlertNotification::class, function ($notification, $channel) {
        $data = $notification->toArray(new stdClass);

        return isset($data['alerts']['mrr_drop']) && $data['alerts']['mrr_drop']['severity'] === 'warning';
    });
});

it('does not send MRR drop alert when no previous snapshot exists', function () {
    Notification::fake();
    User::factory()->admin()->create();

    Cache::forget(AdminCacheKey::BILLING_STATS->value);
    Cache::forget(AdminCacheKey::METRICS_MRR_SNAPSHOT->value);

    // No snapshot → no baseline → no MRR alert, no other triggers
    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('No alerts')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('updates MRR snapshot after each run', function () {
    Notification::fake();
    User::factory()->admin()->create();

    Cache::forget(AdminCacheKey::BILLING_STATS->value);
    Cache::forget(AdminCacheKey::METRICS_MRR_SNAPSHOT->value);

    // Insert a subscription so total_ever > 0 — the guard requires either non-zero MRR
    // or at least one subscription to confirm the stats are real, not a data gap.
    $user = User::factory()->create();
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_snapshot_test',
        'stripe_status' => 'active',
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(Cache::has(AdminCacheKey::METRICS_MRR_SNAPSHOT->value))->toBeFalse();

    $this->artisan('admin:health-alert')->assertExitCode(0);

    // Snapshot is now seeded for next run comparison
    expect(Cache::has(AdminCacheKey::METRICS_MRR_SNAPSHOT->value))->toBeTrue();
});

it('does not alert on churn rate when subscriptions table has no data', function () {
    Notification::fake();
    User::factory()->admin()->create();

    Cache::forget(AdminCacheKey::BILLING_STATS->value);

    // No subscriptions inserted — churn_rate = 0, guard prevents false alert
    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('No alerts')
        ->assertExitCode(0);

    Notification::assertNothingSent();
});

it('sends alert when trial conversion rate falls below warning threshold', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    Cache::forget('metrics:trial_conversion_rate');
    config(['analytics-thresholds.trial_conversion.warning_below' => 20, 'analytics-thresholds.trial_conversion.critical_below' => 10]);

    // 10 trial users, only 1 converts = 10% conversion (below warning of 20%)
    for ($i = 0; $i < 9; $i++) {
        User::factory()->create(['trial_ends_at' => now()->subDays(5)]);
    }
    $convertingUser = User::factory()->create(['trial_ends_at' => now()->subDays(5)]);
    DB::table('subscriptions')->insert([
        'user_id' => $convertingUser->id,
        'type' => 'default',
        'stripe_id' => 'sub_converted',
        'stripe_status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // trial_conversion_rate = 10%, below warning_below = 20%
    $this->artisan('admin:health-alert')
        ->expectsOutputToContain('Alert sent')
        ->assertExitCode(0);

    Notification::assertSentTo($admin, AdminHealthAlertNotification::class);
});
