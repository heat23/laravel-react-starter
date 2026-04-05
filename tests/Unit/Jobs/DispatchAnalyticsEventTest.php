<?php

use App\Jobs\DispatchAnalyticsEvent;
use App\Services\AnalyticsGateway;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

// ============================================
// Constructor / property assignment
// ============================================

test('constructor stores event name, params, and userId', function () {
    $job = new DispatchAnalyticsEvent('auth.login', ['source' => 'email'], 42);

    expect($job->eventName)->toBe('auth.login')
        ->and($job->params)->toBe(['source' => 'email'])
        ->and($job->userId)->toBe(42);
});

test('constructor rejects null userId with TypeError', function () {
    // userId is declared as int (non-nullable); null must be rejected at the type boundary
    // so anonymous events cannot silently bypass the int constraint
    expect(fn () => new DispatchAnalyticsEvent('auth.login', [], null))
        ->toThrow(TypeError::class);
});

// ============================================
// Retry / backoff configuration
// ============================================

test('job has 2 retry attempts', function () {
    $job = new DispatchAnalyticsEvent('auth.login', [], 1);

    expect($job->tries)->toBe(2);
});

test('job has exponential backoff of 10s and 60s', function () {
    $job = new DispatchAnalyticsEvent('auth.login', [], 1);

    expect($job->backoff)->toBe([10, 60]);
});

// ============================================
// Queue dispatch
// ============================================

test('job is dispatched to the queue', function () {
    Queue::fake();

    DispatchAnalyticsEvent::dispatch('auth.register', ['source' => 'social'], 7);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) {
        return $job->eventName === 'auth.register'
            && $job->params === ['source' => 'social']
            && $job->userId === 7;
    });
});

test('dispatch() defers execution — AnalyticsGateway::sendBatch() is not called synchronously', function () {
    Queue::fake();

    $gateway = Mockery::spy(AnalyticsGateway::class);
    app()->instance(AnalyticsGateway::class, $gateway);

    DispatchAnalyticsEvent::dispatch('subscription.created', [], 99);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) {
        return $job->eventName === 'subscription.created'
            && $job->userId === 99;
    });
    $gateway->shouldNotHaveReceived('sendBatch');
});

// ============================================
// handle() — AnalyticsGateway invocation
// ============================================

test('handle delegates to AnalyticsGateway::sendBatch with correct arguments', function () {
    $gateway = Mockery::mock(AnalyticsGateway::class);
    $gateway->shouldReceive('sendBatch')
        ->once()
        ->with([['name' => 'billing.subscription_canceled', 'params' => ['reason' => 'user']]], 55);

    $job = new DispatchAnalyticsEvent('billing.subscription_canceled', ['reason' => 'user'], 55);
    $job->handle($gateway);
});

test('handle passes empty params array to gateway via sendBatch', function () {
    $gateway = Mockery::mock(AnalyticsGateway::class);
    $gateway->shouldReceive('sendBatch')
        ->once()
        ->with([['name' => 'auth.logout', 'params' => []]], 1);

    $job = new DispatchAnalyticsEvent('auth.logout', [], 1);
    $job->handle($gateway);
});

test('handle passes all params unchanged to gateway via sendBatch', function () {
    $params = ['plan' => 'pro', 'billing_period' => 'monthly', 'amount' => 1900];

    $gateway = Mockery::mock(AnalyticsGateway::class);
    $gateway->shouldReceive('sendBatch')
        ->once()
        ->with([['name' => 'subscription.created', 'params' => $params]], 12);

    $job = new DispatchAnalyticsEvent('subscription.created', $params, 12);
    $job->handle($gateway);
});

// ============================================
// failed() — structured error logging
// ============================================

test('failed() logs error with event_name, user_id, and error message', function () {
    Log::spy();

    $job = new DispatchAnalyticsEvent('auth.login', ['source' => 'form'], 42);
    $exception = new RuntimeException('GA4 Measurement Protocol timeout');

    $job->failed($exception);

    // Assert the job's own structured error log fires exactly once.
    // The ->once() is scoped to calls matching the withArgs predicate,
    // so it does not constrain report()'s internal Log::error behaviour.
    Log::shouldHaveReceived('error')
        ->withArgs(function (string $message, array $context) use ($exception) {
            return str_contains($message, 'DispatchAnalyticsEvent failed')
                && $context['event_name'] === 'auth.login'
                && $context['user_id'] === 42
                && $context['error'] === $exception->getMessage();
        })
        ->once();
});

test('failed() does not propagate exceptions for any Throwable subtype', function () {
    Log::spy();

    $job = new DispatchAnalyticsEvent('billing.subscription_canceled', [], 1);

    // Should complete without throwing regardless of the Throwable subtype
    $job->failed(new Error('Fatal error'));
    $job->failed(new Exception('Generic exception'));

    expect(true)->toBeTrue(); // reached without exception
});

test('failed() reports the exception to the application error handler', function () {
    Log::spy();

    $exception = new RuntimeException('GA4 Measurement Protocol timeout');
    $job = new DispatchAnalyticsEvent('auth.login', ['source' => 'form'], 42);

    // The report() helper routes through the exception handler (Sentry, Flare, etc.)
    // Spy on the exception handler to verify report() is called.
    $handler = Mockery::spy(ExceptionHandler::class);
    app()->instance(ExceptionHandler::class, $handler);

    $job->failed($exception);

    $handler->shouldHaveReceived('report')->once()->with($exception);
});
