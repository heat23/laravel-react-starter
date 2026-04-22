<?php

use App\Enums\AuditEvent;
use App\Http\Middleware\EnsureIsAdmin;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('allows admin users through', function () {
    $user = User::factory()->admin()->create();

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsAdmin::class);
    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok');
    expect($response->getStatusCode())->toBe(200);
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsAdmin::class);

    expect(fn () => $middleware->handle($request, fn () => response('ok')))
        ->toThrow(HttpException::class);
});

it('logs audit event when non-admin user attempts admin access', function () {
    $logged = [];
    $auditService = Mockery::mock(AuditService::class);
    $auditService->shouldReceive('log')
        ->once()
        ->with(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS, Mockery::type('array'))
        ->andReturnUsing(function ($event, $context) use (&$logged) {
            $logged = ['event' => $event, 'context' => $context];
        });

    $this->app->instance(AuditService::class, $auditService);

    $user = User::factory()->create(['is_admin' => false]);
    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsAdmin::class);

    try {
        $middleware->handle($request, fn () => response('ok'));
    } catch (HttpException $e) {
        // expected
    }

    expect($logged['event'])->toBe(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS);
    expect($logged['context'])->toHaveKey('path');
    expect($logged['context'])->toHaveKey('route');
});

it('does not log audit event when admin user accesses admin area', function () {
    $auditService = Mockery::mock(AuditService::class);
    $auditService->shouldReceive('log')->never();

    $this->app->instance(AuditService::class, $auditService);

    $user = User::factory()->admin()->create();
    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsAdmin::class);
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

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => null);

    $middleware = app(EnsureIsAdmin::class);

    try {
        $middleware->handle($request, fn () => response('ok'));
    } catch (HttpException $e) {
        // expected
    }

    expect($logged['event'])->toBe(AuditEvent::ADMIN_UNAUTHORIZED_ACCESS);
});

it('returns 403 for super_admin-only users without is_admin set', function () {
    // super_admin alone does not grant isAdmin() — is_admin column is required
    // is_admin defaults to false; super_admin=true tests that super_admin alone does not grant isAdmin()
    $user = User::factory()->create(['super_admin' => true]);

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureIsAdmin::class);

    expect(fn () => $middleware->handle($request, fn () => response('ok')))
        ->toThrow(HttpException::class);
});
