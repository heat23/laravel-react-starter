// Article content is hardcoded as structured JSX (Option A).
// For a multi-article blog, Option B (Markdown files in resources/content/guides/
// parsed by league/commonmark and passed as HTML props) would be preferable.
// Option B would require DOMPurify.sanitize() on all rendered HTML.

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

import DOMPurify from 'dompurify';

const sections: TocSection[] = [
    { id: 'frontend-architecture', title: '1. Choosing Your Frontend Architecture', level: 2 },
    { id: 'inertia-react', title: 'Inertia.js + React', level: 3 },
    { id: 'livewire', title: 'Livewire', level: 3 },
    { id: 'inertia-vue', title: 'Inertia.js + Vue', level: 3 },
    { id: 'authentication', title: '2. Authentication', level: 2 },
    { id: 'stripe-billing', title: '3. Stripe Billing That Won\u2019t Bite You', level: 2 },
    { id: 'race-condition', title: 'The Race Condition Problem', level: 3 },
    { id: 'plan-tiers', title: 'Plan Tiers and Config-Driven Pricing', level: 3 },
    { id: 'team-seats', title: 'Team Seat Billing', level: 3 },
    { id: 'dunning', title: 'Dunning \u2014 Handling Failed Payments', level: 3 },
    { id: 'incomplete-payments', title: 'The Incomplete Payment Flow', level: 3 },
    { id: 'feature-flags', title: '4. Feature Flags', level: 2 },
    { id: 'admin-panel', title: '5. Admin Panel \u2014 What You Actually Need', level: 2 },
    { id: 'testing-strategy', title: '6. Testing Strategy for a SaaS Codebase', level: 2 },
    { id: 'pest-tests', title: 'Pest (PHP) \u2014 Behavior Tests', level: 3 },
    { id: 'vitest-tests', title: 'Vitest (React) \u2014 Component Tests', level: 3 },
    { id: 'phpstan', title: 'PHPStan (Static Analysis)', level: 3 },
    { id: 'skip-early', title: 'What to Skip at Early Stage', level: 3 },
    { id: 'ci-cd', title: '7. CI/CD for a Solo Founder or Small Team', level: 2 },
    { id: 'starter-kit-decision', title: '8. The Laravel SaaS Starter Kit Decision', level: 2 },
    { id: 'conclusion', title: 'Conclusion', level: 2 },
];

