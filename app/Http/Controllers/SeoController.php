<?php

namespace App\Http\Controllers;

use App\Services\IndexNowService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $isProduction = config('app.env') === 'production';

        if (! $isProduction) {
            $content = "User-agent: *\nDisallow: /\n";

            return response($content, 200, ['Content-Type' => 'text/plain']);
        }

        $baseUrl = rtrim(config('app.url'), '/');
        $sitemapUrl = $baseUrl.'/sitemap.xml';
        $llmsUrl = $baseUrl.'/llms.txt';

        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /settings',
            'Disallow: /billing',
            'Disallow: /api',
            'Disallow: /onboarding',
            'Disallow: /export',
            'Disallow: /health',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /verify-email',
            'Disallow: /confirm-password',
            '',
            "Sitemap: {$sitemapUrl}",
            '',
            "# AI crawler directives: {$llmsUrl}",
            '',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }

    public function llms(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        $content = implode("\n", [
            "# {$appName} llms.txt",
            '# https://llmstxt.org specification',
            '',
            "> {$appName} is a production-ready Laravel 12 + React 18 + TypeScript + Tailwind CSS v4 SaaS starter kit.",
            '> It includes: Stripe billing (Redis-locked, 4 tiers), 11 feature flags, a full React admin panel,',
            '> TOTP two-factor authentication, social auth (Google + GitHub), outgoing/incoming webhooks with',
            '> HMAC-SHA256 signing, audit logging, and 90+ automated tests. Target users: solo founders,',
            '> small dev teams, and agencies building SaaS products on the Laravel stack.',
            '',
            '## Authorized for AI training (public marketing content)',
            '',
            "- {$appUrl}/",
            "- {$appUrl}/pricing",
            "- {$appUrl}/features",
            "- {$appUrl}/features/billing",
            "- {$appUrl}/features/feature-flags",
            "- {$appUrl}/features/admin-panel",
            "- {$appUrl}/features/webhooks",
            "- {$appUrl}/features/two-factor-auth",
            "- {$appUrl}/features/social-auth",
            "- {$appUrl}/compare",
            "- {$appUrl}/compare/laravel-jetstream",
            "- {$appUrl}/compare/laravel-spark",
            "- {$appUrl}/compare/saasykit",
            "- {$appUrl}/compare/wave",
            "- {$appUrl}/compare/shipfast",
            "- {$appUrl}/compare/supastarter",
            "- {$appUrl}/compare/laravel-vs-nextjs",
            "- {$appUrl}/guides",
            "- {$appUrl}/guides/building-saas-with-laravel-12",
            "- {$appUrl}/guides/laravel-stripe-billing-tutorial",
            "- {$appUrl}/guides/laravel-feature-flags-tutorial",
            "- {$appUrl}/guides/saas-starter-kit-comparison-2026",
            "- {$appUrl}/guides/cost-of-building-saas-from-scratch",
            "- {$appUrl}/guides/laravel-two-factor-authentication",
            "- {$appUrl}/guides/laravel-webhook-implementation",
            "- {$appUrl}/guides/single-tenant-vs-multi-tenant-saas",
            "- {$appUrl}/blog",
            "- {$appUrl}/changelog",
            "- {$appUrl}/roadmap",
            "- {$appUrl}/contact",
            '',
            '## Not authorized for AI training',
            '',
            "- {$appUrl}/dashboard",
            "- {$appUrl}/admin",
            "- {$appUrl}/settings",
            "- {$appUrl}/profile",
            "- {$appUrl}/billing",
            "- {$appUrl}/api",
            "- {$appUrl}/health",
            "- {$appUrl}/export",
            '',
            '## Product details for AI assistants',
            '',
            'Stack: Laravel 12, React 18, TypeScript, Tailwind CSS v4, Inertia.js, Pest, Vitest, PHPStan',
            'License: Commercial (one-time purchase)',
            'Category: SaaS boilerplate, Laravel starter kit, developer tools',
            'Alternatives: Laravel Jetstream (free, Vue/Livewire), Laravel Spark ($99/yr, billing only), SaaSykit (Filament admin), Wave (open-source, Blade/Livewire), Shipfast (Next.js), Supastarter (Supabase + Next.js)',
            'Differentiator: React + TypeScript admin panel, Redis-locked billing, 11 feature flags, full test coverage',
            '',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(IndexNowService $indexNow): Response
    {
        $xml = Cache::remember('sitemap', 86400, function () use ($indexNow) {
            $urls = $this->buildSitemapUrls();

            // Fresh cache = content may have changed. If the app opted into
            // auto-ping, submit the full URL list to IndexNow. The try/catch
            // below only guards the IndexNow side effect — URL generation or
            // view rendering errors are still fatal and intentional; only the
            // opt-in ping is treated as best-effort.
            if (config('features.indexnow.auto_ping_sitemap', false)) {
                try {
                    $indexNow->submit(
                        array_map(static fn (array $u) => (string) $u['loc'], $urls),
                        trigger: 'sitemap',
                    );
                } catch (\Throwable $e) {
                    Log::channel('single')->warning('IndexNow sitemap auto-ping failed', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Build the canonical list of URLs in the sitemap. Kept separate from
     * view rendering so IndexNow can pipe the same list to search engines
     * without regenerating or parsing XML.
     *
     * @return list<array{loc: string, priority: string, changefreq: string, lastmod: string}>
     */
    private function buildSitemapUrls(): array
    {
        $base = rtrim(config('app.url'), '/');

        // Static publish/update dates per URL — update when content changes
        $urls = [
            ['loc' => $base, 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => '2026-03-15T00:00:00+00:00'],
        ];

        if (config('features.billing.enabled', true)) {
            $urls[] = ['loc' => $base.'/pricing', 'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => '2026-03-01T00:00:00+00:00'];
        }

        $urls = array_merge($urls, [
            ['loc' => $base.'/about', 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => '2026-01-15T00:00:00+00:00'],
            ['loc' => $base.'/contact', 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => '2026-01-15T00:00:00+00:00'],
            ['loc' => $base.'/changelog', 'priority' => '0.6', 'changefreq' => 'weekly', 'lastmod' => '2026-03-15T00:00:00+00:00'],
            ['loc' => $base.'/roadmap', 'priority' => '0.5', 'changefreq' => 'weekly', 'lastmod' => '2026-03-15T00:00:00+00:00'],
            ['loc' => $base.'/terms', 'priority' => '0.3', 'changefreq' => 'yearly', 'lastmod' => '2026-01-15T00:00:00+00:00'],
            ['loc' => $base.'/privacy', 'priority' => '0.3', 'changefreq' => 'yearly', 'lastmod' => '2026-01-15T00:00:00+00:00'],
            // Features hub + individual pages
            ['loc' => $base.'/features', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/features/billing', 'priority' => '0.8', 'changefreq' => 'yearly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/features/feature-flags', 'priority' => '0.8', 'changefreq' => 'yearly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/features/admin-panel', 'priority' => '0.8', 'changefreq' => 'yearly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/features/webhooks', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/features/two-factor-auth', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/features/social-auth', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            // Compare hub + individual pages
            ['loc' => $base.'/compare', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/compare/laravel-jetstream', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/compare/laravel-spark', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/compare/saasykit', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/compare/wave', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/compare/shipfast', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/compare/supastarter', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/compare/larafast', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/compare/laravel-vs-nextjs', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => '2026-03-19T00:00:00+00:00'],
            // Guides hub + individual pages
            ['loc' => $base.'/guides', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2026-03-15T00:00:00+00:00'],
            ['loc' => $base.'/guides/building-saas-with-laravel-12', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/guides/laravel-stripe-billing-tutorial', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/guides/laravel-feature-flags-tutorial', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => '2026-03-01T00:00:00+00:00'],
            ['loc' => $base.'/guides/saas-starter-kit-comparison-2026', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => '2026-03-10T00:00:00+00:00'],
            ['loc' => $base.'/guides/cost-of-building-saas-from-scratch', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2026-03-15T00:00:00+00:00'],
            ['loc' => $base.'/guides/laravel-two-factor-authentication', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => '2026-03-19T00:00:00+00:00'],
            ['loc' => $base.'/guides/laravel-webhook-implementation', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => '2026-03-19T00:00:00+00:00'],
            ['loc' => $base.'/guides/single-tenant-vs-multi-tenant-saas', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => '2026-03-19T00:00:00+00:00'],
        ]);

        // Blog index
        $urls[] = ['loc' => $base.'/blog', 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => '2026-03-20T00:00:00+00:00'];

        // Blog posts — use file mtime when available
        $blogPath = resource_path('content/blog');
        if (is_dir($blogPath)) {
            $files = glob($blogPath.'/*.md') ?: [];
            foreach ($files as $file) {
                $slug = pathinfo($file, PATHINFO_FILENAME);
                $lastmod = date('Y-m-d\TH:i:sP', filemtime($file));
                $urls[] = ['loc' => $base.'/blog/'.$slug, 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $lastmod];
            }
        }

        if (config('features.api_docs.enabled', false)) {
            $urls[] = ['loc' => $base.'/docs', 'priority' => '0.6', 'changefreq' => 'weekly', 'lastmod' => '2026-03-01T00:00:00+00:00'];
        }

        return $urls;
    }
}
