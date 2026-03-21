// Article content is hardcoded as structured JSX (Option A).
// For a multi-article blog, Option B (Markdown files in resources/content/guides/
// parsed by league/commonmark and passed as HTML props) would be preferable.
// Option B would require DOMPurify.sanitize() on all rendered HTML.

// JSON-LD schema strings are NOT passed through DOMPurify — it corrupts structured data.
// (Audit finding SD009: DOMPurify.sanitize() on JSON-LD breaks @context and type values.)

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
    { id: 'what-from-scratch-means', title: '1. What "Building From Scratch" Actually Means', level: 2 },
    { id: 'hidden-costs', title: '2. The Hidden Costs of Building From Scratch', level: 2 },
    { id: 'auth-security', title: 'Auth & Security', level: 3 },
    { id: 'billing', title: 'Billing Integration', level: 3 },
    { id: 'admin-panel', title: 'Admin Panel', level: 3 },
    { id: 'multi-env', title: 'Multi-Environment Configuration', level: 3 },
    { id: 'email-infra', title: 'Email Infrastructure', level: 3 },
    { id: 'testing-setup', title: 'Testing Setup', level: 3 },
    { id: 'typescript-ssr', title: 'TypeScript + Inertia.js SSR', level: 3 },
    { id: 'security-hardening', title: 'Security Hardening', level: 3 },
    { id: 'deployment-infra', title: 'Deployment Infrastructure', level: 3 },
    { id: 'api-webhooks', title: 'API Tokens & Webhooks', level: 3 },
    { id: 'developer-cost', title: '3. What 200–400 Hours of Development Actually Costs', level: 2 },
    { id: 'starter-kit-day-one', title: '4. What a Starter Kit Gives You on Day One', level: 2 },
    { id: 'time-to-market', title: '5. Time-to-Market Comparison', level: 2 },
    { id: 'when-scratch-makes-sense', title: '6. When Building From Scratch Makes Sense', level: 2 },
    { id: 'conclusion', title: 'Conclusion', level: 2 },
    { id: 'faq', title: 'FAQ', level: 2 },
];

interface BuildVsBuyGuideProps extends GuidePageProps {
    appUrl: string;
}

