<?php

it('renders terms page', function () {
    $response = $this->get('/terms');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Public/Legal/Terms'));
});

it('renders privacy page', function () {
    $response = $this->get('/privacy');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Public/Legal/Privacy'));
});

it('includes legal pages in sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('/terms');
    expect($content)->toContain('/privacy');
});
