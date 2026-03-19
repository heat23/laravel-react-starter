<?php

use Inertia\Testing\AssertableInertia;

it('renders the billing feature page for guests', function () {
    $response = $this->get('/features/billing');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Features/Billing')
        ->has('title')
        ->has('metaDescription')
    );
});

it('renders the feature flags page for guests', function () {
    $response = $this->get('/features/feature-flags');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Features/FeatureFlags')
        ->has('title')
        ->has('metaDescription')
    );
});

it('renders the admin panel feature page for guests', function () {
    $response = $this->get('/features/admin-panel');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Features/AdminPanel')
        ->has('title')
        ->has('metaDescription')
    );
});

it('includes all feature pages in the sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertSee('/features/billing');
    $response->assertSee('/features/feature-flags');
    $response->assertSee('/features/admin-panel');
});
