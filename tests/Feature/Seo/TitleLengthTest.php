<?php

use Inertia\Testing\AssertableInertia;

/**
 * Routes that pass `title` as an Inertia prop — testable server-side.
 * Google truncates <title> tags beyond ~60 display pixels (~70 chars).
 * Routes without a `title` prop set their title in React <Head>; those
 * require a browser/SSR render and are not covered here.
 */
dataset('routesWithTitleProps', [
    'features index' => ['/features'],
    'features billing' => ['/features/billing'],
    'features feature-flags' => ['/features/feature-flags'],
    'features admin-panel' => ['/features/admin-panel'],
    'features webhooks' => ['/features/webhooks'],
    'features two-factor-auth' => ['/features/two-factor-auth'],
    'features social-auth' => ['/features/social-auth'],
    'compare index' => ['/compare'],
    'compare jetstream' => ['/compare/laravel-jetstream'],
    'compare spark' => ['/compare/laravel-spark'],
    'compare saasykit' => ['/compare/saasykit'],
    'compare wave' => ['/compare/wave'],
    'compare shipfast' => ['/compare/shipfast'],
    'compare supastarter' => ['/compare/supastarter'],
    'compare larafast' => ['/compare/larafast'],
    'compare nextjs' => ['/compare/laravel-vs-nextjs'],
    'guides index' => ['/guides'],
    'guides building-saas' => ['/guides/building-saas-with-laravel-12'],
    'guides stripe-billing' => ['/guides/laravel-stripe-billing-tutorial'],
    'guides feature-flags' => ['/guides/laravel-feature-flags-tutorial'],
    'guides comparison-2026' => ['/guides/saas-starter-kit-comparison-2026'],
    'guides cost' => ['/guides/cost-of-building-saas-from-scratch'],
    'guides two-factor' => ['/guides/laravel-two-factor-authentication'],
    'guides webhooks' => ['/guides/laravel-webhook-implementation'],
    'guides single-tenant' => ['/guides/single-tenant-vs-multi-tenant-saas'],
    'blog index' => ['/blog'],
]);

it('page title prop does not exceed 70 characters', function (string $url) {
    $capturedTitle = null;

    $this->get($url)->assertOk()->assertInertia(
        fn (AssertableInertia $page) => $page->where('title', function ($title) use (&$capturedTitle) {
            // No string type hint — handles null without TypeError.
            // Pest exceptions thrown inside where() propagate and fail the test correctly.
            expect($title)->not->toBeNull('Title prop is null');
            $capturedTitle = (string) $title;

            return true;
        })
    );

    $length = mb_strlen($capturedTitle ?? '');
    expect($length)->toBeLessThanOrEqual(
        70,
        "Title for {$url} is {$length} chars (max 70): \"{$capturedTitle}\""
    );
})->with('routesWithTitleProps');
