<?php

namespace App\Http\Routing;

use Illuminate\Support\Facades\Route;

/**
 * Single source of truth for all static public (unauthenticated) routes.
 *
 * Consumers:
 *   - SeoController::buildSitemapUrls() — builds sitemap.xml
 *   - TitleLengthTest — dataset of routes with server-side Inertia title props
 *   - SeoShellRendersContentTest — dataset of representative SEO-shell routes
 *   - JsonLdValidityTest — dataset of routes that should have valid JSON-LD
 *
 * To add a new public route: add an entry here. The route must have a named
 * route registered in routes/marketing.php. Set hasInertiaTitle=true if the
 * controller passes a `title` Inertia prop (required for TitleLengthTest).
 * Blog posts are excluded — they are discovered dynamically from content/blog/.
 */
class PublicRouteRegistry
{
    /**
     * All static public routes with SEO metadata.
     * Filters to registered routes only; billing-gated routes are excluded when
     * features.billing.enabled is false. Uses Route::has() — only call during a
     * request, not during test collection.
     *
     * @return list<array{name: string, path: string, priority: string, changefreq: string, lastmod: string, hasInertiaTitle: bool}>
     */
    public static function all(): array
    {
        $billingEnabled = (bool) config('features.billing.enabled', false);

        return array_values(array_filter(
            array_map(
                static function (array $e) use ($billingEnabled): ?array {
                    if (! Route::has($e['name'])) {
                        return null;
                    }
                    if ($e['billingGated'] && ! $billingEnabled) {
                        return null;
                    }

                    return array_diff_key($e, ['billingGated' => true]);
                },
                self::rawEntries()
            )
        ));
    }

    /**
     * Routes that pass the `title` Inertia prop and can be tested server-side.
     * Safe to call during Pest dataset collection — no facade dependencies.
     * Returns a Pest dataset array: ['label' => ['/path']].
     *
     * @return array<string, array{0: string}>
     */
    public static function withInertiaTitle(): array
    {
        return collect(self::rawEntries())
            ->filter(fn (array $entry): bool => $entry['hasInertiaTitle'])
            ->mapWithKeys(fn (array $entry): array => [$entry['name'] => [$entry['path']]])
            ->all();
    }

    /**
     * Representative public routes for SEO shell coverage testing.
     * A subset of rawEntries() — enough to verify shell mechanics without redundant assertions.
     * Safe to call during Pest dataset collection — no facade dependencies.
     * Returns a Pest dataset array: ['label' => ['/path']].
     *
     * @return array<string, array{0: string}>
     */
    public static function forSeoShell(): array
    {
        $representativePaths = [
            '/', '/features', '/features/billing', '/compare', '/compare/laravel-jetstream',
            '/guides', '/about', '/contact', '/changelog', '/roadmap', '/blog', '/terms', '/privacy',
        ];

        return collect(self::rawEntries())
            ->filter(fn (array $entry): bool => in_array($entry['path'], $representativePaths, true))
            ->mapWithKeys(fn (array $entry): array => [$entry['name'] => [$entry['path']]])
            ->all();
    }

    /**
     * Routes where JSON-LD blocks should be valid.
     * Subset limited to pages with richer structured data.
     * Safe to call during Pest dataset collection — no facade dependencies.
     * Returns a Pest dataset array: ['label' => ['/path']].
     *
     * @return array<string, array{0: string}>
     */
    public static function withJsonLd(): array
    {
        $jsonLdPaths = [
            '/', '/features', '/compare', '/guides',
            '/features/billing', '/compare/laravel-jetstream', '/guides/building-saas-with-laravel-12',
        ];

        return collect(self::rawEntries())
            ->filter(fn (array $entry): bool => in_array($entry['path'], $jsonLdPaths, true))
            ->mapWithKeys(fn (array $entry): array => [$entry['name'] => [$entry['path']]])
            ->all();
    }

