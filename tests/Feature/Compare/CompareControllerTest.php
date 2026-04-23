<?php

use Inertia\Testing\AssertableInertia;

it('renders the laravel jetstream comparison page', function () {
    $response = $this->get('/compare/laravel-jetstream');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Public/Compare/LaravelJetstream')
        ->has('features')
        ->where('competitor', 'laravel-jetstream')
        ->where('competitorName', 'Laravel Jetstream')
    );
});

it('renders the laravel spark comparison page', function () {
    $response = $this->get('/compare/laravel-spark');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Public/Compare/LaravelSpark')
        ->has('features')
        ->where('competitor', 'laravel-spark')
        ->where('competitorName', 'Laravel Spark')
    );
});

it('renders the saasykit comparison page', function () {
    $response = $this->get('/compare/saasykit');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Public/Compare/SaaSykit')
        ->has('features')
        ->where('competitor', 'saasykit')
        ->where('competitorName', 'SaaSykit')
    );
});

it('renders the wave comparison page', function () {
    $response = $this->get('/compare/wave');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Public/Compare/Wave')
        ->has('features')
        ->where('competitor', 'wave')
        ->where('competitorName', 'Wave')
    );
});

it('renders the shipfast comparison page', function () {
    $response = $this->get('/compare/shipfast');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Public/Compare/Shipfast')
        ->has('features')
        ->where('competitor', 'shipfast')
        ->where('competitorName', 'Shipfast')
    );
});

it('renders the supastarter comparison page', function () {
    $response = $this->get('/compare/supastarter');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Public/Compare/Supastarter')
        ->has('features')
        ->where('competitor', 'supastarter')
        ->where('competitorName', 'Supastarter')
    );
});

it('renders the larafast comparison page', function () {
    $response = $this->get('/compare/larafast');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Public/Compare/Larafast')
        ->has('features')
        ->where('competitor', 'larafast')
        ->where('competitorName', 'Larafast')
        ->where('canonicalUrl', config('app.url').'/compare/larafast')
        ->where('appUrl', config('app.url'))
    );
});

it('includes all comparison pages in the sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertSee('/compare/laravel-jetstream');
    $response->assertSee('/compare/laravel-spark');
    $response->assertSee('/compare/saasykit');
    $response->assertSee('/compare/wave');
    $response->assertSee('/compare/shipfast');
    $response->assertSee('/compare/supastarter');
});
