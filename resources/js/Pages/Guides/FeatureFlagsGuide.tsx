import { ArrowRight } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { TableOfContents, type TocSection } from '@/Components/blog/TableOfContents';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { GuidePageProps } from '@/types/index';

const sections: TocSection[] = [
    { id: 'two-level-design', title: '1. The Two-Level Design', level: 2 },
    { id: 'database-schema', title: '2. Database Schema', level: 2 },
    { id: 'feature-flag-service', title: '3. The FeatureFlagService', level: 2 },
    { id: 'route-gating', title: '4. Route Gating', level: 2 },
    { id: 'route-file-gating', title: 'Option A: Route File Gating', level: 3 },
    { id: 'middleware-gating', title: 'Option B: Middleware Gating', level: 3 },
    { id: 'react-ui-gating', title: '5. React UI Gating', level: 2 },
    { id: 'admin-ui', title: '6. The Admin UI for Runtime Toggles', level: 2 },
    { id: 'testing-flags', title: '7. Testing Feature Flags', level: 2 },
];

export default function FeatureFlagsGuide({ title, metaDescription, appName, breadcrumbs }: GuidePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-feature-flags' });
    }, [track]);

    const articleSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: title,
        description: metaDescription,
        author: { '@type': 'Organization', name: appName },
        publisher: { '@type': 'Organization', name: appName },
        datePublished: '2026-03-19',
        dateModified: '2026-03-19',
    });

    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="article" />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
                <script
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: articleSchema.replace(/<\/script>/gi, '<\\/script>') }}
                />
            </Head>

            <div className="min-h-screen bg-background">
                <nav className="container flex items-center justify-between py-6">
                    <Link href="/" className="flex items-center gap-2">
                        <Logo className="h-8 w-8" />
                        <TextLogo className="text-xl font-bold" />
                    </Link>
                    <div className="flex items-center gap-4">
                        <Link
                            href="/features/billing"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Billing
                        </Link>
                        <Link
                            href="/features/feature-flags"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Feature Flags
                        </Link>
                        <Link
                            href="/pricing"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Pricing
                        </Link>
                    </div>
                </nav>

                <main className="container pb-24">
                    <header className="mx-auto max-w-4xl py-12 text-center">
                        <p className="mb-4 text-sm font-medium uppercase tracking-wider text-primary">
                            Tutorial
                        </p>
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                            Laravel Feature Flags Without Unleash or LaunchDarkly
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                            Implement feature flags in Laravel without a third-party service: env-based toggles,
                            database overrides, per-user targeting, and a React UI for runtime control.
                        </p>
                        <div className="mt-4 flex items-center justify-center gap-4 text-sm text-muted-foreground">
                            <time dateTime="2026-03-19">March 19, 2026</time>
                            <span aria-hidden="true">&middot;</span>
                            <span>9 min read</span>
                        </div>
                    </header>

                    <div className="mx-auto max-w-6xl lg:grid lg:grid-cols-[1fr_250px] lg:gap-12">
                        <div>
                            <TableOfContents sections={sections} />

                            <article className="prose prose-neutral dark:prose-invert max-w-none">
                                <p>
                                    Third-party feature flag services like Unleash, LaunchDarkly, and Flagsmith add latency,
                                    monthly costs, and an external dependency for something you can implement in a few hours
                                    with a database table and a service class. If you&apos;re running a SaaS on Laravel, you
                                    already have a database, a cache layer, and an admin panel &mdash; everything you need
                                    for a feature flag system.
                                </p>
                                <p>
                                    This guide shows how to build a two-level feature flag system: env-based defaults for
                                    deployment-level control and database overrides for runtime, per-user targeting. It&apos;s
                                    the same system used in the{' '}
                                    <Link href="/" className="text-primary hover:underline">
                                        Laravel React Starter Kit
                                    </Link>
                                    , which manages 11 feature flags across billing, authentication, admin, and notification
                                    subsystems. For the broader architecture context, see the{' '}
                                    <Link href="/guides/building-saas-with-laravel-12" className="text-primary hover:underline">
                                        complete guide to building a SaaS with Laravel 12
                                    </Link>.
                                </p>

                                {/* Section 1 */}
                                <h2 id="two-level-design">1. The Two-Level Design</h2>
                                <p>
                                    Most feature flag implementations use a single source of truth &mdash; either a config file
                                    or a database. Both have tradeoffs. Config files are fast and don&apos;t hit the database, but
                                    require a deploy to change. Database flags can be toggled at runtime, but add a query to every
                                    request. The two-level design gives you both.
                                </p>
                                <p>Here&apos;s the resolution priority, from highest to lowest:</p>
                                <ol>
                                    <li>
                                        <strong>Per-user database override</strong> &mdash; highest priority. Used for beta
                                        access, A/B tests, and customer-specific features. A specific user sees a feature
                                        regardless of the global setting.
                                    </li>
                                    <li>
                                        <strong>Global database override</strong> &mdash; used to toggle a flag across all
                                        users at runtime without a deploy. An admin flips a switch and the feature turns on
                                        or off immediately.
                                    </li>
                                    <li>
                                        <strong><code>config/features.php</code> value</strong> &mdash; set from{' '}
                                        <code>.env</code> variables. This is the deployment-level default that ships with
                                        your code.
                                    </li>
                                    <li>
                                        <strong>Hardcoded default</strong> &mdash; the fallback if no env var is set. Typically{' '}
                                        <code>false</code> for optional features and <code>true</code> for core features like
                                        email verification.
                                    </li>
                                </ol>
                                <p>
                                    This design means a flag can be off by default (<code>FEATURE_NOTIFICATIONS=false</code> in{' '}
                                    <code>.env</code>) but enabled for a specific user via a database override. That&apos;s the
                                    beta access pattern: ship a feature disabled, enable it for internal testers, then enable it
                                    globally when ready &mdash; all without deploying.
                                </p>

                                {/* Section 2 */}
                                <h2 id="database-schema">2. Database Schema</h2>
                                <p>
                                    The feature flag override system needs one table. Here&apos;s the migration:
                                </p>
                                <pre><code>{`Schema::create('feature_flag_overrides', function (Blueprint $table) {
    $table->id();
    $table->string('flag');           // e.g., 'notifications.enabled'
    $table->boolean('value');          // the override value
    $table->foreignId('user_id')      // null = global override
        ->nullable()
        ->constrained()
        ->cascadeOnDelete();
    $table->string('reason')->nullable(); // why the override was set
    $table->foreignId('changed_by')   // who set the override (audit trail)
        ->constrained('users')
        ->cascadeOnDelete();
    $table->timestamps();

    $table->index(['flag', 'user_id']); // lookup pattern
});`}</code></pre>
                                <p>
                                    The key design decisions: <code>user_id</code> is nullable &mdash; a null value means
                                    the override is global (applies to all users). A non-null value means it&apos;s a per-user
                                    override. The <code>reason</code> field is optional but invaluable for debugging: six months
                                    from now, you&apos;ll want to know <em>why</em> notifications were disabled for user #42.
                                    The <code>changed_by</code> field creates an audit trail &mdash; you always know which admin
                                    made the change.
                                </p>
                                <p>
                                    The composite index on <code>(flag, user_id)</code> optimizes the most common query
                                    pattern: &ldquo;for this flag, does this user have an override?&rdquo; Without this index,
                                    every feature flag check hits a sequential scan on the table.
                                </p>

                                {/* Section 3 */}
                                <h2 id="feature-flag-service">3. The FeatureFlagService</h2>
                                <p>
                                    The service class encapsulates the resolution logic. It checks each level in priority order
                                    and returns the first match:
                                </p>
                                <pre><code>{`class FeatureFlagService
{
    public function enabled(string $flag, ?User $user = null): bool
    {
        // 1. Per-user override (highest priority)
        if ($user) {
            $override = FeatureFlagOverride::where('flag', $flag)
                ->where('user_id', $user->id)
                ->first();

            if ($override) {
                return $override->value;
            }
        }

        // 2. Global override (cached for performance)
        $globalOverride = Cache::remember(
            "feature_flag_global:{$flag}",
            300, // 5-minute TTL
            fn () => FeatureFlagOverride::where('flag', $flag)
                ->whereNull('user_id')
                ->first()
        );

        if ($globalOverride) {
            return $globalOverride->value;
        }

        // 3. Config default (from .env via config/features.php)
        return config("features.{$flag}", false);
    }
}`}</code></pre>
                                <p>
                                    Global overrides are cached with a 5-minute TTL because they&apos;re read on every request
                                    for authenticated users. Per-user overrides are <em>not</em> cached &mdash; they&apos;re rare
                                    enough that the database hit is acceptable, and caching them would require cache invalidation
                                    per-user, which adds complexity without meaningful performance gain.
                                </p>
                                <p>
                                    The cache must be invalidated whenever a global override changes. When the admin creates,
                                    updates, or deletes a global override, call{' '}
                                    <code>Cache::forget(&quot;feature_flag_global:&#123;$flag&#125;&quot;)</code>. If you skip
                                    this step, changes won&apos;t take effect for up to 5 minutes &mdash; fine for some features,
                                    unacceptable for others (like an emergency kill switch).
                                </p>

                                {/* Section 4 */}
                                <h2 id="route-gating">4. Route Gating</h2>
                                <p>
                                    There are two patterns for gating routes behind feature flags, each with different tradeoffs.
                                </p>

                                <h3 id="route-file-gating">Option A: Route File Gating</h3>
                                <p>
                                    Wrap route registrations in a config check. When the flag is off, the routes don&apos;t
                                    exist &mdash; requests return 404, not 403:
                                </p>
                                <pre><code>{`// routes/web.php
if (config('features.billing.enabled', false)) {
    Route::get('/pricing', PricingController::class);
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/billing', [BillingController::class, 'index']);
        Route::post('/billing/subscribe', [SubscriptionController::class, 'subscribe']);
        // ... all billing routes
    });
}`}</code></pre>
                                <p>
                                    This is the simplest approach and has zero runtime overhead &mdash; the conditional runs
                                    once during route registration at boot time, not on every request. The limitation: it reads
                                    from <code>config()</code>, which means it can&apos;t do per-user targeting. The flag
                                    value is set at deploy time via <code>.env</code>. Database overrides and per-user targeting
                                    are not available with this pattern.
                                </p>

                                <h3 id="middleware-gating">Option B: Middleware Gating</h3>
                                <p>
                                    For per-user feature targeting, use a middleware that calls{' '}
                                    <code>FeatureFlagService::enabled()</code> on each request:
                                </p>
                                <pre><code>{`class CheckFeatureFlag
{
    public function __construct(private FeatureFlagService $flags) {}

    public function handle(Request $request, Closure $next, string $flag): Response
    {
        if (! $this->flags->enabled($flag, $request->user())) {
            abort(404);
        }

        return $next($request);
    }
}

// Usage in routes
Route::get('/notifications', NotificationsController::class)
    ->middleware('feature:notifications.enabled');`}</code></pre>
                                <p>
                                    This registers the route for everyone (it shows up in <code>php artisan route:list</code>),
                                    but the middleware checks the flag on each request, including per-user overrides. The tradeoff
                                    is a database/cache hit per request. In practice, the 5-minute cache on global overrides
                                    means most requests only hit the cache, not the database.
                                </p>
                                <p>
                                    <strong>Which to use?</strong> Route-file gating for features that are entirely on or off
                                    per deployment (billing, webhooks, API docs). Middleware gating for features that need
                                    per-user targeting (beta features, A/B tests, gradual rollouts).
                                </p>

                                {/* Section 5 */}
                                <h2 id="react-ui-gating">5. React UI Gating</h2>
                                <p>
                                    Feature flags need to propagate to the frontend. With Inertia.js, the cleanest approach
                                    is a shared prop. In your <code>HandleInertiaRequests</code> middleware, compute the
                                    feature state for the current user and pass it as a flat key-value map:
                                </p>
                                <pre><code>{`// In HandleInertiaRequests::share()
'features' => fn () => [
    'billing' => feature_enabled('billing.enabled'),
    'notifications' => feature_enabled('notifications.enabled'),
    'two_factor' => feature_enabled('two_factor.enabled'),
    // ... all flags relevant to the frontend
],`}</code></pre>
                                <p>
                                    In React components, read the features prop and conditionally render UI elements:
                                </p>
                                <pre><code>{`import { usePage } from '@inertiajs/react';

function AppSidebar() {
    const { features } = usePage().props;

    return (
        <nav>
            <SidebarLink href="/dashboard">Dashboard</SidebarLink>
            {features.billing && <SidebarLink href="/billing">Billing</SidebarLink>}
            {features.notifications && <NotificationBell />}
        </nav>
    );
}`}</code></pre>
                                <p>
                                    The <code>features</code> prop is computed once per request in{' '}
                                    <code>HandleInertiaRequests</code> and shared with every Inertia page. This is the single
                                    source of truth for the frontend. Never resolve feature flags client-side from{' '}
                                    <code>localStorage</code> or cookies &mdash; the server resolves them, including per-user
                                    overrides, and the frontend simply reads the result.
                                </p>
                                <p>
                                    For unauthenticated pages (marketing, landing pages), use config defaults since there&apos;s
                                    no user to resolve per-user overrides against. The <code>feature_enabled()</code> helper
                                    falls through to config when no user is provided.
                                </p>

                                {/* Section 6 */}
                                <h2 id="admin-ui">6. The Admin UI for Runtime Toggles</h2>
                                <p>
                                    A feature flag system without a UI requires SSH access and database queries to toggle flags.
                                    That works for a solo developer, but it&apos;s fragile and error-prone. An admin panel UI
                                    makes flag management safe and auditable.
                                </p>
                                <p>The admin UI for feature flags needs four capabilities:</p>
                                <ul>
                                    <li>
                                        <strong>List all registered flags</strong> with their current effective state: is it
                                        on because of a global override, or because the config default is <code>true</code>?
                                        Showing the <em>source</em> of the current value (override vs. config) prevents confusion.
                                    </li>
                                    <li>
                                        <strong>Toggle global overrides</strong> with a required reason field. &ldquo;Turned on
                                        for marketing launch&rdquo; or &ldquo;Disabled due to performance issues&rdquo; &mdash;
                                        the reason is what makes the change understandable months later.
                                    </li>
                                    <li>
                                        <strong>View per-user overrides</strong> from the user detail page in the{' '}
                                        <Link href="/features/admin-panel" className="text-primary hover:underline">
                                            admin panel
                                        </Link>
                                        . When investigating a user&apos;s experience, you need to see what flags are overridden
                                        for them specifically.
                                    </li>
                                    <li>
                                        <strong>Audit logging</strong> for every change. All flag changes go through the{' '}
                                        <code>AuditService</code>, creating a permanent record of who changed what, when, and why.
                                    </li>
                                </ul>
                                <p>
                                    The UI doesn&apos;t need a separate API. Use standard Inertia form posts to a{' '}
                                    <code>FeatureFlagController</code> that creates, updates, or deletes{' '}
                                    <code>FeatureFlagOverride</code> records. On every write, invalidate the cached global override
                                    for the affected flag. The admin sees the change immediately; other users see it within the
                                    5-minute cache TTL (or immediately if the cache is flushed).
                                </p>

                                {/* Section 7 */}
                                <h2 id="testing-flags">7. Testing Feature Flags</h2>
                                <p>
                                    Feature flags introduce conditional behavior, which means your test suite needs to cover
                                    both states. Here&apos;s what to test:
                                </p>
                                <p>
                                    <strong>Resolution priority:</strong> Create a per-user override that returns{' '}
                                    <code>true</code> for a specific user while the config default is <code>false</code>.
                                    Assert that <code>FeatureFlagService::enabled()</code> returns <code>true</code> for that
                                    user and <code>false</code> for a different user. This validates the priority chain.
                                </p>
                                <pre><code>{`it('per-user override takes priority over config default', function () {
    config()->set('features.notifications.enabled', false);
    $beta = User::factory()->create();
    $regular = User::factory()->create();

    FeatureFlagOverride::create([
        'flag' => 'notifications.enabled',
        'value' => true,
        'user_id' => $beta->id,
        'changed_by' => $beta->id,
    ]);

    $service = app(FeatureFlagService::class);
    expect($service->enabled('notifications.enabled', $beta))->toBeTrue();
    expect($service->enabled('notifications.enabled', $regular))->toBeFalse();
});`}</code></pre>
                                <p>
                                    <strong>Cache invalidation:</strong> Set a global override, verify the cached value, then
                                    update the override and confirm the cache is invalidated. This catches a common bug where
                                    flag changes appear to &ldquo;not work&rdquo; because of stale cached values.
                                </p>
                                <p>
                                    <strong>Route gating:</strong> With a flag set to <code>false</code> in config, assert the
                                    route returns 404. Important caveat: routes registered at boot time (in route files) cannot
                                    be toggled within a single test suite. The test environment&apos;s config determines what
                                    routes are registered when the application boots. For per-user middleware gating, you can
                                    test both enabled and disabled states by creating or omitting the database override.
                                </p>
                                <p>
                                    <strong>React <code>features</code> prop:</strong> Assert that{' '}
                                    <code>HandleInertiaRequests::share()</code> returns the correct feature state for an
                                    authenticated user with a known override. This confirms the server&ndash;to&ndash;client
                                    data flow works end to end.
                                </p>

                                {/* Closing */}
                                <hr />
                                <p>
                                    A feature flag system doesn&apos;t need a SaaS vendor. A database table, a service class,
                                    and an admin UI give you everything LaunchDarkly offers for a typical SaaS application &mdash;
                                    without the latency, cost, or vendor dependency. The{' '}
                                    <Link href="/features/feature-flags" className="text-primary hover:underline">
                                        Laravel React Starter&apos;s feature flag system
                                    </Link>{' '}
                                    implements everything in this guide with 11 pre-configured flags, an admin UI, and full
                                    test coverage.
                                </p>
                            </article>
                        </div>

                        {/* Desktop sidebar ToC */}
                        <aside className="hidden lg:block">
                            <div className="sticky top-8">
                                <TableOfContents sections={sections} />
                            </div>
                        </aside>
                    </div>

                    {/* CTA block */}
                    <div className="mx-auto mt-16 max-w-2xl rounded-lg border bg-muted/50 p-8 text-center">
                        <h2 className="text-2xl font-bold">See the Pre-Built Feature Flag System</h2>
                        <p className="mt-3 text-muted-foreground">
                            The Laravel React Starter includes a complete feature flag system with env-based defaults,
                            database overrides, per-user targeting, admin UI, and audit logging &mdash; ready to use
                            out of the box.
                        </p>
                        <div className="mt-6 flex flex-wrap items-center justify-center gap-4">
                            <Button size="lg" asChild>
                                <Link href="/features/feature-flags">
                                    Explore the feature flag system
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/pricing">View pricing</Link>
                            </Button>
                        </div>
                    </div>
                </main>

                <footer className="border-t py-8">
                    <div className="container">
                        <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                            <p className="text-sm text-muted-foreground">
                                &copy; {new Date().getFullYear()}{' '}
                                {import.meta.env.VITE_APP_NAME || 'Laravel React Starter'}.
                                All rights reserved.
                            </p>
                            <nav className="flex items-center gap-4 text-sm text-muted-foreground">
                                <Link
                                    href="/features/billing"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Billing
                                </Link>
                                <Link
                                    href="/features/feature-flags"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Feature Flags
                                </Link>
                                <Link
                                    href="/features/admin-panel"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Admin Panel
                                </Link>
                                <Link
                                    href="/pricing"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Pricing
                                </Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