export default function BuildVsBuyGuide({ title, metaDescription, appName, appUrl, breadcrumbs }: BuildVsBuyGuideProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-build-vs-buy' });
    }, [track]);

    // Audit finding SD006: Use Person type (not Organization) for E-E-A-T signals.
    // Audit finding SD005: Article schema must include mainEntityOfPage and wordCount.
    // Do NOT pass these strings through DOMPurify — it corrupts JSON-LD. (SD009)
    const canonicalUrl = `${appUrl}/guides/cost-of-building-saas-from-scratch`;

    const articleSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: title,
        description: metaDescription,
        // SD006: Person, not Organization — Google uses this for E-E-A-T
        author: { '@type': 'Person', name: appName },
        publisher: { '@type': 'Organization', name: appName },
        // Update dateModified when content is revised
        datePublished: '2026-03-20',
        dateModified: '2026-03-20',
        mainEntityOfPage: { '@type': 'WebPage', '@id': canonicalUrl },
        wordCount: 3000,
    });

    const faqSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: [
            {
                '@type': 'Question',
                name: 'How long does it take to build a SaaS from scratch?',
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: 'Building a production-ready SaaS from scratch requires approximately 200–400 hours of senior developer time for infrastructure alone — auth, billing, admin panel, testing, security hardening, and deployment — before you write a single line of business logic.',
                },
            },
            {
                '@type': 'Question',
                name: 'Is it worth buying a SaaS starter kit?',
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: 'For most projects, yes. A starter kit compresses 200–400 hours of infrastructure work into 2–4 hours of configuration. At a freelancer rate of $75–$125/hr, that is $15,000–$50,000 of avoided development cost. The exceptions are projects with highly unusual infrastructure requirements, or teams whose competitive advantage is the infrastructure itself.',
                },
            },
            {
                '@type': 'Question',
                name: 'What does a Laravel SaaS starter kit include?',
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: 'A production-grade Laravel SaaS starter kit includes: authentication (login, register, email verification, 2FA with TOTP, social OAuth), Stripe billing with subscription tiers and dunning, an admin panel with user management and audit logs, a full test suite (Pest, Vitest, Playwright), security hardening (CSP, HSTS, rate limiting), webhooks, API tokens, onboarding flow, and deployment scripts.',
                },
            },
            {
                '@type': 'Question',
                name: 'What is the ROI of a SaaS starter kit?',
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: 'A starter kit saves 185–365 developer hours of infrastructure work. At typical freelancer rates ($75–$150/hr), that is $13,875–$54,750 in avoided cost. Even at an in-house junior developer rate of $50/hr, the savings are $9,250–$18,250. A typical starter kit priced around $189–$299 represents a 46x–290x ROI on developer time alone.',
                },
            },
        ],
    });

    return (
        <>
            {/* Update dateModified when content is revised */}
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <link rel="canonical" href={canonicalUrl} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="article" />
                <meta property="og:url" content={canonicalUrl} />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
                {/* JSON-LD: do NOT sanitize with DOMPurify — it corrupts @context and type values (SD009) */}
                <script
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: articleSchema }}
                />
                <script
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: faqSchema }}
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
                            href="/features/admin-panel"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Admin Panel
                        </Link>
                        <Link
                            href="/pricing"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Pricing
                        </Link>
                        <Button asChild size="sm">
                            <Link href="/pricing">
                                Get the Starter Kit <ArrowRight className="ml-1 h-3 w-3" />
                            </Link>
                        </Button>
                    </div>
                </nav>

                <main className="container pb-24">
                    <header className="mx-auto max-w-4xl py-12 text-center">
                        <p className="mb-4 text-sm font-medium uppercase tracking-wider text-primary">
                            Mid-Funnel Guide &mdash; Build vs Buy
                        </p>
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                            The True Cost of Building a SaaS from Scratch in 2026 (And Why Starter Kits Exist)
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                            Building a production-ready SaaS from scratch takes approximately 200&ndash;400 hours of senior
                            developer time before you write a single line of business logic. A starter kit compresses this to
                            2&ndash;4 hours of configuration. Here&apos;s the breakdown.
                        </p>
                        <div className="mt-4 flex items-center justify-center gap-4 text-sm text-muted-foreground">
                            <time dateTime="2026-03-20">March 20, 2026</time>
                            <span aria-hidden="true">&middot;</span>
                            <span>18 min read</span>
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
                                    Every developer who reaches the &ldquo;should I buy a starter kit?&rdquo; stage has
                                    already done some mental accounting. You know roughly what you&apos;re building. You have a
                                    sense of how long things take. And you&apos;re skeptical of anything that promises to save you
                                    hundreds of hours &mdash; because that&apos;s exactly what every over-engineered tool claims.
                                </p>
                                <p>
                                    This guide isn&apos;t a sales pitch. It&apos;s a time-and-cost audit. We&apos;ll break down
                                    the ten infrastructure areas every SaaS needs, estimate hours honestly, calculate the real
                                    dollar cost at typical developer rates, and then compare that to what a starter kit delivers.
                                    At the end, we&apos;ll tell you the situations where building from scratch is the right call.
                                </p>
                                <p>
                                    Full disclosure: this guide was written by the author of the{' '}
                                    <Link href="/" className="text-primary hover:underline">
                                        Laravel React Starter
                                    </Link>{' '}
                                    kit. The cost estimates below are based on the actual time it took to build each of those
                                    features. The estimates are conservative &mdash; most developers will take longer than the
                                    minimum, not less.
                                </p>

                                {/* Section 1 */}
                                <h2 id="what-from-scratch-means">1. What &ldquo;Building From Scratch&rdquo; Actually Means</h2>
                                <p>
                                    Building from scratch doesn&apos;t mean avoiding all libraries. It means you&apos;re
                                    assembling the infrastructure yourself &mdash; choosing packages, wiring them together,
                                    writing the glue code, handling edge cases, writing tests, and configuring deployment. You
                                    use Laravel Cashier instead of rolling your own Stripe integration, but you still have to
                                    implement subscription lifecycle management, webhooks, dunning, plan limits, and the admin UI
                                    yourself. The packages save you from calling Stripe&apos;s REST API directly. They
                                    don&apos;t save you from the weeks of work that follow.
                                </p>
                                <p>
                                    Here are the ten infrastructure areas every production SaaS needs before it can acquire its
                                    first paying customer:
                                </p>
                                <ol>
                                    <li>Authentication &amp; security (login, register, 2FA, social OAuth, rate limiting)</li>
                                    <li>Billing integration (subscriptions, webhooks, dunning, plan limits)</li>
                                    <li>Admin panel (user management, audit logs, health monitoring, feature flags)</li>
                                    <li>Multi-environment configuration (local, preview, production)</li>
                                    <li>Email infrastructure (queued notifications, transactional templates)</li>
                                    <li>Testing setup (PHP, JavaScript, E2E, CI pipeline)</li>
                                    <li>TypeScript + Inertia.js SSR (type definitions, shared props, build config)</li>
                                    <li>Security hardening (CSP, HSTS, request tracing, security headers)</li>
                                    <li>Deployment infrastructure (nginx, supervisor, queue management, gzip)</li>
                                    <li>API tokens &amp; webhooks (Sanctum UI, HMAC signing, incoming webhook verification)</li>
                                </ol>
                                <p>
                                    None of these are your product. They are the foundation. You must build them before you can
                                    ship the thing that makes you money.
                                </p>

                                {/* Section 2 */}
                                <h2 id="hidden-costs">2. The Hidden Costs of Building From Scratch</h2>
                                <p>
                                    Below are honest hour estimates for each area. &ldquo;Low&rdquo; assumes an experienced
                                    Laravel developer who has done this before. &ldquo;High&rdquo; assumes a competent
                                    developer encountering edge cases they haven&apos;t hit before &mdash; which is most
                                    developers, most of the time.
                                </p>

                                <h3 id="auth-security">1. Authentication &amp; Security &mdash; 20&ndash;40 Hours</h3>
                                <p>
                                    Laravel Breeze gives you login and registration in minutes. What it doesn&apos;t give you:
                                    email verification with queued notifications (the default is synchronous, which blocks the
                                    request), rate limiting tuned for both login attempts and registration abuse, session
                                    regeneration on login to prevent session fixation, TOTP two-factor authentication with
                                    recovery codes, and social OAuth via Google and GitHub with account linking logic.
                                </p>
                                <p>
                                    Two-factor authentication alone is 8&ndash;15 hours of implementation and testing if
                                    you&apos;re using <code>laragear/two-factor</code> (the best available package for Laravel
                                    TOTP). More if you need to handle the edge case where a user is mid-challenge and their
                                    session expires. Social auth adds another 8&ndash;12 hours for OAuth token handling,
                                    account-linking logic (what happens when a GitHub and Google account share an email?), and
                                    testing against mocked OAuth responses.
                                </p>
                                <p>
                                    Add configurable remember-me duration, middleware for email verification enforcement, and
                                    the rate limiting that keeps your auth endpoints off brute-force lists. The authentication
                                    surface alone accounts for 20&ndash;40 hours of careful work.
                                </p>

                                <h3 id="billing">2. Billing Integration (Stripe + Cashier) &mdash; 30&ndash;60 Hours</h3>
                                <p>
                                    Stripe&apos;s documentation makes billing look straightforward. The Cashier package makes it
                                    look even easier. The reality: billing is the highest-risk area in SaaS development because
                                    bugs directly cost money, and the edge cases are endless.
                                </p>
                                <p>
                                    The subscription lifecycle alone (create, cancel, resume, upgrade, downgrade) takes
                                    15&ndash;25 hours to implement correctly. Proration, mid-cycle plan changes, billing
                                    anchor dates &mdash; each of these requires test coverage with mocked Stripe responses.
                                    Then there&apos;s dunning: incomplete payment reminders at 1 hour and 12 hours, a cron job
                                    to detect subscriptions in incomplete state, and queued notifications that retry on failure.
                                </p>
                                <p>
                                    The single most common bug in Laravel billing code: concurrent subscription mutations from
                                    two simultaneous requests (a user double-clicking Upgrade, or a webhook arriving while the
                                    user is changing their plan). Without Redis distributed locks around every subscription
                                    mutation, you&apos;ll create duplicate Stripe subscriptions or corrupt subscription state.
                                    Implementing this correctly takes 4&ndash;8 hours. Discovering and fixing it in production
                                    takes longer.
                                </p>
                                <p>
                                    Seat management for team tiers, plan limit enforcement, billing portal integration, and
                                    webhook signature verification for Stripe events bring the total to 30&ndash;60 hours.
                                </p>

                                <h3 id="admin-panel">3. Admin Panel &mdash; 40&ndash;80 Hours</h3>
                                <p>
                                    &ldquo;Admin panel&rdquo; sounds like a table of users with a delete button. What a
                                    production admin panel actually requires: user search and filtering with pagination, toggle
                                    admin status (with audit log), deactivate and restore accounts, impersonation (the ability
                                    to log in as any user for debugging &mdash; with strict middleware to prevent privilege
                                    escalation), feature flag overrides per user or globally, a billing stats dashboard with
                                    KPI metrics (MRR, churn, trial conversion), an audit log viewer with searchable history, a
                                    health monitoring dashboard (DB/cache/queue/disk), a config viewer for diagnostics, and data
                                    export (CSV).
                                </p>
                                <p>
                                    Building this in React means component-level access control, keyboard shortcuts for power
                                    users, and a responsive layout. The impersonation feature alone requires careful middleware
                                    design &mdash; the stop-impersonation route must not use <code>verified</code> middleware,
                                    because the impersonated user may not be verified. Getting that wrong creates a support
                                    nightmare or a security gap.
                                </p>
                                <p>
                                    Admin panels are also where caching complexity hits hardest. Dashboard stats that hit the
                                    database on every page load don&apos;t scale. You need cache invalidation hooks on every
                                    mutation that affects those stats. That&apos;s 40&ndash;80 hours, and most of it isn&apos;t
                                    glamorous work.
                                </p>

                                <h3 id="multi-env">4. Multi-Environment Configuration &mdash; 10&ndash;20 Hours</h3>
                                <p>
                                    Three environments (local, preview, production) with different mail drivers, analytics
                                    settings, Sentry DSN, CORS origins, queue connections, and debug modes. The configuration
                                    itself isn&apos;t hard. What takes time: making sure feature flags can be toggled per
                                    environment, wiring Sentry for production-only error tracking, configuring trusted proxies
                                    correctly for VPS deployments behind nginx, and ensuring analytics events fire in production
                                    but not in local or test environments. Add queue workers, supervisor configuration, and
                                    environment-specific cache TTLs. 10&ndash;20 hours.
                                </p>

                                <h3 id="email-infra">5. Email Infrastructure &mdash; 10&ndash;20 Hours</h3>
                                <p>
                                    Queued email notifications prevent slow mail sending from blocking request lifecycle.
                                    Setting up the queue listener, configuring Mailpit for local development, building
                                    transactional email templates that look good in Gmail, Outlook, and Apple Mail, and testing
                                    that emails are queued correctly (not sent synchronously) takes time. Add reminders for
                                    failed payments, refund confirmations, and password reset emails. Each notification class
                                    needs a test. 10&ndash;20 hours.
                                </p>

                                <h3 id="testing-setup">6. Testing Setup &mdash; 20&ndash;40 Hours</h3>
                                <p>
                                    A test suite is not optional for a billing product. Bugs in billing code cost real money.
                                    Setting up Pest for PHP behavior tests, Vitest for React component tests, and Playwright
                                    for E2E auth smoke tests takes 5&ndash;10 hours of scaffolding alone. Then come the
                                    factories: every model needs a factory with state methods for common configurations (e.g.,
                                    <code>User::factory()-&gt;admin()-&gt;withSubscription()-&gt;create()</code>). Writing
                                    tests for auth flows, billing lifecycle, admin actions, and webhook handling adds 15&ndash;30
                                    hours of test code. The CI pipeline (GitHub Actions with MySQL, parallel test workers, Pint
                                    linting, PHPStan analysis, and build verification) is another 3&ndash;5 hours to configure
                                    correctly.
                                </p>

                                <h3 id="typescript-ssr">7. TypeScript + Inertia.js SSR &mdash; 15&ndash;30 Hours</h3>
                                <p>
                                    TypeScript in strict mode across a Laravel + Inertia.js codebase requires type definitions
                                    for every Inertia page component, shared props (auth user, feature flags, flash messages),
                                    and Ziggy route types. Getting Vite configured for both development and SSR builds, wiring
                                    the SSR manifest for production, and ensuring that shared props stay typed without leaking
                                    Eloquent model internals requires careful design. The wrong shared prop setup results in
                                    either missing types or serializing entire model graphs over the wire. 15&ndash;30 hours.
                                </p>

                                <h3 id="security-hardening">8. Security Hardening &mdash; 10&ndash;20 Hours</h3>
                                <p>
                                    Content Security Policy headers that don&apos;t break Vite hot-reload in development but
                                    prevent XSS in production. HSTS on production only. Referrer Policy, Permissions Policy,
                                    X-Frame-Options, and X-Content-Type-Options. Request ID middleware that threads a trace ID
                                    through logs and Sentry for request correlation. Rate limit headers on throttled API
                                    responses. CSRF on all state-changing routes. Getting all of this configured correctly,
                                    tested, and applied only where appropriate (some routes like webhook ingestion need CSRF
                                    exemption with signature verification as a substitute) is 10&ndash;20 hours.
                                </p>

                                <h3 id="deployment-infra">9. Deployment Infrastructure &mdash; 15&ndash;30 Hours</h3>
                                <p>
                                    nginx configuration with gzip compression, static asset cache headers, and PHP-FPM
                                    passthrough. Supervisor configuration for queue workers with auto-restart. Deploy scripts
                                    that run <code>php artisan optimize:clear</code>, <code>php artisan queue:restart</code>,
                                    and cache-clearing in the correct order. VPS setup scripts that are idempotent (can be run
                                    again safely). Let&apos;s Encrypt SSL certificate automation. Log rotation. These are not
                                    interesting problems &mdash; they&apos;re configuration work that takes a full week the
                                    first time you do it and 2 hours the second time if you have scripts. 15&ndash;30 hours.
                                </p>

                                <h3 id="api-webhooks">10. API Tokens (Sanctum) + Webhooks &mdash; 15&ndash;25 Hours</h3>
                                <p>
                                    Token management UI (create, list, revoke with confirmation), token scoping, and the
                                    Sanctum middleware configuration. Outgoing webhooks: endpoint management UI, HMAC-SHA256
                                    payload signing, async dispatch via a dedicated job, delivery history with retry logic.
                                    Incoming webhooks: signature verification middleware for GitHub and Stripe, route
                                    registration, handler abstraction. Each of these is a discrete surface with its own test
                                    coverage requirements. 15&ndash;25 hours.
                                </p>

                                {/* Cost Summary Table */}
                                <h3>Total: 185&ndash;365 Hours</h3>
                                <div className="not-prose my-8 overflow-x-auto rounded-lg border border-border">
                                    <table className="w-full text-sm">
                                        <caption className="sr-only">Infrastructure areas and estimated development hours (low and high estimates)</caption>
                                        <thead>
                                            <tr className="border-b border-border bg-muted/50">
                                                <th className="px-4 py-3 text-left font-semibold">Infrastructure Area</th>
                                                <th className="px-4 py-3 text-right font-semibold">Low (hrs)</th>
                                                <th className="px-4 py-3 text-right font-semibold">High (hrs)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {[
                                                ['Authentication & Security', 20, 40],
                                                ['Billing Integration (Stripe + Cashier)', 30, 60],
                                                ['Admin Panel', 40, 80],
                                                ['Multi-Environment Configuration', 10, 20],
                                                ['Email Infrastructure', 10, 20],
                                                ['Testing Setup (Pest, Vitest, Playwright, CI)', 20, 40],
                                                ['TypeScript + Inertia.js SSR', 15, 30],
                                                ['Security Hardening', 10, 20],
                                                ['Deployment Infrastructure', 15, 30],
                                                ['API Tokens & Webhooks', 15, 25],
                                            ].map(([area, low, high]) => (
                                                <tr key={area as string} className="border-b border-border last:border-0">
                                                    <td className="px-4 py-3 text-foreground">{area}</td>
                                                    <td className="px-4 py-3 text-right tabular-nums text-muted-foreground">{low}</td>
                                                    <td className="px-4 py-3 text-right tabular-nums text-muted-foreground">{high}</td>
                                                </tr>
                                            ))}
                                            <tr className="bg-primary/5 font-semibold">
                                                <td className="px-4 py-3 text-foreground">Total</td>
                                                <td className="px-4 py-3 text-right tabular-nums text-primary">185</td>
                                                <td className="px-4 py-3 text-right tabular-nums text-primary">365</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p>
                                    These estimates assume the developer has already decided on the architecture. They
                                    don&apos;t include the time to research packages, make architecture decisions, read
                                    Stripe&apos;s webhook documentation three times, or debug the subtle difference between
                                    <code>subscription-&gt;cancel()</code> and{' '}
                                    <code>subscription-&gt;cancelAt(now()-&gt;endOfMonth())</code>. The real number is higher.
                                </p>

                                {/* Section 3 */}
                                <h2 id="developer-cost">3. What 200&ndash;400 Hours of Development Actually Costs</h2>
                                <p>
                                    Developer time is not free. Here are the real costs at typical market rates:
                                </p>

                                <div className="not-prose my-8 overflow-x-auto rounded-lg border border-border">
                                    <table className="w-full text-sm">
                                        <caption className="sr-only">Developer cost estimates by type, hourly rate, and total hours</caption>
                                        <thead>
                                            <tr className="border-b border-border bg-muted/50">
                                                <th className="px-4 py-3 text-left font-semibold">Developer Type</th>
                                                <th className="px-4 py-3 text-right font-semibold">Hourly Rate</th>
                                                <th className="px-4 py-3 text-right font-semibold">Low (185 hrs)</th>
                                                <th className="px-4 py-3 text-right font-semibold">High (365 hrs)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {[
                                                ['Freelancer (mid-market)', '$75–$125/hr', '$13,875', '$45,625'],
                                                ['Agency', '$150–$250/hr', '$27,750', '$91,250'],
                                                ['In-house Senior Engineer', '$85–$140/hr', '$15,725', '$51,100'],
                                                ['In-house Mid-Level Engineer', '$55–$85/hr', '$10,175', '$31,025'],
                                            ].map(([type, rate, low, high]) => (
                                                <tr key={type as string} className="border-b border-border last:border-0">
                                                    <td className="px-4 py-3 text-foreground">{type}</td>
                                                    <td className="px-4 py-3 text-right tabular-nums text-muted-foreground">{rate}</td>
                                                    <td className="px-4 py-3 text-right tabular-nums text-muted-foreground">{low}</td>
                                                    <td className="px-4 py-3 text-right tabular-nums text-muted-foreground">{high}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <p>
                                    These figures cover only the initial build. They don&apos;t include:
                                </p>
                                <ul>
                                    <li>
                                        <strong>Ongoing maintenance.</strong> Laravel releases a major version each year. Stripe
                                        changes its API. The <code>laragear/two-factor</code> package updates. Each dependency
                                        update requires regression testing. Budget 20&ndash;40 hours per year.
                                    </li>
                                    <li>
                                        <strong>Bug fixes in production.</strong> Billing bugs that surface in production are
                                        time-pressured fixes. A billing bug discovered at 11pm that affects active subscribers
                                        is not a normal debugging session.
                                    </li>
                                    <li>
                                        <strong>Opportunity cost.</strong> Every week building infrastructure is a week not
                                        spent on the product. In competitive markets, this matters.
                                    </li>
                                </ul>
                                <p>
                                    The conservative estimate: <strong>$13,875&ndash;$54,750</strong> to build the infrastructure
                                    you need before you write the first line of your actual product.
                                </p>

                                {/* Section 4 */}
                                <h2 id="starter-kit-day-one">4. What a Starter Kit Gives You on Day One</h2>
                                <p>
                                    The{' '}
                                    <Link href="/" className="text-primary hover:underline">
                                        Laravel React Starter
                                    </Link>{' '}
                                    ships every one of the ten infrastructure areas above, fully implemented, tested, and
                                    documented. Here is exactly what you get:
                                </p>

                                <h3>Authentication</h3>
                                <ul>
                                    <li>Login, register, email verification (queued), password reset</li>
                                    <li>Social OAuth: Google and GitHub with account linking</li>
                                    <li>TOTP two-factor authentication via <code>laragear/two-factor</code> with recovery codes</li>
                                    <li>Session regeneration on login, configurable remember-me duration</li>
                                    <li>Rate limiting: login (10/min, IP+email), registration (5/min), password reset (3/min)</li>
                                </ul>

                                <h3>
                                    <Link href="/features/billing" className="text-primary hover:underline">
                                        Billing
                                    </Link>
                                </h3>
                                <ul>
                                    <li>Stripe Cashier with Redis-locked subscription mutations (prevents concurrent Stripe calls)</li>
                                    <li>Four plan tiers: free, pro, team (3&ndash;50 seats), enterprise</li>
                                    <li>Subscription lifecycle: create, cancel, resume, upgrade, downgrade with proration</li>
                                    <li>Dunning: incomplete payment reminders at 1 hour and 12 hours via queued notifications</li>
                                    <li>Seat management with validation for team/enterprise tiers</li>
                                    <li>Billing portal integration and Stripe webhook signature verification</li>
                                </ul>

                                <h3>
                                    <Link href="/features/admin-panel" className="text-primary hover:underline">
                                        Admin Panel
                                    </Link>
                                </h3>
                                <ul>
                                    <li>User management: search, filter, toggle admin, deactivate/restore, impersonation</li>
                                    <li>Feature flag overrides: global or per-user, with reason tracking and changed_by audit</li>
                                    <li>Billing stats dashboard: MRR, churn, trial conversion, tier distribution, KPI charts</li>
                                    <li>Audit log viewer with searchable history and IP/user agent tracking</li>
                                    <li>Health monitoring: DB, cache, queue, and disk status</li>
                                    <li>Config viewer and data export (CSV)</li>
                                    <li>Keyboard shortcuts for power users</li>
                                </ul>

                                <h3>Developer Experience</h3>
                                <ul>
                                    <li>Pest (PHP) behavior tests, Vitest (React) component tests, Playwright E2E auth smoke tests</li>
                                    <li>PHPStan static analysis, Laravel Pint code style, Husky pre-commit hooks</li>
                                    <li>GitHub Actions CI: MySQL 8.0, 4 parallel test workers, TypeScript build verification</li>
                                    <li>Model factories with state methods for every model</li>
                                </ul>

                                <h3>Infrastructure &amp; Security</h3>
                                <ul>
                                    <li>nginx gzip + static cache configs, supervisor config, VPS setup scripts (<code>scripts/init.sh</code>)</li>
                                    <li>CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer Policy, Permissions Policy</li>
                                    <li>Request ID middleware for distributed tracing, rate limit headers on throttled responses</li>
                                    <li>Sanctum API token management UI, outgoing webhooks with HMAC-SHA256 signing</li>
                                    <li>Incoming webhook verification for GitHub and Stripe</li>
                                </ul>

                                <h3>Extras</h3>
                                <ul>
                                    <li>Onboarding wizard for new users</li>
                                    <li>11 feature flags with DB overrides (per-user and global)</li>
                                    <li>In-app notification system</li>
                                    <li>
                                        <code>audit:prune</code> and <code>webhooks:prune-stale</code> maintenance commands
                                    </li>
                                    <li>Sentry integration (production), Google Analytics (production)</li>
                                </ul>

                                <p>
                                    Configuration time with <code>scripts/init.sh</code>: 2&ndash;4 hours. That&apos;s not
                                    a promise &mdash; it&apos;s what the init script actually does (configure app name, feature
                                    flags, Stripe keys, mail settings, and deploy target). The rest is already built.
                                </p>

                                {/* Section 5 */}
                                <h2 id="time-to-market">5. Time-to-Market Comparison</h2>
                                <p>
                                    Time is not just money &mdash; it&apos;s competitive advantage. Every month you spend
                                    building infrastructure is a month your competitor is acquiring customers.
                                </p>

                                <div className="not-prose my-8 overflow-x-auto rounded-lg border border-border">
                                    <table className="w-full text-sm">
                                        <caption className="sr-only">Time-to-market milestones: building from scratch vs using a starter kit</caption>
                                        <thead>
                                            <tr className="border-b border-border bg-muted/50">
                                                <th className="px-4 py-3 text-left font-semibold">Milestone</th>
                                                <th className="px-4 py-3 text-right font-semibold">Build From Scratch</th>
                                                <th className="px-4 py-3 text-right font-semibold">With Starter Kit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {[
                                                ['Infrastructure complete', '6–12 weeks', '½ day'],
                                                ['First feature built', '8–14 weeks', '1–2 weeks'],
                                                ['Beta ready', '3–5 months', '3–6 weeks'],
                                                ['First paying customer', '4–6 months', '4–8 weeks'],
                                            ].map(([milestone, scratch, kit]) => (
                                                <tr key={milestone as string} className="border-b border-border last:border-0">
                                                    <td className="px-4 py-3 text-foreground">{milestone}</td>
                                                    <td className="px-4 py-3 text-right text-muted-foreground">{scratch}</td>
                                                    <td className="px-4 py-3 text-right font-medium text-primary">{kit}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <p>
                                    At $1,000 MRR (a modest first milestone), every month of delay costs $12,000 in annual
                                    revenue. Four months saved by starting with a starter kit is $4,000 of found revenue in
                                    year one &mdash; compounding in subsequent years as you grow.
                                </p>

                                {/* Section 6 */}
                                <h2 id="when-scratch-makes-sense">6. When Building From Scratch Makes Sense</h2>
                                <p>
                                    There are genuine reasons to build infrastructure from scratch. Here are the ones that
                                    actually hold up:
                                </p>
                                <ol>
                                    <li>
                                        <strong>Your infrastructure is your product.</strong> If you&apos;re building a
                                        developer tool where the auth system, billing engine, or admin panel IS the thing
                                        you&apos;re selling, you need full control and full understanding of every layer.
                                        Starter kits are for SaaS products built on top of infrastructure, not for the
                                        infrastructure itself.
                                    </li>
                                    <li>
                                        <strong>Highly unusual infrastructure requirements.</strong> Real-time collaborative
                                        applications, IoT data pipelines, edge-distributed compute, or multi-tenant
                                        architectures with strict data isolation requirements may genuinely not fit the
                                        single-tenant, queue-based model that most starter kits assume.
                                    </li>
                                    <li>
                                        <strong>Security compliance requires full dependency auditability.</strong> SOC 2 Type
                                        II, FedRAMP, and similar certifications may require that you understand and audit every
                                        dependency. A starter kit that uses packages you haven&apos;t vetted can create
                                        compliance scope. (This matters more in enterprise and government contracts than in most
                                        consumer SaaS.)
                                    </li>
                                    <li>
                                        <strong>Your team&apos;s competitive moat is engineering velocity, not product.</strong>{' '}
                                        Some teams win on technical differentiation. If building a custom billing engine is your
                                        recruiting pitch and your brand identity, that&apos;s a coherent strategy. For everyone
                                        else, it&apos;s a distraction.
                                    </li>
                                </ol>
                                <p>
                                    If none of these apply to you, the math is clear.
                                </p>

                                {/* Conclusion */}
                                <h2 id="conclusion">Conclusion &mdash; The Math Is Straightforward</h2>
                                <p>
                                    Building SaaS infrastructure from scratch costs 185&ndash;365 developer hours. At typical
                                    market rates, that&apos;s $13,875&ndash;$54,750 of developer time before you write a line
                                    of the product that makes you money.
                                </p>
                                <p>
                                    A production-ready starter kit compresses that to 2&ndash;4 hours of configuration. The
                                    ROI is not subtle.
                                </p>
                                <p>
                                    The question isn&apos;t &ldquo;should I pay $189 for a starter kit?&rdquo; The question is
                                    &ldquo;should I spend $189 or $13,875 to get to the same starting point?&rdquo;
                                </p>

                                <div className="not-prose my-8 flex flex-col gap-4 sm:flex-row">
                                    <Button asChild size="lg">
                                        <Link href="/pricing">
                                            See Laravel React Starter pricing <ArrowRight className="ml-2 h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button asChild variant="outline" size="lg">
                                        <Link href="/guides/building-saas-with-laravel-12">
                                            Read the Laravel 12 SaaS guide
                                        </Link>
                                    </Button>
                                </div>

                                {/* FAQ Section */}
                                <h2 id="faq">FAQ</h2>

                                <h3>How long does it take to build a SaaS from scratch?</h3>
                                <p>
                                    Building a production-ready SaaS from scratch requires approximately 200&ndash;400 hours
                                    of senior developer time for infrastructure alone &mdash; auth, billing, admin panel,
                                    testing, security hardening, and deployment &mdash; before you write a single line of
                                    business logic. At a full-time pace (40 hrs/week), that is 5&ndash;10 weeks of work before
                                    your actual product exists.
                                </p>

                                <h3>Is it worth buying a SaaS starter kit?</h3>
                                <p>
                                    For most projects, yes. A starter kit compresses 200&ndash;400 hours of infrastructure work
                                    into 2&ndash;4 hours of configuration. At a freelancer rate of $75&ndash;$125/hr, that is
                                    $15,000&ndash;$50,000 of avoided development cost. The exceptions are projects with highly
                                    unusual infrastructure requirements, or teams whose competitive advantage is the
                                    infrastructure itself.
                                </p>

                                <h3>What does a Laravel SaaS starter kit include?</h3>
                                <p>
                                    A production-grade Laravel SaaS starter kit includes: authentication (login, register,
                                    email verification, 2FA with TOTP and recovery codes, social OAuth via Google and GitHub),
                                    Stripe billing with subscription tiers and dunning, an{' '}
                                    <Link href="/features/admin-panel" className="text-primary hover:underline">
                                        admin panel
                                    </Link>{' '}
                                    with user management and audit logs, a full test suite (Pest, Vitest, Playwright), security
                                    hardening (CSP, HSTS, rate limiting), webhooks, API tokens, onboarding flow, and deployment
                                    scripts.
                                </p>

                                <h3>What is the ROI of a SaaS starter kit?</h3>
                                <p>
                                    A starter kit saves 185&ndash;365 developer hours of infrastructure work. At typical
                                    freelancer rates ($75&ndash;$150/hr), that is $13,875&ndash;$54,750 in avoided cost. Even
                                    at an in-house junior developer rate of $50/hr, the savings are $9,250&ndash;$18,250. A
                                    starter kit priced around $189&ndash;$299 represents a 46x&ndash;290x ROI on developer
                                    time alone &mdash; not counting faster time-to-market.
                                </p>
                            </article>
                        </div>

                        {/* Desktop sticky ToC */}
                        <aside className="hidden lg:block">
                            <div className="sticky top-8">
                                <TableOfContents sections={sections} />
                            </div>
                        </aside>
                    </div>
                </main>

                <footer className="border-t border-border py-12">
                    <div className="container flex flex-col items-center gap-4 text-center text-sm text-muted-foreground">
                        <div className="flex items-center gap-2">
                            <Logo className="h-5 w-5" />
                            <span>{appName}</span>
                        </div>
                        <p>
                            <Link href="/pricing" className="hover:text-foreground hover:underline">
                                Pricing
                            </Link>
                            {' \u00b7 '}
                            <Link href="/features/billing" className="hover:text-foreground hover:underline">
                                Billing Features
                            </Link>
                            {' \u00b7 '}
                            <Link href="/features/admin-panel" className="hover:text-foreground hover:underline">
                                Admin Panel
                            </Link>
                            {' \u00b7 '}
                            <Link href="/guides/building-saas-with-laravel-12" className="hover:text-foreground hover:underline">
                                Laravel 12 SaaS Guide
                            </Link>
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}
