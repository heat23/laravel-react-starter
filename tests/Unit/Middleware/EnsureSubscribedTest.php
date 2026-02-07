<?php

use App\Http\Middleware\EnsureSubscribed;
use App\Models\User;
use Illuminate\Http\Request;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('allows subscribed user through without tier requirement', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user->fresh());

    $middleware = app(EnsureSubscribed::class);
    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok');
});

it('redirects non-subscribed user to pricing page', function () {
    $user = User::factory()->create();

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureSubscribed::class);
    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getStatusCode())->toBe(302);
});

it('allows trial user through', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'trialing',
        'trial_ends_at' => now()->addDays(14),
    ]);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user->fresh());

    $middleware = app(EnsureSubscribed::class);
    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok');
});

it('blocks user with expired subscription', function () {
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'canceled',
        'ends_at' => now()->subDay(),
    ]);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user->fresh());

    $middleware = app(EnsureSubscribed::class);
    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getStatusCode())->toBe(302);
});

it('returns 403 json for api requests from non-subscribed user', function () {
    $user = User::factory()->create();

    $request = Request::create('/api/test', 'GET');
    $request->headers->set('Accept', 'application/json');
    $request->setUserResolver(fn () => $user);

    $middleware = app(EnsureSubscribed::class);
    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getStatusCode())->toBe(403);
});

it('allows team user through team tier gate', function () {
    $user = User::factory()->create();
    createTeamSubscription($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user->fresh());

    $middleware = app(EnsureSubscribed::class);
    $response = $middleware->handle($request, fn () => response('ok'), 'team');

    expect($response->getContent())->toBe('ok');
});

it('blocks pro user from team tier gate', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user->fresh());

    $middleware = app(EnsureSubscribed::class);
    $response = $middleware->handle($request, fn () => response('ok'), 'team');

    expect($response->getStatusCode())->toBe(302);
});

it('allows enterprise user through team tier gate', function () {
    $user = User::factory()->create();
    createEnterpriseSubscription($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user->fresh());

    $middleware = app(EnsureSubscribed::class);
    $response = $middleware->handle($request, fn () => response('ok'), 'team');

    expect($response->getContent())->toBe('ok');
});
