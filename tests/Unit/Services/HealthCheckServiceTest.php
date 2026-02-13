<?php

use App\Services\HealthCheckService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Cache::flush();
    $this->service = new HealthCheckService;
});

// ============================================
// checkDatabase() tests
// ============================================

test('checkDatabase returns ok status', function () {
    $result = $this->service->checkDatabase();

    expect($result['status'])->toBe('ok');
    expect($result['message'])->toBe('Connection successful');
    expect($result)->toHaveKey('response_time_ms');
});

test('checkDatabase returns error when connection fails', function () {
    DB::shouldReceive('select')
        ->once()
        ->andThrow(new \RuntimeException('Connection refused'));

    $result = $this->service->checkDatabase();

    expect($result['status'])->toBe('error');
    expect($result['message'])->toBe('Check failed');
});

// ============================================
// checkCache() tests
// ============================================

test('checkCache returns ok status', function () {
    $result = $this->service->checkCache();

    expect($result['status'])->toBe('ok');
    expect($result['message'])->toBe('Read/write successful');
});

test('checkCache returns error on mismatch', function () {
    Cache::shouldReceive('put')->once();
    Cache::shouldReceive('get')->once()->andReturn('wrong_value');
    Cache::shouldReceive('forget')->once();

    $result = $this->service->checkCache();

    expect($result['status'])->toBe('error');
    expect($result['message'])->toBe('Cache read/write mismatch');
});

// ============================================
// checkQueue() tests
// ============================================

test('checkQueue returns ok when queue is small', function () {
    Queue::shouldReceive('size')->once()->andReturn(0);

    $result = $this->service->checkQueue();

    expect($result['status'])->toBe('ok');
    expect($result['message'])->toBe('Queue nominal');
});

test('checkQueue returns warning when queue exceeds threshold', function () {
    config(['health.queue_warning_threshold' => 100]);
    Queue::shouldReceive('size')->once()->andReturn(150);

    $result = $this->service->checkQueue();

    expect($result['status'])->toBe('warning');
    expect($result['message'])->toBe('Queue backlog detected');
});

test('checkQueue uses default threshold of 1000', function () {
    Queue::shouldReceive('size')->once()->andReturn(999);

    $result = $this->service->checkQueue();

    expect($result['status'])->toBe('ok');
});

// ============================================
// checkDisk() tests
// ============================================

test('checkDisk returns ok for normal usage', function () {
    $result = $this->service->checkDisk();

    expect($result['status'])->toBeIn(['ok', 'warning']);
    expect($result)->toHaveKey('response_time_ms');
});

// ============================================
// runAllChecks() tests
// ============================================

test('runAllChecks returns all check results', function () {
    Queue::shouldReceive('size')->once()->andReturn(0);

    $result = $this->service->runAllChecks();

    expect($result)->toHaveKey('status');
    expect($result)->toHaveKey('checks');
    expect($result)->toHaveKey('timestamp');
    expect($result['checks'])->toHaveKeys(['database', 'cache', 'queue', 'disk']);
});

test('runAllChecks returns healthy when all checks pass', function () {
    Queue::shouldReceive('size')->once()->andReturn(0);

    $result = $this->service->runAllChecks();

    expect($result['status'])->toBe('healthy');
});

test('runAllChecks returns degraded when a check has warning', function () {
    config(['health.queue_warning_threshold' => 10]);
    Queue::shouldReceive('size')->once()->andReturn(50);

    $result = $this->service->runAllChecks();

    expect($result['status'])->toBe('degraded');
});

test('runAllChecks caches results', function () {
    Queue::shouldReceive('size')->once()->andReturn(0);

    $this->service->runAllChecks();

    expect(Cache::has('health_checks'))->toBeTrue();
});

// ============================================
// registerCheck() tests
// ============================================

test('registerCheck adds custom check to results', function () {
    Queue::shouldReceive('size')->once()->andReturn(0);

    $this->service->registerCheck('redis', fn () => ['status' => 'ok', 'message' => 'Connected']);

    $result = $this->service->runAllChecks();

    expect($result['checks'])->toHaveKey('redis');
    expect($result['checks']['redis']['status'])->toBe('ok');
});

test('custom check failure sets status to unhealthy', function () {
    Queue::shouldReceive('size')->once()->andReturn(0);

    $this->service->registerCheck('external_api', fn () => ['status' => 'error', 'message' => 'Timeout']);

    $result = $this->service->runAllChecks();

    expect($result['status'])->toBe('unhealthy');
});

test('custom check exception is caught', function () {
    Queue::shouldReceive('size')->once()->andReturn(0);

    $this->service->registerCheck('broken', function () {
        throw new \RuntimeException('Unexpected error');
    });

    $result = $this->service->runAllChecks();

    expect($result['checks']['broken']['status'])->toBe('error');
    expect($result['checks']['broken']['message'])->toBe('Check failed');
});

// ============================================
// timedCheck() tests
// ============================================

test('timedCheck adds response_time_ms to result', function () {
    $result = $this->service->timedCheck(fn () => ['status' => 'ok', 'message' => 'Test']);

    expect($result)->toHaveKey('response_time_ms');
    expect($result['response_time_ms'])->toBeFloat();
});

test('timedCheck catches exceptions', function () {
    $result = $this->service->timedCheck(function () {
        throw new \Exception('Test failure');
    });

    expect($result['status'])->toBe('error');
    expect($result['message'])->toBe('Check failed');
    expect($result)->toHaveKey('response_time_ms');
});
