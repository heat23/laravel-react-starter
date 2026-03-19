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

        $sitemapUrl = rtrim(config('app.url'), '/').'/sitemap.xml';

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

        // Competitor comparison pages
        $urls[] = ['loc' => config('app.url').'/compare/laravel-jetstream', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => $now];
        $urls[] = ['loc' => config('app.url').'/compare/laravel-spark', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => $now];
        $urls[] = ['loc' => config('app.url').'/compare/saasykit', 'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => $now];

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
