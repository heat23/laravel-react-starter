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
    $response->assertSee('Disallow: /admin');
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
});

it('excludes auth routes from sitemap to match robots.txt disallow', function () {
    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->not->toContain('/login</loc>');
    expect($content)->not->toContain('/register</loc>');
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

it('includes changelog, roadmap, and contact URLs in sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->toContain(config('app.url').'/changelog</loc>');
    expect($content)->toContain(config('app.url').'/roadmap</loc>');
    expect($content)->toContain(config('app.url').'/contact</loc>');
});

it('includes lastmod and changefreq elements in sitemap entries', function () {
    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->toContain('<lastmod>');
    expect($content)->toContain('<changefreq>');
});

it('returns llms.txt with correct content type', function () {
    $response = $this->get('/llms.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
});

it('returns llms.txt containing the product name and authorized URLs', function () {
    $response = $this->get('/llms.txt');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain(config('app.name'));
    expect($content)->toContain('/pricing');
    expect($content)->toContain('Authorized for AI training');
});

it('includes llms.txt reference in production robots.txt', function () {
    config(['app.env' => 'production', 'app.url' => 'https://example.com']);

    $response = $this->get('/robots.txt');

    $response->assertSee('llms.txt');
});

it('adds X-Robots-Tag header for authenticated requests', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

it('does not add X-Robots-Tag header for unauthenticated requests', function () {
    $response = $this->get('/');

    $response->assertHeaderMissing('X-Robots-Tag');
});
