<?php

use Illuminate\Support\Facades\Route;

test('all named web routes survive the routes/web.php split', function () {
    $required = [
        // marketing.php
        'welcome', 'buy', 'legal.terms', 'legal.privacy', 'about',
        'contact.show', 'contact.store', 'contact.sales',
        'changelog', 'changelog.acknowledge',
        'roadmap', 'roadmap.vote',
        'compare.index', 'compare.jetstream', 'compare.spark',
        'compare.saasykit', 'compare.wave', 'compare.shipfast',
        'compare.supastarter', 'compare.larafast', 'compare.nextjs-saas',
        'features.index', 'features.billing', 'features.feature-flags',
        'features.admin-panel', 'features.webhooks',
        'features.two-factor-auth', 'features.social-auth',
        'guides.index', 'guides.laravel-saas', 'guides.stripe-guide',
        'guides.feature-flags-guide', 'guides.saas-starter-kit-comparison',
        'guides.build-vs-buy', 'guides.two-factor', 'guides.webhook',
        'guides.tenancy-architecture',
        'blog.index', 'blog.show',
        'unsubscribe',
        // app.php
        'feedback.store',
        'nps.eligible', 'nps.store',
        'onboarding', 'onboarding.complete',
        'dashboard', 'dashboard.charts',
        'export.users', 'export.personal-data',
        'profile.edit', 'profile.update', 'profile.destroy',
        'settings.security',
        'two-factor.enable', 'two-factor.confirm', 'two-factor.disable',
        'two-factor.recovery-codes', 'two-factor.recovery-codes.regenerate',
        'settings.webhooks',
        // dev.php
        'favicon', 'robots', 'sitemap', 'llms', 'health',
    ];

    foreach ($required as $name) {
        expect(Route::has($name))->toBeTrue("Route '{$name}' missing after web.php split");
    }
});

test('total route count after split is at least 100', function () {
    expect(count(Route::getRoutes()->getRoutes()))->toBeGreaterThanOrEqual(100);
});