export default function LaravelSaasGuide({ title, metaDescription, appName, breadcrumbs }: GuidePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-laravel-saas' });
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
                    dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(articleSchema) }}
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
                            Comprehensive Guide
                        </p>
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                            Complete Guide to Building a SaaS with Laravel 12 in 2026
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                            Everything you need to ship a production Laravel 12 SaaS: auth, billing,
                            admin panel, feature flags, webhooks, and testing &mdash; with code examples
                            and a working starter kit.
                        </p>
                        <div className="mt-4 flex items-center justify-center gap-4 text-sm text-muted-foreground">
                            <time dateTime="2026-03-19">March 19, 2026</time>
                            <span aria-hidden="true">&middot;</span>
                            <span>25 min read</span>
                        </div>
                    </header>

                    {/* Two-column layout: article + sticky ToC */}
                    <div className="mx-auto max-w-6xl lg:grid lg:grid-cols-[1fr_250px] lg:gap-12">
                        <div>
                            {/* Mobile ToC */}
                            <TableOfContents sections={sections} />

                            <article className="prose prose-neutral dark:prose-invert max-w-none">
                                {/* Introduction */}
                                <p>
                                    Laravel has been a top choice for web applications for over a decade, but in 2026 it&apos;s
                                    a different beast entirely. PHP 8.3 brought JIT compilation improvements, readonly classes,
                                    and typed class constants that make the runtime meaningfully faster. Laravel 12 builds on
                                    this with streamlined configuration (everything in <code>bootstrap/app.php</code>),
                                    auto-discovered providers and events, and a mature ecosystem that covers every SaaS need:
                                    Cashier for billing, Sanctum for API authentication, Horizon for queue monitoring, and
                                    Octane for high-concurrency deployments.
                                </p>
                                <p>
                                    But having great tools available isn&apos;t the same as having a production-ready application.
                                    The gap between &ldquo;I installed Laravel&rdquo; and &ldquo;I have a SaaS that handles
                                    billing, auth, admin, and testing correctly&rdquo; is measured in weeks of full-time work.
                                    This guide walks through the eight major decisions you&apos;ll make when building a SaaS on
                                    Laravel 12, explains the tradeoffs for each, and shows you the patterns that work in
                                    production &mdash; including the ones that tutorials skip.
                                </p>
                                <p>
                                    Full disclosure: the author built a{' '}
                                    <Link href="/" className="text-primary hover:underline">
                                        production-ready Laravel SaaS starter kit
                                    </Link>{' '}
                                    that implements every pattern described in this guide. This article explains the decisions
                                    and the reasoning &mdash; the starter kit is the working code. You don&apos;t need the
                                    starter kit to benefit from this guide, but if you want to skip the implementation work,
                                    it&apos;s there.
                                </p>

                                {/* Section 1: Frontend Architecture */}
                                <h2 id="frontend-architecture">1. Choosing Your Frontend Architecture</h2>
                                <p>
                                    The frontend architecture decision shapes everything that follows: your component library,
                                    your testing approach, your deployment pipeline, and the developers you can hire. In 2026
                                    there are three real options for Laravel SaaS projects, each with honest tradeoffs.
                                </p>

                                <h3 id="inertia-react">Inertia.js + React</h3>
                                <p>
                                    Inertia gives you a full single-page application experience with server-side routing.
                                    You write Laravel controllers that return <code>Inertia::render()</code> instead of Blade
                                    views, and your React components receive typed props directly from the controller. No API
                                    layer to build, no client-side router to configure, no state management library to debate.
                                </p>
                                <p>
                                    Pair this with TypeScript in strict mode and you get end-to-end type safety from your PHP
                                    controller to your React component. Vite handles hot module replacement during development.
                                    The monorepo structure means your frontend and backend deploy as one unit &mdash; no CORS,
                                    no versioning headaches, no separate deployment pipeline.
                                </p>
                                <p>
                                    This is the approach used in{' '}
                                    <Link href="/" className="text-primary hover:underline">
                                        this starter kit
                                    </Link>
                                    , and it&apos;s the one this guide assumes throughout.
                                </p>

                                <h3 id="livewire">Livewire</h3>
                                <p>
                                    Livewire is the right choice if your team is PHP-first and you want server-driven
                                    interactivity without JavaScript build tooling. It excels at forms-heavy CRUD applications
                                    where the primary interaction is filling in fields and submitting data. The learning curve
                                    is gentler for PHP developers, and you skip the entire React/Vue ecosystem.
                                </p>
                                <p>
                                    The tradeoff: complex interactive UIs (drag-and-drop, real-time dashboards, rich text
                                    editors) are harder to build in Livewire. You&apos;ll also miss TypeScript&apos;s compile-time
                                    type checking on the frontend. For a SaaS with an admin panel, billing forms, and
                                    settings pages, Livewire works well. For a SaaS with a complex, interactive product UI,
                                    React is the stronger choice.
                                </p>

                                <h3 id="inertia-vue">Inertia.js + Vue</h3>
                                <p>
                                    Vue with Inertia is functionally equivalent to React with Inertia. The architecture,
                                    deployment, and testing story are nearly identical. Choose Vue if your team already knows
                                    Vue, if you prefer template syntax over JSX, or if you&apos;re coming from a
                                    Jetstream background. Laravel Jetstream uses Vue by default, so there&apos;s more
                                    Laravel-specific Vue content available.
                                </p>
                                <p>
                                    <strong>Recommendation:</strong> React + TypeScript for teams who want end-to-end type
                                    safety and a shared component model between marketing pages and the authenticated
                                    application. The React ecosystem is larger, TypeScript support is more mature, and hiring
                                    React developers is easier in most markets.
                                </p>

                                {/* Section 2: Authentication */}
                                <h2 id="authentication">2. Authentication</h2>
                                <p>
                                    Laravel 12 gives you strong building blocks: Fortify for headless auth logic, Breeze for
                                    scaffolded auth pages, and Sanctum for SPA cookie authentication and API token management.
                                    But a production SaaS needs more than login and register forms. Here&apos;s what you still
                                    need to build on top of the framework defaults.
                                </p>
                                <p>
                                    <strong>Social auth (OAuth):</strong> Use{' '}
                                    <code>laravel/socialite</code> with Google and GitHub as your initial providers. The critical
                                    design decision: social accounts need their own model (<code>SocialAccount</code>) with a
                                    polymorphic relationship to <code>User</code>. Don&apos;t bolt OAuth credentials directly
                                    onto the <code>users</code> table &mdash; you&apos;ll want to support multiple providers per
                                    user, and the <code>users</code> table shouldn&apos;t grow a column for every new OAuth
                                    provider. The starter kit auto-detects available providers by checking for{' '}
                                    <code>GOOGLE_CLIENT_ID</code> and <code>GITHUB_CLIENT_ID</code> environment variables.
                                </p>
                                <p>
                                    <strong>Two-factor authentication (TOTP):</strong> The <code>laragear/two-factor</code>{' '}
                                    package handles TOTP generation, QR code rendering, and recovery codes. Rolling your own
                                    TOTP implementation is tempting but risky &mdash; there are subtle timing-safe comparison
                                    requirements and QR code encoding edge cases that a battle-tested package handles correctly.
                                    Gate the 2FA setup UI behind a{' '}
                                    <Link href="/features/feature-flags" className="text-primary hover:underline">
                                        feature flag
                                    </Link>{' '}
                                    so you can enable it selectively during rollout.
                                </p>
                                <p>
                                    <strong>Rate limiting:</strong> Every auth endpoint needs rate limits. Without them, a
                                    determined attacker can brute-force passwords or flood your email verification queue.
                                    The minimum: registration at 5 requests per minute, login at 10 per minute with a composite
                                    key of IP + email (so one IP can&apos;t lock out an email address, but one email can&apos;t
                                    be brute-forced from multiple IPs), and password reset at 3 per minute. Configure these in{' '}
                                    <code>AppServiceProvider</code> using Laravel&apos;s <code>RateLimiter</code> facade.
                                </p>
                                <p>
                                    <strong>Audit logging:</strong> Log every authentication event &mdash; login, logout,
                                    registration, password change &mdash; with the user&apos;s IP address and user agent. When a
                                    customer emails &ldquo;someone accessed my account,&rdquo; you need to be able to answer
                                    definitively. An <code>AuditService</code> that dispatches <code>PersistAuditLog</code> jobs
                                    keeps the logging async and out of the request lifecycle.
                                </p>

                                {/* Section 3: Stripe Billing */}
                                <h2 id="stripe-billing">3. Stripe Billing That Won&apos;t Bite You</h2>
                                <p>
                                    This is the section that justifies reading this entire guide.{' '}
                                    <Link href="/features/billing" className="text-primary hover:underline">
                                        Stripe billing
                                    </Link>{' '}
                                    in Laravel looks simple in tutorials: install Cashier, call{' '}
                                    <code>$user-&gt;newSubscription()</code>, done. In production, billing is where the most
                                    money-losing bugs hide.
                                </p>

                                <h3 id="race-condition">The race condition problem</h3>
                                <p>
                                    Picture this: two browser tabs open, both on your pricing page. The user clicks
                                    &ldquo;Subscribe&rdquo; in both tabs within a second. Without concurrency protection, both
                                    requests hit Stripe, both create subscriptions, and your customer is now double-charged.
                                    Laravel Cashier does not protect against this &mdash; it&apos;s your responsibility.
                                </p>
                                <p>
                                    The solution is a Redis lock per user. Before any subscription mutation (create, cancel,
                                    resume, swap), acquire a lock with a 35-second timeout keyed to the user ID. If the lock
                                    is already held, reject the second request immediately with a clear error message. The lock
                                    timeout is set to 35 seconds because Stripe API calls can take up to 30 seconds in worst-case
                                    scenarios, and you need margin for database transactions. Every billing mutation in a
                                    production SaaS should go through a service class that enforces this lock pattern.
                                </p>

                                <h3 id="plan-tiers">Plan tiers and config-driven pricing</h3>
                                <p>
                                    Hardcoding plan prices in controllers is a maintenance nightmare that gets worse with every
                                    pricing change. Put your plans in a config file (<code>config/plans.php</code>) with each
                                    tier defining its price, included features, seat limits, and Stripe price ID. The controller
                                    reads from config, the pricing page reads from config, and the billing service reads from
                                    config. Change a price in one place and it propagates everywhere.
                                </p>
                                <p>
                                    A typical SaaS starts with four tiers: Free (no Stripe subscription, feature limits
                                    enforced in code), Pro (single-user, monthly/annual pricing), Team (seat-based, 3&ndash;50
                                    seats), and Enterprise (custom pricing, contact sales). The config file makes it trivial
                                    to add or modify tiers without touching controller logic.
                                </p>

                                <h3 id="team-seats">Team seat billing</h3>
                                <p>
                                    Seat-based pricing with Stripe is subscription quantity management. The mental model: one
                                    subscription, one subscription item, quantity equals seat count. When a team admin adds a
                                    seat, you call <code>updateQuantity()</code> on the subscription item. Stripe handles
                                    proration automatically.
                                </p>
                                <p>
                                    The gotchas: validate minimum (1) and maximum (50 for a team tier) seat counts before
                                    calling Stripe. The UI should show the current seat count, allow increment/decrement, and
                                    display the prorated cost change before the user confirms. Never let the quantity drop to
                                    zero &mdash; that effectively cancels the subscription in a confusing way.
                                </p>

                                <h3 id="dunning">Dunning &mdash; handling failed payments</h3>
                                <p>
                                    Stripe sends webhook events when payments fail, but many Laravel SaaS apps ignore them.
                                    You need three things: (1) a webhook handler for <code>invoice.payment_failed</code> that
                                    updates the subscription status in your database, (2) a scheduled command that checks for
                                    subscriptions in an <code>incomplete</code> state and sends reminder emails at 1-hour and
                                    12-hour intervals, and (3) a UI that shows the customer their payment has failed and
                                    guides them to update their payment method.
                                </p>
                                <p>
                                    Without dunning, failed payments silently churn customers. With it, you recover a
                                    meaningful percentage of would-be churned revenue. The reminder emails don&apos;t need to be
                                    complex &mdash; a clear subject line (&ldquo;Your payment failed &mdash; update your card
                                    to keep your account active&rdquo;) and a direct link to the billing portal.
                                </p>

                                <h3 id="incomplete-payments">The incomplete payment flow</h3>
                                <p>
                                    Stripe&apos;s PaymentIntent system requires 3D Secure confirmation for many European cards.
                                    If you ignore this, approximately 15% of European customers will fail checkout silently
                                    &mdash; they&apos;ll click &ldquo;Subscribe,&rdquo; the page will appear to succeed, but
                                    no subscription is actually created because the bank required additional authentication
                                    that was never presented.
                                </p>
                                <p>
                                    Handle <code>payment_action_required</code> webhook events. When a subscription enters the
                                    <code>incomplete</code> state, show the customer their subscription status and provide a
                                    &ldquo;Complete payment&rdquo; link that redirects to Stripe&apos;s hosted payment
                                    confirmation page. Cashier provides helper methods for this, but you need to wire them
                                    into your UI and webhook handling.
                                </p>

                                {/* Section 4: Feature Flags */}
                                <h2 id="feature-flags">4. Feature Flags</h2>
                                <p>
                                    <Link href="/features/feature-flags" className="text-primary hover:underline">
                                        Feature flags
                                    </Link>{' '}
                                    are essential infrastructure for any SaaS, not a nice-to-have. They let you deploy code
                                    before it&apos;s ready for all users, give beta testers early access to new features,
                                    disable broken functionality without a deployment, and run controlled rollouts of changes
                                    that affect billing or data.
                                </p>
                                <p>
                                    A practical feature flag system needs two-level resolution: environment variables for
                                    deployment-level defaults (set in <code>.env</code>, never changes at runtime), and
                                    database overrides for runtime changes (a <code>feature_flag_overrides</code> table that
                                    can target specific users). This gives you the simplicity of config-driven flags with
                                    the flexibility of per-user targeting when you need it.
                                </p>
                                <p>
                                    The rule for feature-gated routes: wrap route registration in{' '}
                                    <code>if (config(&apos;features.X.enabled&apos;))</code> in your route files. This means
                                    the route doesn&apos;t exist when the feature is off, returning a proper 404 instead of a
                                    403. Don&apos;t gate routes with middleware alone &mdash; the route still exists and returns
                                    a 403, which leaks the existence of the feature and leaves URL surface area.
                                </p>
                                <p>
                                    The starter kit ships with 11 feature flags covering billing, social auth, email
                                    verification, API tokens, user settings, notifications, onboarding, API docs, two-factor
                                    auth, webhooks, and the{' '}
                                    <Link href="/features/admin-panel" className="text-primary hover:underline">
                                        admin panel
                                    </Link>
                                    . For a typical SaaS launch, you&apos;d enable billing, email verification, API tokens,
                                    and user settings. Add two-factor auth and webhooks as you grow. Keep notifications and
                                    onboarding disabled until you have the content to fill them.
                                </p>

                                {/* Section 5: Admin Panel */}
                                <h2 id="admin-panel">5. Admin Panel &mdash; What You Actually Need</h2>
                                <p>
                                    The{' '}
                                    <Link href="/features/admin-panel" className="text-primary hover:underline">
                                        admin panel
                                    </Link>{' '}
                                    is the tool you&apos;ll use most in the first year of your SaaS &mdash; more than your own
                                    product. Here&apos;s the minimum viable admin panel for an early-stage SaaS, and nothing
                                    more.
                                </p>
                                <p>
                                    <strong>User list with search:</strong> Essential from day one. Customers will email you
                                    about their accounts, and you need to find them fast. Full-text search on name and email,
                                    filters for admin status and verification state, and the ability to impersonate a user to
                                    see exactly what they see. Pagination is non-negotiable &mdash; you&apos;ll have thousands
                                    of users sooner than you think.
                                </p>
                                <p>
                                    <strong>Subscription overview:</strong> Which users are on which plan, when they started,
                                    and whether they have payment issues. This isn&apos;t analytics &mdash; it&apos;s
                                    operational data. When a customer emails &ldquo;my account isn&apos;t working,&rdquo; the
                                    first thing you check is their subscription status.
                                </p>
                                <p>
                                    <strong>Audit log:</strong> For debugging customer-reported issues. &ldquo;I didn&apos;t
                                    change that setting&rdquo; &mdash; check the audit log. &ldquo;Someone accessed my
                                    account&rdquo; &mdash; check the audit log. Every admin action, every authentication
                                    event, every settings change, timestamped with IP and user agent.
                                </p>
                                <p>
                                    <strong>Feature flag UI:</strong> Toggle flags without a deployment. Essential for managing
                                    beta rollouts and for emergency kill switches when a feature causes production issues.
                                    The UI should show current state (environment default vs. database override), allow global
                                    and per-user overrides, and require a reason for every change.
                                </p>
                                <p>
                                    <strong>Health check:</strong> Know your app is running before your customers tell you
                                    it&apos;s down. Database connectivity, cache availability, queue processing, and disk
                                    space &mdash; the basics that break first.
                                </p>
                                <p>
                                    What you don&apos;t need at early stage: analytics dashboards, cohort analysis, revenue
                                    charts, A/B test results. Those come later, when you have enough data to make them useful.
                                    Start with the operational tools that help you support customers and keep the lights on.
                                </p>

                                {/* Section 6: Testing Strategy */}
                                <h2 id="testing-strategy">6. Testing Strategy for a SaaS Codebase</h2>
                                <p>
                                    Testing a SaaS is different from testing a library or a simple CRUD app. You have
                                    authentication, authorization, billing, webhooks, feature flags, and admin functionality
                                    &mdash; each with their own edge cases. Here&apos;s the three-layer testing approach
                                    that catches real bugs without drowning in test maintenance.
                                </p>

                                <h3 id="pest-tests">Pest (PHP) &mdash; behavior tests</h3>
                                <p>
                                    Test what the system <em>does</em>, not how it does it. Every controller action gets a
                                    test. Every billing mutation gets a test. The test should assert on the user-visible
                                    outcome: the redirect destination, the session flash message, and the final database state.
                                    Don&apos;t assert that a mock was called &mdash; assert that the subscription was actually
                                    created in the database.
                                </p>
                                <p>
                                    The edge cases that matter for SaaS: concurrent subscription creation (test the Redis
                                    lock rejection), deactivated users attempting login (test the middleware), unverified users
                                    accessing verified-only routes (test the gate), and feature-flagged routes when the flag is
                                    off (test the 404). Use Pest&apos;s <code>RefreshDatabase</code> trait for isolation and
                                    run tests in parallel for speed.
                                </p>

                                <h3 id="vitest-tests">Vitest (React) &mdash; component tests</h3>
                                <p>
                                    Test user-visible behavior, not implementation details. Does the billing form show the
                                    correct plan name and price? Does the admin table render all expected columns? Does the
                                    loading state appear while the form is submitting? These are the tests that catch
                                    regressions users will actually notice.
                                </p>
                                <p>
                                    Avoid testing internal component state, hook call counts, or specific DOM structure. When
                                    you refactor a component&apos;s internals, its tests should still pass if the user-visible
                                    behavior hasn&apos;t changed. Use <code>@testing-library/react</code> and query by role
                                    and text content, not by CSS class or test ID.
                                </p>

                                <h3 id="phpstan">PHPStan (static analysis)</h3>
                                <p>
                                    Run PHPStan at level 8 with Larastan included. This catches null reference errors, missing
                                    method calls on <code>mixed</code> types, and Eloquent relationship mismatches before they
                                    reach production. The initial setup takes an hour of fixing type annotations, but after
                                    that it prevents an entire class of runtime errors.
                                </p>
                                <p>
                                    The key insight: PHPStan catches bugs that tests miss. A test might not exercise the code
                                    path where a nullable relationship returns <code>null</code>, but PHPStan flags the missing
                                    null check at analysis time. Run it in CI alongside your test suite.
                                </p>

                                <h3 id="skip-early">What to skip at early stage</h3>
                                <p>
                                    <strong>Visual regression testing:</strong> The screenshot comparison tools (Percy,
                                    Chromatic) are expensive to maintain and produce false positives on every CSS change. Skip
                                    until your design system is stable and you have dedicated QA.
                                </p>
                                <p>
                                    <strong>100% code coverage:</strong> Coverage percentage is a vanity metric. A test suite
                                    with 70% coverage and good edge case tests catches more bugs than 100% coverage with
                                    superficial assertions. Focus on behavior coverage, not line coverage.
                                </p>
                                <p>
                                    <strong>Contract testing between frontend and backend:</strong> Inertia handles this
                                    implicitly. Your TypeScript types mirror your PHP controller props. If a controller changes
                                    its response shape, the TypeScript compiler catches the frontend breakage. You don&apos;t
                                    need a separate contract testing tool.
                                </p>

                                {/* Section 7: CI/CD */}
                                <h2 id="ci-cd">7. CI/CD for a Solo Founder or Small Team</h2>
                                <p>
                                    The minimal GitHub Actions pipeline that catches real bugs: PHP tests (Pest with parallel
                                    execution), static analysis (PHPStan), code style (Laravel Pint), JavaScript tests
                                    (Vitest), TypeScript compilation, ESLint, production build verification, and security
                                    audits (<code>composer audit</code> + <code>npm audit</code>). Run on every pull request.
                                    Block merge to main on any gate failure.
                                </p>
                                <p>
                                    For PHP tests, use SQLite in-memory for local development (fast iteration) and MySQL 8.0
                                    in CI (matches production). The test database differences are rare but real &mdash;
                                    SQLite doesn&apos;t enforce foreign key constraints the same way MySQL does, so CI
                                    catches constraint violations that local tests miss. Use PCOV for code coverage
                                    (faster than Xdebug) and run tests with 4 parallel workers.
                                </p>
                                <p>
                                    For deployment, a VPS-based setup (nginx + supervisor for queue workers) is simpler and
                                    cheaper than containerized deployments for most early-stage SaaS products. The starter kit
                                    includes nginx configs with gzip and static asset caching, supervisor configs for queue
                                    workers, and setup scripts that automate the initial server provisioning. Docker adds
                                    complexity that isn&apos;t justified until you need horizontal scaling or multi-environment
                                    parity.
                                </p>

                                {/* Section 8: Starter Kit Decision */}
                                <h2 id="starter-kit-decision">8. The Laravel SaaS Starter Kit Decision</h2>
                                <p>
                                    The honest question: should you use a starter kit or build from scratch? Building auth +
                                    billing + admin + testing + CI from scratch takes 6&ndash;8 weeks for an experienced
                                    developer. A starter kit compresses that to days. The tradeoff is that you inherit someone
                                    else&apos;s architectural decisions &mdash; if those decisions are good and well-documented,
                                    that&apos;s a feature. If they&apos;re opaque or poorly tested, it&apos;s technical debt
                                    from day one.
                                </p>
                                <p>
                                    What to look for in a starter kit: a full test suite (not just &ldquo;tests included&rdquo;
                                    but actually tested edge cases), static analysis (PHPStan at a high level), TypeScript
                                    strict mode (not just &ldquo;TypeScript support&rdquo;), concurrency-safe billing (Redis
                                    locks, not hope), and a frontend stack you actually want to build with for the next
                                    two years.
                                </p>
                                <p>Here&apos;s how the options compare:</p>
                                <ul>
                                    <li>
                                        <strong>
                                            <a href="/compare/laravel-jetstream" className="text-primary hover:underline">
                                                Laravel Jetstream
                                            </a>
                                        </strong>
                                        : Free, official Laravel package. Vue or Livewire frontend. Includes auth and team
                                        management but no billing, no admin panel, and no feature flags. A solid starting point
                                        if you want Vue and don&apos;t need billing.
                                    </li>
                                    <li>
                                        <strong>
                                            <a href="/compare/laravel-spark" className="text-primary hover:underline">
                                                Laravel Spark
                                            </a>
                                        </strong>
                                        : $99/year subscription. Billing-focused with Stripe and Paddle support. No admin panel
                                        or feature flags. Good if billing is your only gap.
                                    </li>
                                    <li>
                                        <strong>
                                            <a href="/compare/saasykit" className="text-primary hover:underline">
                                                SaaSykit
                                            </a>
                                        </strong>
                                        : One-time purchase. Filament-based admin panel, billing, and multi-tenancy. PHP-only
                                        frontend (Livewire + Filament). Choose this if you prefer a PHP-only stack.
                                    </li>
                                    <li>
                                        <strong>This starter kit</strong>: React + TypeScript frontend, Redis-locked billing
                                        with 4 tiers, 11 feature flags, a full admin panel, 90+ automated tests, PHPStan
                                        at level 8. One-time purchase, no subscription. Built for developers who want a
                                        React + Laravel SaaS with production-grade billing from day one.
                                    </li>
                                </ul>

                                {/* Conclusion */}
                                <h2 id="conclusion">Conclusion</h2>
                                <p>
                                    Building a SaaS on Laravel 12 in 2026 means making decisions about frontend architecture,
                                    authentication, billing, feature flags, admin tooling, testing, and deployment. Each
                                    decision has real tradeoffs, and the &ldquo;right&rdquo; answer depends on your team, your
                                    timeline, and your product.
                                </p>
                                <p>
                                    This guide covered the eight decisions that take the most time and cause the most
                                    production bugs: choosing React + TypeScript over Livewire or Vue, building auth that
                                    goes beyond login forms, implementing billing that handles race conditions and failed
                                    payments, using feature flags for safe rollouts, building only the admin tools you
                                    actually need, testing behavior instead of implementation, running CI that catches real
                                    bugs, and knowing when a starter kit saves more time than it costs.
                                </p>
                                <p>
                                    Every pattern described in this guide is implemented, tested, and shipping in the{' '}
                                    <Link href="/" className="text-primary hover:underline">
                                        Laravel React Starter Kit
                                    </Link>
                                    . If you want to skip the implementation work and start building your product instead of
                                    your infrastructure, it&apos;s ready to go.
                                </p>
                            </article>
                        </div>

                        {/* Desktop ToC sidebar */}
                        <aside className="hidden lg:block">
                            <TableOfContents sections={sections} />
                        </aside>
                    </div>

                    {/* CTA */}
                    <div className="mx-auto mt-16 max-w-4xl border-t pt-12 text-center">
                        <h2 className="text-2xl font-bold">Ready to start building?</h2>
                        <p className="mt-2 text-muted-foreground">
                            Skip the 6&ndash;8 weeks of infrastructure work. Get auth, billing, admin, and testing
                            out of the box.
                        </p>
                        <div className="mt-6 flex flex-wrap items-center justify-center gap-4">
                            <Button size="lg" asChild>
                                <Link href="/pricing">
                                    View pricing
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/">Back to overview</Link>
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
