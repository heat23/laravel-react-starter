<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class CompareController extends Controller
{
    /**
     * Returns the customizable template purchase price from config.
     * Set TEMPLATE_PRICE in .env before launch.
     */
    private function templatePrice(): string
    {
        return config('app.template_price', '$[YOUR_PRICE]');
    }

    /**
     * All competitors with name, route slug, and one-line positioning.
     */
    private function allCompetitors(): array
    {
        return [
            ['name' => 'Laravel Jetstream', 'slug' => 'laravel-jetstream', 'tagline' => 'Free auth scaffolding (Vue/Livewire)'],
            ['name' => 'Laravel Spark',     'slug' => 'laravel-spark',     'tagline' => 'Billing-focused, $99/year recurring'],
            ['name' => 'SaaSykit',          'slug' => 'saasykit',          'tagline' => 'React + Filament admin panel'],
            ['name' => 'Wave',              'slug' => 'wave',              'tagline' => 'Blade + Livewire, open-source'],
            ['name' => 'Shipfast',          'slug' => 'shipfast',          'tagline' => 'Next.js starter, ~$299 one-time'],
            ['name' => 'Supastarter',       'slug' => 'supastarter',       'tagline' => 'Supabase + Next.js, ~$299 one-time'],
            ['name' => 'Larafast',          'slug' => 'larafast',          'tagline' => 'Laravel + Blade/Livewire, one-time'],
            ['name' => 'Makerkit',          'slug' => 'makerkit',          'tagline' => 'React + Supabase, $299/year'],
        ];
    }

    /**
     * Returns related competitor list excluding the current one.
     */
    private function relatedComparisons(string $excludeSlug): array
    {
        return array_values(array_filter(
            $this->allCompetitors(),
            fn (array $c) => $c['slug'] !== $excludeSlug
        ));
    }

    public function index(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/Index', [
            'title' => 'Laravel SaaS Starter Kit Comparison 2026 — Best Boilerplates Reviewed',
            'metaDescription' => 'Compare 8 Laravel SaaS boilerplates side by side and find the right Laravel SaaS starter kit for your project. Features, pricing, and honest pros/cons.',
            'appUrl' => $appUrl,
            'canonicalUrl' => $appUrl.'/compare',
            'competitors' => $this->allCompetitors(),
            'templatePrice' => $this->templatePrice(),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
            ],
        ]);
    }

    public function jetstream(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/LaravelJetstream', [
            'competitor' => 'laravel-jetstream',
            'competitorName' => 'Laravel Jetstream',
            'title' => 'Laravel React Starter vs Jetstream — Side-by-Side Comparison',
            'metaDescription' => 'Jetstream uses Vue or Livewire. This starter ships React + TypeScript out of the box. Compare features, billing, admin panel, and production readiness.',
            'canonicalUrl' => $appUrl.'/compare/laravel-jetstream',
            'lastVerified' => '2026-03',
            'relatedComparisons' => $this->relatedComparisons('laravel-jetstream'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
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
                ['feature' => 'Test coverage', 'us' => '90+ tests, PHPStan, Vitest', 'them' => 'Auth scaffolding tests only, no billing or admin coverage'],
                ['feature' => 'Social auth', 'us' => true, 'them' => false],
                ['feature' => 'Audit logging', 'us' => true, 'them' => false],
                ['feature' => 'Price', 'us' => 'One-time — '.$this->templatePrice(), 'them' => 'Free (open-source)'],
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
            'canonicalUrl' => $appUrl.'/compare/laravel-spark',
            'lastVerified' => '2026-03',
            'relatedComparisons' => $this->relatedComparisons('laravel-spark'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
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
                ['feature' => 'Price', 'us' => 'One-time — '.$this->templatePrice(), 'them' => '$99/year (per project)'],
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
            'title' => 'Laravel React Starter vs SaaSykit — React vs Filament Admin',
            'metaDescription' => 'SaaSykit uses Filament for admin. This starter uses a custom React admin panel with TypeScript. Compare stack, features, and philosophy for your SaaS build.',
            'canonicalUrl' => $appUrl.'/compare/saasykit',
            'lastVerified' => '2026-03',
            'relatedComparisons' => $this->relatedComparisons('saasykit'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
                ['name' => 'SaaSykit', 'url' => $appUrl.'/compare/saasykit'],
            ],
            'features' => [
                ['feature' => 'Frontend', 'us' => 'React 18 + TypeScript', 'them' => 'React (Inertia)'],
                ['feature' => 'Admin panel', 'us' => 'Custom React + TypeScript', 'them' => 'Filament (Livewire)'],
                ['feature' => 'Admin type safety', 'us' => 'Full TypeScript', 'them' => 'PHP/Blade'],
                ['feature' => 'Stripe billing', 'us' => true, 'them' => true],
                ['feature' => 'Feature flags', 'us' => true, 'them' => 'Config-only, no runtime DB overrides'],
                ['feature' => 'Webhooks', 'us' => true, 'them' => 'Outgoing only, no HMAC signing or delivery tracking'],
                ['feature' => 'Audit logging', 'us' => true, 'them' => 'Login events only, no billing or admin actions'],
                ['feature' => 'Social auth', 'us' => true, 'them' => true],
                ['feature' => '2FA', 'us' => true, 'them' => true],
                ['feature' => 'PHPStan / static analysis', 'us' => true, 'them' => 'Not included in default setup'],
                ['feature' => 'Pest tests', 'us' => '90+', 'them' => 'Yes'],
                ['feature' => 'Test quality gates', 'us' => 'PHPStan + Pint + Vitest CI', 'them' => 'CI varies'],
                ['feature' => 'Price', 'us' => 'One-time — '.$this->templatePrice(), 'them' => 'One-time (~$149–$299)'],
            ],
        ]);
    }

    public function wave(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/Wave', [
            'competitor' => 'wave',
            'competitorName' => 'Wave',
            'title' => 'Laravel React Starter vs Wave — SaaS Boilerplate Comparison',
            'metaDescription' => 'Compare Laravel React Starter vs Wave — both are Laravel SaaS starters, but our template ships with React, TypeScript, tested auth, and CI/CD out of the box.',
            'canonicalUrl' => $appUrl.'/compare/wave',
            'lastVerified' => '2026-03',
            'relatedComparisons' => $this->relatedComparisons('wave'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
                ['name' => 'Wave', 'url' => $appUrl.'/compare/wave'],
            ],
            'features' => [
                ['feature' => 'Frontend', 'us' => 'React 18 + TypeScript', 'them' => 'Blade + Livewire + Alpine.js'],
                ['feature' => 'Admin panel', 'us' => 'React + TypeScript', 'them' => 'Filament-based'],
                ['feature' => 'Stripe billing', 'us' => 'Yes (Redis-locked, custom)', 'them' => 'Via Laravel Spark'],
                ['feature' => 'Feature flags', 'us' => 'Yes (11 flags, DB overrides)', 'them' => 'No DB overrides, config-only toggles'],
                ['feature' => 'Webhooks (in + out)', 'us' => 'Yes (HMAC-signed)', 'them' => false],
                ['feature' => 'Audit logging', 'us' => true, 'them' => 'Basic activity log, no IP or user agent tracking'],
                ['feature' => 'Social auth', 'us' => true, 'them' => true],
                ['feature' => '2FA', 'us' => 'Yes (TOTP)', 'them' => true],
                ['feature' => 'PHPStan / static analysis', 'us' => 'Yes (Larastan, level 8)', 'them' => 'Not standard'],
                ['feature' => 'Vitest / TypeScript tests', 'us' => true, 'them' => 'No (no TypeScript)'],
                ['feature' => 'Test coverage', 'us' => '90+ Pest + Vitest', 'them' => 'PHP tests only'],
                ['feature' => 'Blog / announcements', 'us' => 'No (add your own)', 'them' => 'Yes (built-in blog)'],
                ['feature' => 'License', 'us' => 'Commercial', 'them' => 'Open-source (MIT)'],
                ['feature' => 'Price', 'us' => 'One-time — '.$this->templatePrice(), 'them' => 'Free'],
            ],
        ]);
    }

    public function shipfast(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/Shipfast', [
            'competitor' => 'shipfast',
            'competitorName' => 'Shipfast',
            'title' => 'Laravel React Starter vs Shipfast — Laravel vs Next.js SaaS Starter',
            'metaDescription' => 'Shipfast is a Next.js starter. This is its Laravel equivalent: full-stack React + TypeScript with server-side rendering via Inertia, Stripe billing, and a built-in admin panel.',
            'canonicalUrl' => $appUrl.'/compare/shipfast',
            'lastVerified' => '2026-03',
            'relatedComparisons' => $this->relatedComparisons('shipfast'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
                ['name' => 'Shipfast', 'url' => $appUrl.'/compare/shipfast'],
            ],
            'features' => [
                ['feature' => 'Backend', 'us' => 'Laravel 12 (PHP)', 'them' => 'Next.js (Node.js)'],
                ['feature' => 'Frontend', 'us' => 'React 18 + TypeScript', 'them' => 'React + TypeScript'],
                ['feature' => 'Rendering', 'us' => 'SSR via Inertia.js', 'them' => 'SSR via Next.js'],
                ['feature' => 'Database', 'us' => 'MySQL / PostgreSQL (Eloquent)', 'them' => 'MongoDB or PostgreSQL (Prisma)'],
                ['feature' => 'Auth', 'us' => 'Breeze + Sanctum', 'them' => 'NextAuth.js'],
                ['feature' => 'Stripe billing', 'us' => 'Yes (Redis-locked, 4 tiers)', 'them' => 'Yes (Stripe.js)'],
                ['feature' => 'Admin panel', 'us' => 'Yes (custom React)', 'them' => false],
                ['feature' => 'Feature flags', 'us' => 'Yes (11 flags, DB overrides)', 'them' => false],
                ['feature' => 'Webhooks', 'us' => 'Yes (in + out, HMAC)', 'them' => 'Partial'],
                ['feature' => 'Audit logging', 'us' => true, 'them' => false],
                ['feature' => '2FA', 'us' => 'Yes (TOTP)', 'them' => false],
                ['feature' => 'PHPStan / TypeScript', 'us' => 'Both (PHP + TS)', 'them' => 'TypeScript only'],
                ['feature' => 'Test coverage', 'us' => '90+ Pest + Vitest', 'them' => 'Varies'],
                ['feature' => 'Deployment', 'us' => 'VPS (nginx + supervisor)', 'them' => 'Vercel / serverless'],
                ['feature' => 'License', 'us' => 'Commercial', 'them' => 'Commercial'],
                ['feature' => 'Price', 'us' => 'One-time — '.$this->templatePrice(), 'them' => '~$299 (Shipfast)'],
            ],
        ]);
    }

    public function larafast(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/Larafast', [
            'competitor' => 'larafast',
            'competitorName' => 'Larafast',
            'title' => 'Laravel React Starter vs Larafast 2026 — Full Comparison',
            'metaDescription' => 'Larafast vs Laravel React Starter: honest comparison. See pricing, TypeScript support, test coverage, webhooks, and which larafast alternative ships more.',
            'appUrl' => $appUrl,
            'canonicalUrl' => $appUrl.'/compare/larafast',
            'lastVerified' => '2026-03',
            'relatedComparisons' => $this->relatedComparisons('larafast'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
                ['name' => 'Larafast vs Laravel React Starter', 'url' => $appUrl.'/compare/larafast'],
            ],
            'features' => [
                ['feature' => 'Price', 'us' => 'One-time — '.$this->templatePrice(), 'them' => 'One-time purchase'],
                ['feature' => 'Frontend stack', 'us' => 'React 18 + TypeScript', 'them' => 'Blade / Livewire (React add-on)'],
                ['feature' => 'Admin panel', 'us' => 'Custom React + TypeScript', 'them' => 'Filament (Livewire/PHP)'],
                ['feature' => 'Stripe billing', 'us' => 'Yes (Redis-locked, 4 tiers)', 'them' => 'Yes'],
                ['feature' => 'TypeScript', 'us' => 'Full (frontend + admin)', 'them' => 'No (PHP/Blade default)'],
                ['feature' => 'Pest test suite', 'us' => '90+ tests', 'them' => 'Minimal coverage, no billing or webhook tests'],
                ['feature' => 'PHPStan / static analysis', 'us' => 'Yes (Larastan, level 8)', 'them' => 'Not standard'],
                ['feature' => 'Vitest frontend tests', 'us' => true, 'them' => false],
                ['feature' => 'Open source', 'us' => 'Commercial (full source)', 'them' => 'Commercial (full source)'],
                ['feature' => 'Feature flags (11)', 'us' => 'Yes (DB overrides)', 'them' => false],
                ['feature' => 'Webhooks (in + out)', 'us' => 'Yes (HMAC-signed)', 'them' => false],
                ['feature' => 'Audit logging', 'us' => true, 'them' => false],
                ['feature' => 'Two-factor auth (TOTP)', 'us' => true, 'them' => true],
                ['feature' => 'Social auth', 'us' => 'Yes (Google + GitHub)', 'them' => true],
                ['feature' => 'API tokens', 'us' => true, 'them' => 'Included in higher-tier plans'],
                ['feature' => 'Onboarding flow', 'us' => true, 'them' => 'Basic welcome screen only'],
                ['feature' => 'Accessibility (WCAG 2.1 AA)', 'us' => true, 'them' => 'Not specified'],
                ['feature' => 'Deployment config', 'us' => 'nginx + supervisor (VPS)', 'them' => 'VPS / cloud'],
            ],
        ]);
    }

    public function nextjsSaas(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/NextjsSaas', [
            'title' => 'Laravel vs Next.js for SaaS 2026 — Full Stack Comparison',
            'metaDescription' => 'Which is better for SaaS in 2026? Laravel vs Next.js compared on developer experience, performance, ecosystem, and deployment. With starter kit recommendations.',
            'appName' => config('app.name', 'Laravel React Starter'),
            'canonicalUrl' => $appUrl.'/compare/laravel-vs-nextjs',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
                ['name' => 'Laravel vs Next.js for SaaS', 'url' => $appUrl.'/compare/laravel-vs-nextjs'],
            ],
        ]);
    }

    public function supastarter(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/Supastarter', [
            'competitor' => 'supastarter',
            'competitorName' => 'Supastarter',
            'title' => 'Laravel React Starter vs Supastarter — Laravel vs Supabase',
            'metaDescription' => 'Supastarter uses Supabase + Next.js. This starter uses Laravel + MySQL + Redis. Compare auth, billing, admin, and backend philosophy for your SaaS architecture decision.',
            'canonicalUrl' => $appUrl.'/compare/supastarter',
            'lastVerified' => '2026-03',
            'relatedComparisons' => $this->relatedComparisons('supastarter'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
                ['name' => 'Supastarter', 'url' => $appUrl.'/compare/supastarter'],
            ],
            'features' => [
                ['feature' => 'Backend', 'us' => 'Laravel 12 (PHP)', 'them' => 'Supabase (BaaS) + Next.js'],
                ['feature' => 'Database', 'us' => 'MySQL / PostgreSQL (Eloquent ORM)', 'them' => 'PostgreSQL (Supabase)'],
                ['feature' => 'Auth', 'us' => 'Breeze + Sanctum (self-hosted)', 'them' => 'Supabase Auth (managed)'],
                ['feature' => 'Realtime', 'us' => 'Via Laravel Echo + Pusher/Reverb', 'them' => 'Supabase Realtime (built-in)'],
                ['feature' => 'File storage', 'us' => 'Laravel Storage (S3/local)', 'them' => 'Supabase Storage'],
                ['feature' => 'Stripe billing', 'us' => 'Yes (Redis-locked, 4 tiers)', 'them' => 'Yes (via Stripe SDK)'],
                ['feature' => 'Admin panel', 'us' => 'Yes (custom React)', 'them' => 'Limited'],
                ['feature' => 'Feature flags', 'us' => 'Yes (11 flags, DB overrides)', 'them' => false],
                ['feature' => 'Webhooks', 'us' => 'Yes (in + out, HMAC)', 'them' => 'Supabase webhooks (limited)'],
                ['feature' => 'Audit logging', 'us' => true, 'them' => 'Supabase audit log (limited)'],
                ['feature' => '2FA', 'us' => 'Yes (TOTP)', 'them' => 'Via Supabase Auth'],
                ['feature' => 'Social auth', 'us' => 'Yes (Google + GitHub)', 'them' => 'Via Supabase Auth'],
                ['feature' => 'Test coverage', 'us' => '90+ Pest + Vitest', 'them' => 'Varies'],
                ['feature' => 'Vendor lock-in', 'us' => 'Low (self-hosted MySQL)', 'them' => 'Medium (Supabase APIs)'],
                ['feature' => 'Deployment', 'us' => 'VPS or cloud (flexible)', 'them' => 'Vercel + Supabase Cloud'],
                ['feature' => 'Price', 'us' => 'One-time — '.$this->templatePrice(), 'them' => '~$299 (Supastarter)'],
            ],
        ]);
    }

    public function makerkit(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Compare/Makerkit', [
            'competitor' => 'makerkit',
            'competitorName' => 'Makerkit',
            'title' => 'Laravel React Starter vs Makerkit — Laravel vs Supabase SaaS Starter',
            'metaDescription' => 'Makerkit uses Supabase + Next.js or Remix. This starter uses Laravel + MySQL + Redis. Compare stack, features, pricing, and vendor lock-in for your SaaS decision.',
            'canonicalUrl' => $appUrl.'/compare/makerkit',
            'lastVerified' => '2026-03',
            'relatedComparisons' => $this->relatedComparisons('makerkit'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Compare', 'url' => $appUrl.'/compare'],
                ['name' => 'Makerkit', 'url' => $appUrl.'/compare/makerkit'],
            ],
            'features' => [
                ['feature' => 'Backend', 'us' => 'Laravel 12 (PHP)', 'them' => 'Supabase (BaaS) + Next.js/Remix'],
                ['feature' => 'Database', 'us' => 'MySQL / PostgreSQL (Eloquent ORM)', 'them' => 'PostgreSQL (Supabase)'],
                ['feature' => 'Auth', 'us' => 'Breeze + Sanctum (self-hosted)', 'them' => 'Supabase Auth (managed)'],
                ['feature' => 'Stripe billing', 'us' => 'Yes (Redis-locked, 4 tiers)', 'them' => 'Yes (Stripe SDK)'],
                ['feature' => 'Admin panel', 'us' => 'Yes (custom React)', 'them' => 'Yes (Supabase-based)'],
                ['feature' => 'Feature flags', 'us' => 'Yes (11 flags, DB overrides)', 'them' => 'Limited'],
                ['feature' => 'Webhooks', 'us' => 'Yes (in + out, HMAC)', 'them' => 'Partial'],
                ['feature' => 'Audit logging', 'us' => true, 'them' => 'Supabase audit (limited)'],
                ['feature' => '2FA', 'us' => 'Yes (TOTP)', 'them' => 'Via Supabase Auth'],
                ['feature' => 'Social auth', 'us' => 'Yes (Google + GitHub)', 'them' => 'Via Supabase Auth'],
                ['feature' => 'Test coverage', 'us' => '90+ Pest + Vitest', 'them' => 'Varies'],
                ['feature' => 'Vendor lock-in', 'us' => 'Low (self-hosted)', 'them' => 'High (Supabase APIs)'],
                ['feature' => 'Deployment', 'us' => 'VPS or cloud (flexible)', 'them' => 'Vercel + Supabase Cloud'],
                ['feature' => 'Price', 'us' => 'One-time — '.$this->templatePrice(), 'them' => '~$299/year (Makerkit)'],
            ],
        ]);
    }
}
