<?php

it('returns Disallow: / in robots.txt for non-production environment', function () {
    config(['app.env' => 'local']);

    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $response->assertSee('Disallow: /');
    $response->assertDontSee('Allow: /');
});

it('returns Allow: / in robots.txt for production environment', function () {
    config(['app.env' => 'production']);

    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $response->assertSee('Allow: /');
    $response->assertSee('Disallow: /dashboard');
    $response->assertSee('Disallow: /profile');
    $response->assertSee('Disallow: /settings');
    $response->assertSee('Disallow: /api');
    $response->assertSee('Sitemap:');
});

it('includes sitemap directive in production robots.txt', function () {
    config(['app.env' => 'production', 'app.url' => 'https://example.com']);

    $response = $this->get('/robots.txt');

    $response->assertSee('Sitemap: https://example.com/sitemap.xml');
});

it('returns valid XML sitemap with expected URLs', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');

    $content = $response->getContent();
    expect($content)->toContain('<urlset');
    expect($content)->toContain('<loc>'.config('app.url').'</loc>');
    expect($content)->toContain('<loc>'.config('app.url').'/login</loc>');
    expect($content)->toContain('<loc>'.config('app.url').'/register</loc>');
});

it('includes pricing URL in sitemap when billing enabled', function () {
    config(['features.billing.enabled' => true]);

    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->toContain('<loc>'.config('app.url').'/pricing</loc>');
});

it('excludes pricing URL from sitemap when billing disabled', function () {
    config(['features.billing.enabled' => false]);

    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->not->toContain('/pricing</loc>');
});

it('includes docs URL in sitemap when api docs enabled', function () {
    config(['features.api_docs.enabled' => true]);

    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->toContain('<loc>'.config('app.url').'/docs</loc>');
});

it('excludes docs URL from sitemap when api docs disabled', function () {
    config(['features.api_docs.enabled' => false]);

    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->not->toContain('/docs</loc>');
});
