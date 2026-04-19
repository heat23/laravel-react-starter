<?php

use App\Models\User;

dataset('publicRoutesWithTitle', [
    'homepage' => ['/'],
    'features' => ['/features'],
    'features-billing' => ['/features/billing'],
    'compare' => ['/compare'],
    'compare-jetstream' => ['/compare/laravel-jetstream'],
    'guides' => ['/guides'],
    'about' => ['/about'],
    'contact' => ['/contact'],
    'changelog' => ['/changelog'],
    'roadmap' => ['/roadmap'],
    'blog' => ['/blog'],
    'terms' => ['/terms'],
    'privacy' => ['/privacy'],
]);

it('renders seo-shell for guest visitors', function (string $url) {
    $response = $this->get($url);

    $response->assertOk();
    expect($response->getContent())->toContain('id="seo-shell"');
})->with('publicRoutesWithTitle');

it('seo-shell contains at least 5 internal navigation links', function (string $url) {
    $response = $this->get($url);
    $content = $response->getContent();
    $appUrl = rtrim(config('app.url'), '/');

    preg_match_all('/<a href="'.preg_quote($appUrl, '/').'/i', $content, $matches);

    expect(count($matches[0]))->toBeGreaterThanOrEqual(5);
})->with('publicRoutesWithTitle');

it('seo-shell contains link to homepage', function (string $url) {
    $response = $this->get($url);
    $appUrl = rtrim(config('app.url'), '/');

    expect($response->getContent())->toContain('href="'.$appUrl.'/"');
})->with('publicRoutesWithTitle');

it('seo-shell contains links to core sections', function (string $url) {
    $content = $this->get($url)->getContent();
    $appUrl = rtrim(config('app.url'), '/');

    expect($content)
        ->toContain('href="'.$appUrl.'/features"')
        ->toContain('href="'.$appUrl.'/compare"')
        ->toContain('href="'.$appUrl.'/contact"');
})->with('publicRoutesWithTitle');

it('does not render seo-shell for authenticated users', function () {
    $user = User::factory()->create();
    $content = $this->actingAs($user)->get('/')->getContent();

    expect($content)->not->toContain('id="seo-shell"');
});

it('seo-shell contains H1 with page title on pages that pass title prop', function () {
    $response = $this->get('/features');
    $content = $response->getContent();

    expect($content)
        ->toContain('id="seo-shell"')
        ->toContain('<h1>');
});

it('seo-shell contains H1 on compare pages that pass title prop', function () {
    $response = $this->get('/compare');
    $content = $response->getContent();

    expect($content)
        ->toContain('id="seo-shell"')
        ->toContain('<h1>');
});

it('seo-shell contains breadcrumb nav on pages that pass breadcrumbs', function () {
    $response = $this->get('/features/billing');
    $content = $response->getContent();

    expect($content)->toContain('aria-label="Breadcrumb"');
});

it('pricing page seo-shell renders for guests when billing is enabled', function () {
    // Route registration is boot-time; phpunit.xml must have FEATURE_BILLING=true for this test to run.
    // If the route returns 404, billing is disabled in phpunit.xml and this test is a no-op.
    $response = $this->get('/pricing');

    if ($response->status() === 404) {
        $this->markTestSkipped('Billing feature disabled in phpunit.xml — /pricing route not registered.');
    }

    $response->assertOk();
    expect($response->getContent())->toContain('id="seo-shell"');
});
