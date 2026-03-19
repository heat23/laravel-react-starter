<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

// Using /guides/ namespace for long-form pillar content, separate from shorter blog posts.
// If a multi-article blog is added later, use a BlogController with /blog/ routes
// and consider Option B (Markdown files parsed by league/commonmark) for content delivery.
class GuidesController extends Controller
{
    public function laravelSaasGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/LaravelSaasGuide', [
            'title' => 'Complete Guide to Building a SaaS with Laravel 12 in 2026',
            'metaDescription' => 'Everything you need to ship a production Laravel 12 SaaS: auth, billing, admin panel, feature flags, webhooks, and testing — with code examples and a working starter kit.',
            'appName' => $appName,
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides/building-saas-with-laravel-12'],
                ['name' => 'Building a SaaS with Laravel 12', 'url' => $appUrl.'/guides/building-saas-with-laravel-12'],
            ],
        ]);
    }
}
