<?php

use Inertia\Testing\AssertableInertia;

it('renders the laravel jetstream comparison page', function () {
    $response = $this->get('/compare/laravel-jetstream');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Compare/LaravelJetstream')
        ->has('features')
        ->where('competitor', 'laravel-jetstream')
        ->where('competitorName', 'Laravel Jetstream')
    );
});

it('renders the laravel spark comparison page', function () {
    $response = $this->get('/compare/laravel-spark');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Compare/LaravelSpark')
        ->has('features')
        ->where('competitor', 'laravel-spark')
        ->where('competitorName', 'Laravel Spark')
    );
});

it('renders the saasykit comparison page', function () {
    $response = $this->get('/compare/saasykit');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Compare/SaaSykit')
        ->has('features')
        ->where('competitor', 'saasykit')
        ->where('competitorName', 'SaaSykit')
    );
});

it('includes all comparison pages in the sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertSee('/compare/laravel-jetstream');
    $response->assertSee('/compare/laravel-spark');
    $response->assertSee('/compare/saasykit');
});
