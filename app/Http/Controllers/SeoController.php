<?php

namespace App\Http\Controllers;

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
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /settings',
            'Disallow: /api',
            'Disallow: /onboarding',
            'Disallow: /export',
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
        $urls = [
            ['loc' => config('app.url'), 'priority' => '1.0'],
            ['loc' => config('app.url').'/login', 'priority' => '0.5'],
            ['loc' => config('app.url').'/register', 'priority' => '0.5'],
        ];

        if (config('features.billing.enabled', false)) {
            $urls[] = ['loc' => config('app.url').'/pricing', 'priority' => '0.7'];
        }

        if (config('features.api_docs.enabled', false)) {
            $urls[] = ['loc' => config('app.url').'/docs', 'priority' => '0.6'];
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
