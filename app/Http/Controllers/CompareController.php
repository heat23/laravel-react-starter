<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class CompareController extends Controller
{
    public function jetstream(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/LaravelJetstream', [
            'competitor' => 'laravel-jetstream',
            'competitorName' => 'Laravel Jetstream',
            'title' => 'Laravel React Starter vs Jetstream — Side-by-Side Comparison',
            'metaDescription' => 'Jetstream uses Vue or Livewire. This starter ships React + TypeScript out of the box. Compare features, billing, admin panel, and production readiness.',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare/laravel-jetstream'],
                ['name' => 'Laravel Jetstream', 'url' => $appUrl.'/compare/laravel-jetstream'],
            ],
            'features' => [
                ['feature' => 'Frontend framework', 'us' => 'React 18 + TypeScript', 'them' => 'Vue 3 or Livewire'],
                ['feature' => 'Admin panel', 'us' => true, 'them' => false],
                ['feature' => 'Stripe billing', 'us' => true, 'them' => false],
                ['feature' => 'Feature flags', 'us' => true, 'them' => false],
                ['feature' => 'Webhooks (in + out)', 'us' => true, 'them' => false],
                ['feature' => 'API token UI', 'us' => true, 'them' => true],
                ['feature' => 'Two-factor auth', 'us' => true, 'them' => true],
                ['feature' => 'Team management', 'us' => 'Via team plan seats', 'them' => 'Yes (Teams feature)'],
                ['feature' => 'Test coverage', 'us' => '90+ tests, PHPStan, Vitest', 'them' => 'Basic — add your own'],
                ['feature' => 'Social auth', 'us' => true, 'them' => false],
                ['feature' => 'Audit logging', 'us' => true, 'them' => false],
                ['feature' => 'Price', 'us' => 'One-time purchase', 'them' => 'Free'],
            ],
        ]);
    }

    public function spark(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/LaravelSpark', [
            'competitor' => 'laravel-spark',
            'competitorName' => 'Laravel Spark',
            'title' => 'Laravel React Starter vs Laravel Spark — Feature & Price Comparison',
            'metaDescription' => 'Spark costs $99/year and focuses on billing. This starter includes billing, admin, feature flags, webhooks, and 90+ tests for a one-time price. Compare both.',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare/laravel-spark'],
                ['name' => 'Laravel Spark', 'url' => $appUrl.'/compare/laravel-spark'],
            ],
            'features' => [
                ['feature' => 'Stripe billing', 'us' => true, 'them' => true],
                ['feature' => 'Team billing / seats', 'us' => true, 'them' => true],
                ['feature' => 'Per-seat pricing UI', 'us' => true, 'them' => true],
                ['feature' => 'Billing portal', 'us' => true, 'them' => true],
                ['feature' => 'Admin panel', 'us' => true, 'them' => false],
                ['feature' => 'Feature flags', 'us' => true, 'them' => false],
                ['feature' => 'Webhooks', 'us' => true, 'them' => false],
                ['feature' => 'Audit logging', 'us' => true, 'them' => false],
                ['feature' => 'Social auth', 'us' => true, 'them' => false],
                ['feature' => '2FA', 'us' => true, 'them' => false],
                ['feature' => 'Frontend', 'us' => 'React 18 + TypeScript', 'them' => 'Bring your own'],
                ['feature' => 'Test coverage', 'us' => '90+ Pest + Vitest tests', 'them' => 'Minimal'],
                ['feature' => 'Price', 'us' => 'One-time', 'them' => '$99/year'],
                ['feature' => 'Source code access', 'us' => 'Full', 'them' => 'Full'],
            ],
        ]);
    }

    public function saasykit(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/SaaSykit', [
            'competitor' => 'saasykit',
            'competitorName' => 'SaaSykit',
            'title' => 'Laravel React Starter vs SaaSykit — Which SaaS Boilerplate Is Right for You?',
            'metaDescription' => 'SaaSykit uses Filament for admin. This starter uses a custom React admin panel with TypeScript. Compare stack, features, and philosophy for your SaaS build.',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare/saasykit'],
                ['name' => 'SaaSykit', 'url' => $appUrl.'/compare/saasykit'],
            ],
            'features' => [
                ['feature' => 'Frontend', 'us' => 'React 18 + TypeScript', 'them' => 'React (Inertia)'],
                ['feature' => 'Admin panel', 'us' => 'Custom React + TypeScript', 'them' => 'Filament (Livewire)'],
                ['feature' => 'Admin type safety', 'us' => 'Full TypeScript', 'them' => 'PHP/Blade'],
                ['feature' => 'Stripe billing', 'us' => true, 'them' => true],
                ['feature' => 'Feature flags', 'us' => true, 'them' => 'Limited'],
                ['feature' => 'Webhooks', 'us' => true, 'them' => 'Partial'],
                ['feature' => 'Audit logging', 'us' => true, 'them' => 'Limited'],
                ['feature' => 'Social auth', 'us' => true, 'them' => true],
                ['feature' => '2FA', 'us' => true, 'them' => true],
                ['feature' => 'PHPStan / static analysis', 'us' => true, 'them' => 'Varies'],
                ['feature' => 'Pest tests', 'us' => '90+', 'them' => 'Yes'],
                ['feature' => 'Test quality gates', 'us' => 'PHPStan + Pint + Vitest CI', 'them' => 'CI varies'],
                ['feature' => 'Price', 'us' => 'One-time', 'them' => 'One-time'],
            ],
        ]);
    }
}
