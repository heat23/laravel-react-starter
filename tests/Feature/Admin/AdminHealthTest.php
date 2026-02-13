<?php

use App\Models\User;
use App\Services\HealthCheckService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    registerAdminRoutes();
});

it('redirects guests to login', function () {
    $this->get('/admin/health')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/health')->assertStatus(403);
});

it('loads health page with check data', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/health');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Health')
        ->has('health.status')
        ->has('health.checks')
        ->has('health.timestamp')
    );
});

it('includes all 4 default checks', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/health');

    $response->assertInertia(fn ($page) => $page
        ->has('health.checks.database')
        ->has('health.checks.cache')
        ->has('health.checks.queue')
        ->has('health.checks.disk')
    );
});

it('each check includes response_time_ms', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/health');

    $response->assertInertia(function ($page) {
        $checks = $page->toArray()['props']['health']['checks'];
        foreach ($checks as $name => $check) {
            expect($check)->toHaveKey('response_time_ms');
            expect($check['response_time_ms'])->toBeNumeric();
        }
    });
});

it('reports healthy when all checks pass', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/health');

    $response->assertInertia(fn ($page) => $page
        ->where('health.status', 'healthy')
    );
});

it('health check service returns ok for database', function () {
    $service = app(HealthCheckService::class);
    $result = $service->checkDatabase();

    expect($result['status'])->toBe('ok');
    expect($result)->toHaveKey('response_time_ms');
});

it('health check service returns ok for cache', function () {
    $service = app(HealthCheckService::class);
    $result = $service->checkCache();

    expect($result['status'])->toBe('ok');
});

it('health check service returns ok for queue', function () {
    $service = app(HealthCheckService::class);
    $result = $service->checkQueue();

    expect($result['status'])->toBe('ok');
});

it('health check service returns ok for disk', function () {
    $service = app(HealthCheckService::class);
    $result = $service->checkDisk();

    expect($result['status'])->toBe('ok');
});

it('health check service handles custom checks', function () {
    $service = app(HealthCheckService::class);
    $service->registerCheck('custom', fn () => ['status' => 'ok', 'message' => 'Custom check']);

    Cache::flush();
    $result = $service->runAllChecks();

    expect($result['checks'])->toHaveKey('custom');
    expect($result['checks']['custom']['status'])->toBe('ok');
});

it('reports degraded when custom check has warning', function () {
    $service = app(HealthCheckService::class);
    $service->registerCheck('slow_api', fn () => ['status' => 'warning', 'message' => 'Slow response']);

    Cache::flush();
    $result = $service->runAllChecks();

    expect($result['status'])->toBe('degraded');
});

it('reports unhealthy when custom check has error', function () {
    $service = app(HealthCheckService::class);
    $service->registerCheck('broken_api', fn () => ['status' => 'error', 'message' => 'Connection failed']);

    Cache::flush();
    $result = $service->runAllChecks();

    expect($result['status'])->toBe('unhealthy');
});

it('timed check catches exceptions and returns error', function () {
    $service = app(HealthCheckService::class);
    $result = $service->timedCheck(fn () => throw new \RuntimeException('Test failure'));

    expect($result['status'])->toBe('error');
    expect($result['message'])->toBe('Check failed');
    expect($result)->toHaveKey('response_time_ms');
});
