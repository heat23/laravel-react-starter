<?php

use App\Models\User;
use App\Notifications\AdminHealthAlertNotification;
use App\Services\HealthCheckService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

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
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
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
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
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
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
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
