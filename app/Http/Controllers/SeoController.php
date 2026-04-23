<?php

namespace App\Http\Controllers;

use App\Http\Routing\PublicRouteRegistry;
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

        $urls = collect(PublicRouteRegistry::all())
            ->map(fn (array $entry): array => [
                'loc' => rtrim($base.$entry['path'], '/'),
                'priority' => $entry['priority'],
                'changefreq' => $entry['changefreq'],
                'lastmod' => $entry['lastmod'],
            ])
            ->values()
            ->all();

        // Blog posts — dynamically discovered from content/blog/ (not in registry)
        $blogPath = resource_path('content/blog');
        if (is_dir($blogPath)) {
            $files = glob($blogPath.'/*.md') ?: [];
            foreach ($files as $file) {
                $slug = pathinfo($file, PATHINFO_FILENAME);
                $lastmod = date('Y-m-d\TH:i:sP', filemtime($file));
                $urls[] = ['loc' => $base.'/blog/'.$slug, 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $lastmod];
            }
        }

        return $urls;
    }
}
