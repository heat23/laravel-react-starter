<?php

use App\Enums\AuditEvent;
use App\Http\Middleware\EnsureIsSuperAdmin;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('allows super_admin users through', function () {
    $user = User::factory()->superAdmin()->create();

    $request = Request::create('/admin/system', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsSuperAdmin::class);
    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok');
    expect($response->getStatusCode())->toBe(200);
});

it('returns 403 for regular admin users on super_admin routes', function () {
    // is_admin alone is insufficient — super_admin column must also be true
    $user = User::factory()->admin()->create(['super_admin' => false]);

    $request = Request::create('/admin/system', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsSuperAdmin::class);

    expect(fn () => $middleware->handle($request, fn () => response('ok')))
        ->toThrow(HttpException::class);
});

it('returns 403 for regular non-admin users', function () {
    $user = User::factory()->create(['is_admin' => false, 'super_admin' => false]);

    $request = Request::create('/admin/system', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsSuperAdmin::class);

    expect(fn () => $middleware->handle($request, fn () => response('ok')))
        ->toThrow(HttpException::class);
});

it('logs audit event with super_admin_required reason for regular admin', function () {
    $logged = [];
    $auditService = Mockery::mock(AuditService::class);
    $auditService->shouldReceive('log')
        ->once()
        ->with(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS, Mockery::type('array'))
        ->andReturnUsing(function ($event, $context) use (&$logged) {
            $logged = ['event' => $event, 'context' => $context];
        });

    $this->app->instance(AuditService::class, $auditService);

    $user = User::factory()->admin()->create(['super_admin' => false]);
    $request = Request::create('/admin/system', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsSuperAdmin::class);

    try {
        $middleware->handle($request, fn () => response('ok'));
    } catch (HttpException $e) {
        // expected
    }

    expect($logged['event'])->toBe(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS);
    expect($logged['context']['reason'])->toBe('super_admin_required');
});

it('logs audit event with path and route context', function () {
    $logged = [];
    $auditService = Mockery::mock(AuditService::class);
    $auditService->shouldReceive('log')
        ->once()
        ->with(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS, Mockery::type('array'))
        ->andReturnUsing(function ($event, $context) use (&$logged) {
            $logged = ['event' => $event, 'context' => $context];
        });

    $this->app->instance(AuditService::class, $auditService);

    $user = User::factory()->create(['super_admin' => false]);
    $request = Request::create('/admin/system', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsSuperAdmin::class);

    try {
        $middleware->handle($request, fn () => response('ok'));
    } catch (HttpException $e) {
        // expected
    }

    expect($logged['event'])->toBe(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS);
    expect($logged['context'])->toHaveKey('path');
    expect($logged['context'])->toHaveKey('route');
    expect($logged['context'])->toHaveKey('reason');
});

it('does not log audit event when super_admin accesses restricted route', function () {
    $auditService = Mockery::mock(AuditService::class);
    $auditService->shouldReceive('log')->never();

    $this->app->instance(AuditService::class, $auditService);

    $user = User::factory()->superAdmin()->create();
    $request = Request::create('/admin/system', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsSuperAdmin::class);
    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getStatusCode())->toBe(200);
});

it('returns 403 for unauthenticated requests', function () {
    $logged = [];
    $auditService = Mockery::mock(AuditService::class);
    $auditService->shouldReceive('log')
        ->once()
        ->with(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS, Mockery::type('array'))
        ->andReturnUsing(function ($event, $context) use (&$logged) {
            $logged = ['event' => $event, 'context' => $context];
        });

    $this->app->instance(AuditService::class, $auditService);

    $request = Request::create('/admin/system', 'GET');
    $request->setUserResolver(fn () => null);

    $middleware = app(EnsureIsSuperAdmin::class);

    try {
        $middleware->handle($request, fn () => response('ok'));
    } catch (HttpException $e) {
        // expected
    }

    expect($logged['event'])->toBe(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS);
    expect($logged['context']['reason'])->toBe('super_admin_required');
});