    /**
     * Raw list of all route entries without Route::has() filtering.
     * Safe to call at any time — no facade dependencies.
     *
     * @return list<array{name: string, path: string, priority: string, changefreq: string, lastmod: string, hasInertiaTitle: bool, billingGated: bool}>
     */
    private static function rawEntries(): array
    {
        return [
            // Core pages
            self::raw('welcome', '/', '1.0', 'weekly', '2026-03-15', false),
            self::raw('about', '/about', '0.5', 'monthly', '2026-01-15', false),
            self::raw('contact.show', '/contact', '0.4', 'yearly', '2026-01-15', false),
            self::raw('changelog', '/changelog', '0.6', 'weekly', '2026-03-15', false),
            self::raw('roadmap', '/roadmap', '0.5', 'weekly', '2026-03-15', false),
            self::raw('legal.terms', '/terms', '0.3', 'yearly', '2026-01-15', false),
            self::raw('legal.privacy', '/privacy', '0.3', 'yearly', '2026-01-15', false),
            self::raw('blog.index', '/blog', '0.7', 'weekly', '2026-03-20', true),
            // Billing-gated — excluded from sitemap when features.billing.enabled is false
            self::raw('buy', '/pricing', '0.9', 'monthly', '2026-03-01', false, billingGated: true),
            // Features hub + individual pages
            self::raw('features.index', '/features', '0.8', 'monthly', '2026-03-01', true),
            self::raw('features.billing', '/features/billing', '0.8', 'yearly', '2026-03-01', true),
            self::raw('features.feature-flags', '/features/feature-flags', '0.8', 'yearly', '2026-03-01', true),
            self::raw('features.admin-panel', '/features/admin-panel', '0.8', 'yearly', '2026-03-01', true),
            self::raw('features.webhooks', '/features/webhooks', '0.7', 'yearly', '2026-03-01', true),
            self::raw('features.two-factor-auth', '/features/two-factor-auth', '0.7', 'yearly', '2026-03-01', true),
            self::raw('features.social-auth', '/features/social-auth', '0.7', 'yearly', '2026-03-01', true),
            // Compare hub + individual pages
            self::raw('compare.index', '/compare', '0.8', 'monthly', '2026-03-10', true),
            self::raw('compare.jetstream', '/compare/laravel-jetstream', '0.7', 'yearly', '2026-03-10', true),
            self::raw('compare.spark', '/compare/laravel-spark', '0.7', 'yearly', '2026-03-10', true),
            self::raw('compare.saasykit', '/compare/saasykit', '0.7', 'yearly', '2026-03-10', true),
            self::raw('compare.wave', '/compare/wave', '0.7', 'yearly', '2026-03-10', true),
            self::raw('compare.shipfast', '/compare/shipfast', '0.7', 'yearly', '2026-03-10', true),
            self::raw('compare.supastarter', '/compare/supastarter', '0.7', 'yearly', '2026-03-10', true),
            self::raw('compare.larafast', '/compare/larafast', '0.7', 'yearly', '2026-03-10', true),
            self::raw('compare.nextjs-saas', '/compare/laravel-vs-nextjs', '0.7', 'yearly', '2026-03-19', true),
            self::raw('compare.makerkit', '/compare/makerkit', '0.7', 'yearly', '2026-03-19', true),
            // Guides hub + individual pages
            self::raw('guides.index', '/guides', '0.8', 'monthly', '2026-03-15', true),
            self::raw('guides.laravel-saas', '/guides/building-saas-with-laravel-12', '0.8', 'monthly', '2026-03-01', true),
            self::raw('guides.stripe-guide', '/guides/laravel-stripe-billing-tutorial', '0.7', 'monthly', '2026-03-01', true),
            self::raw('guides.feature-flags-guide', '/guides/laravel-feature-flags-tutorial', '0.7', 'monthly', '2026-03-01', true),
            self::raw('guides.saas-starter-kit-comparison', '/guides/saas-starter-kit-comparison-2026', '0.7', 'monthly', '2026-03-10', true),
            self::raw('guides.build-vs-buy', '/guides/cost-of-building-saas-from-scratch', '0.8', 'monthly', '2026-03-15', true),
            self::raw('guides.two-factor', '/guides/laravel-two-factor-authentication', '0.7', 'monthly', '2026-03-19', true),
            self::raw('guides.webhook', '/guides/laravel-webhook-implementation', '0.7', 'monthly', '2026-03-19', true),
            self::raw('guides.tenancy-architecture', '/guides/single-tenant-vs-multi-tenant-saas', '0.7', 'monthly', '2026-03-19', true),
        ];
    }

    /**
     * @return array{name: string, path: string, priority: string, changefreq: string, lastmod: string, hasInertiaTitle: bool, billingGated: bool}
     */
    private static function raw(
        string $name,
        string $path,
        string $priority,
        string $changefreq,
        string $lastmod,
        bool $hasInertiaTitle,
        bool $billingGated = false,
    ): array {
        return [
            'name' => $name,
            'path' => $path,
            'priority' => $priority,
            'changefreq' => $changefreq,
            'lastmod' => $lastmod.'T00:00:00+00:00',
            'hasInertiaTitle' => $hasInertiaTitle,
            'billingGated' => $billingGated,
        ];
    }
}
