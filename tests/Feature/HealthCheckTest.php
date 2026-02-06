<?php

use Illuminate\Support\Facades\DB;

test('returns healthy when all checks pass', function () {
    config(['health.allowed_ips' => '127.0.0.1']);

    $response = $this->getJson('/health');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'checks' => [
                'database' => ['status', 'message', 'response_time_ms'],
                'cache' => ['status', 'message', 'response_time_ms'],
                'queue' => ['status', 'message', 'response_time_ms'],
                'disk' => ['status', 'message', 'response_time_ms'],
            ],
            'timestamp',
        ])
        ->assertJsonPath('status', 'healthy');
});

test('returns 403 when unauthorized in production', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['health.token' => null, 'health.allowed_ips' => '10.0.0.1']);

    $response = $this->getJson('/health');

    $response->assertStatus(403);
});

test('allows access with valid token', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['health.token' => 'secret-token', 'health.allowed_ips' => null]);

    $response = $this->getJson('/health?token=secret-token');

    $response->assertOk()
        ->assertJsonPath('status', 'healthy');
});

test('allows access with bearer token', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['health.token' => 'secret-token', 'health.allowed_ips' => null]);

    $response = $this->getJson('/health', [
        'Authorization' => 'Bearer secret-token',
    ]);

    $response->assertOk();
});

test('allows access with valid ip', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['health.token' => null, 'health.allowed_ips' => '127.0.0.1']);

    $response = $this->getJson('/health');

    $response->assertOk();
});

test('returns degraded on disk warning', function () {
    config(['health.allowed_ips' => '127.0.0.1']);
    config(['health.disk_warning_percent' => 1]);
    config(['health.disk_critical_percent' => 99]);

    $response = $this->getJson('/health');

    $response->assertOk();
    $checks = $response->json('checks');
    $this->assertEquals('warning', $checks['disk']['status']);
    $this->assertEquals('degraded', $response->json('status'));
});

test('returns unhealthy on db failure', function () {
    config(['health.allowed_ips' => '127.0.0.1']);

    // Mock DB to throw an exception for the health check
    DB::shouldReceive('select')
        ->once()
        ->with('SELECT 1')
        ->andThrow(new \RuntimeException('Connection refused'));

    $response = $this->getJson('/health');

    $response->assertStatus(503)
        ->assertJsonPath('status', 'unhealthy');

    $checks = $response->json('checks');
    $this->assertEquals('error', $checks['database']['status']);
});

test('existing up endpoint still works', function () {
    $response = $this->get('/up');

    $response->assertOk();
});
