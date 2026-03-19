<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;

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
            "- {$appUrl}/features/billing",
            "- {$appUrl}/features/feature-flags",
            "- {$appUrl}/features/admin-panel",
            "- {$appUrl}/compare/laravel-jetstream",
            "- {$appUrl}/compare/laravel-spark",
            "- {$appUrl}/compare/saasykit",
            "- {$appUrl}/guides/building-saas-with-laravel-12",
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
            'Alternatives: Laravel Jetstream (free, Vue/Livewire), Laravel Spark ($99/yr, billing only), SaaSykit (Filament admin)',
            'Differentiator: React + TypeScript admin panel, Redis-locked billing, 11 feature flags, full test coverage',
            '',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $now = Carbon::now()->toAtomString();

        $urls = [
            ['loc' => config('app.url'), 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => config('app.url').'/about', 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => config('app.url').'/contact', 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => $now],
            ['loc' => config('app.url').'/changelog', 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => config('app.url').'/roadmap', 'priority' => '0.5', 'changefreq' => 'weekly', 'lastmod' => $now],
            ['loc' => config('app.url').'/terms', 'priority' => '0.3', 'changefreq' => 'yearly', 'lastmod' => $now],
            ['loc' => config('app.url').'/privacy', 'priority' => '0.3', 'changefreq' => 'yearly', 'lastmod' => $now],
        ];

        if (config('features.billing.enabled', false)) {
            $urls[] = ['loc' => config('app.url').'/pricing', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now];
        }

        if (config('features.api_docs.enabled', false)) {
            $urls[] = ['loc' => config('app.url').'/docs', 'priority' => '0.6', 'changefreq' => 'weekly', 'lastmod' => $now];
        }

        // Feature landing pages
        $urls[] = ['loc' => config('app.url').'/features/billing', 'priority' => '0.8', 'changefreq' => 'yearly', 'lastmod' => $now];
        $urls[] = ['loc' => config('app.url').'/features/feature-flags', 'priority' => '0.8', 'changefreq' => 'yearly', 'lastmod' => $now];
        $urls[] = ['loc' => config('app.url').'/features/admin-panel', 'priority' => '0.8', 'changefreq' => 'yearly', 'lastmod' => $now];

        // Guides (pillar content)
        $urls[] = ['loc' => config('app.url').'/guides/building-saas-with-laravel-12', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $now];

        // Competitor comparison pages
        $urls[] = ['loc' => config('app.url').'/compare/laravel-jetstream', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => $now];
        $urls[] = ['loc' => config('app.url').'/compare/laravel-spark', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => $now];
        $urls[] = ['loc' => config('app.url').'/compare/saasykit', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => $now];

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
