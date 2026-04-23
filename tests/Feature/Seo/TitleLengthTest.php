<?php

use App\Http\Routing\PublicRouteRegistry;
use Inertia\Testing\AssertableInertia;

/**
 * Routes that pass `title` as an Inertia prop — testable server-side.
 * Google truncates <title> tags beyond ~60 display pixels (~70 chars).
 * Routes without a `title` prop set their title in React <Head>; those
 * require a browser/SSR render and are not covered here.
 *
 * Source of truth: PublicRouteRegistry::withInertiaTitle().
 * To add a route to this test, add it to PublicRouteRegistry with hasInertiaTitle=true.
 */
dataset('routesWithTitleProps', fn () => PublicRouteRegistry::withInertiaTitle());

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
