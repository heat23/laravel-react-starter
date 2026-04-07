<?php

use App\Http\Middleware\CaptureUtmParameters;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\Store;

describe('CaptureUtmParameters middleware', function () {
    afterEach(fn () => Mockery::close());

    $makeRequest = fn (array $query = [], string $method = 'GET'): Request => tap(
        Request::create('/', $method, $query),
        fn (Request $r) => $r->setLaravelSession(Mockery::mock(Store::class))
    );

    $makeResponse = fn (Request $request): Response => (new CaptureUtmParameters)->handle($request, fn () => new Response);

    it('stores first-touch UTM data when session has no utm_data', function () use ($makeRequest, $makeResponse) {
        $request = $makeRequest(['utm_source' => 'google', 'utm_medium' => 'cpc']);
        $session = $request->session();

        $session->shouldReceive('has')->with('utm_data')->once()->andReturn(false);
        $session->shouldReceive('put')->with('utm_data', ['utm_source' => 'google', 'utm_medium' => 'cpc'])->once();
        $session->shouldReceive('put')->with('utm_last_touch', ['utm_source' => 'google', 'utm_medium' => 'cpc'])->once();

        $makeResponse($request);
    });

    it('does not overwrite first-touch utm_data on a second visit with different UTM params', function () use ($makeRequest, $makeResponse) {
        $request = $makeRequest(['utm_source' => 'email', 'utm_campaign' => 'newsletter']);
        $session = $request->session();

        $session->shouldReceive('has')->with('utm_data')->once()->andReturn(true);
        $session->shouldReceive('put')->with('utm_data', Mockery::any())->never();
        $session->shouldReceive('put')->with('utm_last_touch', ['utm_source' => 'email', 'utm_campaign' => 'newsletter'])->once();

        $makeResponse($request);
    });

    it('always updates utm_last_touch on every visit with UTM params', function () use ($makeRequest, $makeResponse) {
        // First visit
        $request1 = $makeRequest(['utm_source' => 'google']);
        $session1 = $request1->session();
        $session1->shouldReceive('has')->with('utm_data')->andReturn(false);
        $session1->shouldReceive('put')->with('utm_data', ['utm_source' => 'google'])->once();
        $session1->shouldReceive('put')->with('utm_last_touch', ['utm_source' => 'google'])->once();
        $makeResponse($request1);

        // Second visit with different UTM
        $request2 = $makeRequest(['utm_source' => 'email', 'utm_medium' => 'newsletter']);
        $session2 = $request2->session();
        $session2->shouldReceive('has')->with('utm_data')->andReturn(true);
        $session2->shouldReceive('put')->with('utm_data', Mockery::any())->never();
        $session2->shouldReceive('put')->with('utm_last_touch', ['utm_source' => 'email', 'utm_medium' => 'newsletter'])->once();
        $makeResponse($request2);
    });

    it('does not store any UTM data when no UTM params are present', function () use ($makeRequest, $makeResponse) {
        $request = $makeRequest(['foo' => 'bar']);
        $session = $request->session();

        $session->shouldReceive('has')->never();
        $session->shouldReceive('put')->never();

        $makeResponse($request);
    });

    it('ignores empty string UTM parameter values', function () use ($makeRequest, $makeResponse) {
        $request = $makeRequest(['utm_source' => '', 'utm_medium' => 'cpc']);
        $session = $request->session();

        $session->shouldReceive('has')->with('utm_data')->once()->andReturn(false);
        $session->shouldReceive('put')->with('utm_data', ['utm_medium' => 'cpc'])->once();
        $session->shouldReceive('put')->with('utm_last_touch', ['utm_medium' => 'cpc'])->once();

        $makeResponse($request);
    });

    it('does not capture UTM params on non-GET requests', function () use ($makeRequest, $makeResponse) {
        $request = $makeRequest(['utm_source' => 'google'], 'POST');
        $session = $request->session();

        $session->shouldReceive('has')->never();
        $session->shouldReceive('put')->never();

        $makeResponse($request);
    });
});
