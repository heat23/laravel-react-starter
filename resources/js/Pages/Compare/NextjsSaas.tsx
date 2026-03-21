// Article content is hardcoded as structured JSX.
// JSON-LD schema is written as raw JSON — do NOT wrap in DOMPurify.sanitize()
// because the content is authored code, not user input (audit finding SD009).

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
    { id: 'tldr', title: 'TL;DR', level: 2 },
    { id: 'architecture', title: 'Architecture Differences', level: 2 },
    { id: 'developer-experience', title: 'Developer Experience', level: 2 },
    { id: 'performance', title: 'Performance', level: 2 },
    { id: 'ecosystem', title: 'Ecosystem and Libraries', level: 2 },
    { id: 'deployment', title: 'Deployment and Infrastructure', level: 2 },
    { id: 'security', title: 'Security Defaults', level: 2 },
    { id: 'choose-laravel', title: 'When to Choose Laravel for SaaS', level: 2 },
    { id: 'choose-nextjs', title: 'When to Choose Next.js for SaaS', level: 2 },
    { id: 'hybrid', title: 'The Hybrid Approach — Laravel + React', level: 2 },
    { id: 'our-take', title: 'Our Take', level: 2 },
];

export default function NextjsSaas({ title, metaDescription, appName, breadcrumbs }: GuidePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-laravel-vs-nextjs' });
    }, [track]);

    // Derive absolute app URL from the breadcrumb home entry (set server-side from config('app.url')).
    // Avoids SSR mismatch that would occur with window.location.origin.
    const appUrl = breadcrumbs?.[0]?.url ?? '';
    const canonicalUrl = `${appUrl}/compare/laravel-vs-nextjs`;

    const articleSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: title,
        description: metaDescription,
        author: { '@type': 'Person', name: 'Laravel React Starter Team' },
        publisher: { '@type': 'Organization', name: appName },
        datePublished: '2026-03-20',
        dateModified: '2026-03-20',
        wordCount: 3500,
        mainEntityOfPage: {
            '@type': 'WebPage',
            '@id': canonicalUrl,
        },
    });

    const faqSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: [
            {
                '@type': 'Question',
                name: 'Is Laravel faster than Next.js?',
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: 'Neither is universally faster. Laravel with Octane (Swoole or RoadRunner) handles high-concurrency workloads on persistent processes, avoiding PHP bootstrap overhead on every request. Next.js on Vercel edge functions has near-zero cold-start latency for stateless operations. For database-backed SaaS, the bottleneck is almost always query performance, not framework overhead — Eloquent with eager loading and proper indexing is comparable to Prisma in practice.',
                },
            },
            {
                '@type': 'Question',
                name: 'Can you use React with Laravel?',
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: 'Yes. Inertia.js is the standard bridge between Laravel and React. It replaces the need for a separate API layer: Laravel controllers return Inertia responses, React pages receive typed props, and navigation is client-side without a full page reload. Laravel React Starter uses this approach — React 18 + TypeScript on the frontend, Laravel 12 on the backend, with Inertia.js handling the contract between them.',
                },
            },
            {
                '@type': 'Question',
                name: 'Is Next.js better for SaaS than Laravel?',
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: 'It depends on your team. Next.js is better if your team is JavaScript-only, you need Vercel\'s edge network, or you\'re building consumer apps where React Server Components and SSR are central to the product. Laravel is better for B2B SaaS where a mature server-side ecosystem matters: Cashier for Stripe, Horizon for queues, Telescope for debugging, and Pest for expressive testing. The real question is what your team already knows.',
                },
            },
            {
                '@type': 'Question',
                name: 'What is the best Laravel SaaS starter kit?',
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: 'Laravel React Starter is a production-ready starter kit for building SaaS with Laravel 12, React 18, TypeScript, and Tailwind CSS v4. It includes Stripe billing with Redis-locked mutations, a custom admin panel, 11 feature flags with database overrides, HMAC-signed webhooks, TOTP two-factor authentication, audit logging, and 90+ tests across Pest and Vitest — all in a single codebase with no external service dependencies.',
                },
            },
        ],
    });

    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <link rel="canonical" href={canonicalUrl} />
                <meta property="og:url" content={canonicalUrl} />
                <meta property="og:title" content="Laravel vs Next.js for SaaS 2026 — Full Stack Comparison" />
                <meta
                    property="og:description"
                    content="Which is better for SaaS in 2026? Laravel vs Next.js compared on developer experience, performance, ecosystem, and deployment. With starter kit recommendations."
                />
                <meta property="og:type" content="article" />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content="Laravel vs Next.js for SaaS 2026 — Full Stack Comparison" />
                <meta
                    name="twitter:description"
                    content="Which is better for SaaS in 2026? Laravel vs Next.js compared on developer experience, performance, ecosystem, and deployment. With starter kit recommendations."
                />
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
                <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: articleSchema }} />
                <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: faqSchema }} />
            </Head>

            <div className="min-h-screen bg-background">
                {/* Navigation */}
                <nav className="container flex items-center justify-between py-6">
                    <Link href="/" className="flex items-center gap-2">
                        <Logo className="h-8 w-8" />
                        <TextLogo className="text-xl font-bold" />
                    </Link>
                    <div className="flex items-center gap-4">
                        <Link
                            href="/compare"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Compare
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
                    <article className="mx-auto max-w-4xl">
                        {/* Hero */}
                        <header className="py-16 text-center">
                            <div className="mb-4 flex items-center justify-center gap-2 text-sm text-muted-foreground">
                                <Link href="/compare" className="hover:text-foreground transition-colors">
                                    Compare
                                </Link>
                                <span>/</span>
                                <span>Laravel vs Next.js for SaaS</span>
                            </div>
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                Laravel vs Next.js for SaaS in 2026 —<br className="hidden sm:block" /> Which Should You Build On?
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                An opinionated comparison for developers at the framework-selection stage.
                                Architecture, developer experience, deployment, and security — side by side.
                            </p>
                            <p className="mt-3 text-sm text-muted-foreground">
                                Updated March 20, 2026 &middot; ~3,500 words
                            </p>
                        </header>

                        <div className="lg:grid lg:grid-cols-[240px_1fr] lg:gap-12">
                            {/* Table of Contents (sidebar on large screens) */}
                            <aside className="mb-12 lg:mb-0 lg:sticky lg:top-8 lg:self-start">
                                <TableOfContents sections={sections} />
                            </aside>

                            {/* Article Body */}
                            <div className="min-w-0">
                                {/* TL;DR */}
                                <section
                                    id="tldr"
                                    className="mb-12 rounded-2xl border border-border bg-muted/40 p-6"
                                >
                                    <h2 className="text-xl font-bold mb-3">TL;DR</h2>
                                    <ul className="space-y-2 text-sm text-muted-foreground">
                                        <li>
                                            <strong className="text-foreground">Choose Laravel</strong> if you prefer convention-over-configuration, a mature
                                            PHP ecosystem, and need Cashier-grade Stripe integration out of the box.
                                        </li>
                                        <li>
                                            <strong className="text-foreground">Choose Next.js</strong> if your team is JavaScript-only, you need Vercel edge
                                            deployment, or you&apos;re building consumer apps where React Server Components are central.
                                        </li>
                                        <li>
                                            <strong className="text-foreground">Want both?</strong>{' '}
                                            <Link href="/" className="text-primary underline-offset-4 hover:underline">
                                                Laravel React Starter
                                            </Link>{' '}
                                            ships React 18 + TypeScript on the frontend with Laravel 12 on the backend — Inertia.js
                                            handles the bridge without a separate API layer.
                                        </li>
                                    </ul>
                                </section>

                                <div className="prose prose-neutral dark:prose-invert max-w-none">
                                    {/* Architecture */}
                                    <h2 id="architecture">Architecture Differences</h2>

                                    <p>
                                        Laravel and Next.js solve the same problem — building web applications — from opposite
                                        ends of the stack. Understanding the architectural difference is the first step in
                                        picking the right tool.
                                    </p>

                                    <h3>Laravel: MVC + server-side monolith</h3>
                                    <p>
                                        Laravel is a monolith-first MVC framework. Business logic lives in controllers, models,
                                        and services. The request lifecycle is synchronous by default, with jobs and queues
                                        handling async work. Eloquent ORM maps database rows to PHP objects with expressive
                                        relationship definitions. Templates are Blade (server-rendered HTML) or, with Inertia.js,
                                        React components receiving typed PHP props.
                                    </p>
                                    <p>
                                        The monolith-first philosophy means you start with a single deployable unit. Queue
                                        workers, scheduled commands, and the web server all run from the same codebase. This
                                        trades the operational complexity of microservices for the development velocity of a
                                        single-service architecture.
                                    </p>

                                    <h3>Next.js: React-first, edge-ready</h3>
                                    <p>
                                        Next.js is a React framework with a file-based router, API routes, and first-class
                                        support for React Server Components. The architecture is designed around the Vercel
                                        deployment model: serverless functions, edge middleware, and CDN-native caching. Each
                                        API route is an independent function that can run at the edge, close to the user.
                                    </p>
                                    <p>
                                        React Server Components in Next.js 14+ let you fetch data inside a component without
                                        a separate API layer — the component renders on the server and streams HTML to the
                                        client. This is a genuinely different mental model from Laravel&apos;s request-response
                                        cycle.
                                    </p>

                                    <h3>The Inertia.js bridge: React UI + Laravel backend</h3>
                                    <p>
                                        Inertia.js is the third option that most comparisons miss.{' '}
                                        <a href="https://inertiajs.com" target="_blank" rel="noopener noreferrer">
                                            Inertia
                                        </a>{' '}
                                        replaces the API layer between a Laravel backend and a React frontend. Laravel
                                        controllers return <code>Inertia::render()</code> responses with typed props. React
                                        pages receive those props directly — no REST endpoints, no JSON serialization ceremony,
                                        no separate SPA bootstrap. Navigation is client-side (no full page reloads) but data
                                        fetching is always server-side.
                                    </p>

                                    {/*
                                     * Architecture diagram (ASCII):
                                     *
                                     * Next.js:
                                     *   Browser ←→ Next.js Server (React Server Components + API Routes) ←→ DB
                                     *
                                     * Laravel + Inertia:
                                     *   Browser ←→ Inertia (client adapter) ←→ Laravel (controllers + Eloquent) ←→ DB
                                     *
                                     * The key difference: Next.js collapses server and client rendering into one model;
                                     * Laravel + Inertia keeps a hard boundary between backend logic and frontend rendering.
                                     */}

                                    <p>
                                        Laravel React Starter uses this architecture: every page is a React TypeScript component
                                        that receives server-side props from a Laravel controller. There is no separate API
                                        server, no client-side data fetching library, and no GraphQL layer. The SSR path uses
                                        <code>@inertiajs/react</code> with Node.js SSR for search engine rendering.
                                    </p>

                                    {/* Developer Experience */}
                                    <h2 id="developer-experience">Developer Experience</h2>

                                    <h3>Laravel</h3>
                                    <p>
                                        Laravel&apos;s developer experience is built around Artisan CLI and convention-over-configuration.
                                        Running <code>php artisan make:model Post --migration --factory --controller --requests</code>{' '}
                                        generates the scaffolding for a new resource in seconds. Eloquent&apos;s relationship API is
                                        declarative: <code>hasMany</code>, <code>belongsTo</code>, <code>hasManyThrough</code> — with
                                        eager loading that catches N+1 queries at the ORM level.
                                    </p>
                                    <p>
                                        The ecosystem is opinionated in ways that save decisions: Breeze for auth, Cashier for
                                        Stripe, Sanctum for API tokens, Horizon for queue visibility, Telescope for request
                                        introspection, Dusk for browser automation, Pest for tests. Each package has a well-defined
                                        scope and integrates cleanly with the rest. You configure, not assemble.
                                    </p>

                                    <h3>Next.js</h3>
                                    <p>
                                        Next.js developer experience is strong for front-end engineers. Hot module replacement
                                        is fast, the file-based router is intuitive, and the ecosystem around it — shadcn/ui,
                                        Tailwind, Drizzle/Prisma, tRPC — has matured significantly. Vercel&apos;s integration
                                        with GitHub means a production-quality preview deployment on every pull request, which
                                        is genuinely difficult to replicate in a PHP stack.
                                    </p>
                                    <p>
                                        The cost is assembly. Auth (Clerk, NextAuth, Auth.js), billing (Stripe.js + manual
                                        webhook handling), queues (Inngest, Trigger.dev, BullMQ), background jobs, and email
                                        are all separate packages with separate APIs. You are assembling a stack, not
                                        configuring a framework.
                                    </p>

                                    <h3>Prototyping vs scaling</h3>
                                    <p>
                                        For prototyping a CRUD app over a weekend, both are comparable. Next.js gets a small
                                        edge for purely front-end work. Laravel gets the edge once you need billing, queues,
                                        background jobs, or an admin panel — the packages exist, they&apos;re tested, and they
                                        have known failure modes. Scaling a Laravel app on a VPS is well-documented; scaling
                                        a Node.js app on a custom server has more sharp edges.
                                    </p>

                                    {/* Performance */}
                                    <h2 id="performance">Performance</h2>

                                    <p>
                                        Raw framework performance is rarely the bottleneck in a SaaS application. Query
                                        performance, caching strategy, and connection pooling matter far more than PHP vs.
                                        Node.js runtime overhead. That said, architectural trade-offs do affect end-to-end
                                        latency.
                                    </p>

                                    <h3>Laravel with Octane</h3>
                                    <p>
                                        Traditional PHP bootstraps the application on every request — loading autoloader,
                                        service providers, and config on each hit. Laravel Octane eliminates this by keeping
                                        the application bootstrapped in a persistent Swoole or RoadRunner process. Benchmarks
                                        consistently show 5–10x throughput improvements over traditional FPM for CPU-bound
                                        workloads. For database-bound SaaS, the difference is smaller but measurable.
                                    </p>

                                    <h3>Next.js with Vercel edge</h3>
                                    <p>
                                        Vercel&apos;s edge network runs Next.js middleware and lightweight functions in 30+
                                        regions globally. For stateless operations — auth token verification, A/B test routing,
                                        geolocation — edge latency is hard to beat. For database operations, the edge function
                                        still needs to reach a database that is likely in a single region, which often negates
                                        the edge latency advantage.
                                    </p>

                                    <h3>SSR: Inertia.js vs React Server Components</h3>
                                    <p>
                                        Inertia.js SSR renders React components to HTML on the server (via a Node.js process)
                                        and sends the hydration payload to the client. React Server Components in Next.js
                                        stream server-rendered HTML progressively, with fine-grained control over what is
                                        static vs. dynamic. For standard SaaS pages — dashboards, settings, billing — the
                                        difference in user-perceived performance is negligible. For content-heavy public pages,
                                        RSC streaming has a measurable advantage.
                                    </p>

                                    {/* Ecosystem */}
                                    <h2 id="ecosystem">Ecosystem and Libraries</h2>

                                    <h3>Laravel ecosystem</h3>
                                    <p>
                                        The Laravel package ecosystem is deep for backend SaaS primitives:
                                    </p>
                                    <ul>
                                        <li><strong>Cashier</strong> — Stripe and Paddle subscription billing with seat management, trial periods, and webhook handling</li>
                                        <li><strong>Sanctum</strong> — SPA and API token authentication without OAuth complexity</li>
                                        <li><strong>Horizon</strong> — Redis queue monitoring with throughput metrics and failed job visibility</li>
                                        <li><strong>Telescope</strong> — local request introspection: queries, jobs, events, mail, notifications</li>
                                        <li><strong>Dusk</strong> — browser automation and E2E testing without Playwright setup overhead</li>
                                        <li><strong>Pest</strong> — expressive PHP testing with parallel execution, snapshots, and architecture tests</li>
                                    </ul>

                                    <h3>Next.js ecosystem</h3>
                                    <p>
                                        The Next.js ecosystem leans heavily on the npm ecosystem and Vercel integrations:
                                    </p>
                                    <ul>
                                        <li><strong>shadcn/ui</strong> — component library built on Radix UI that ships code you own (no dependency lock-in)</li>
                                        <li><strong>Drizzle / Prisma</strong> — TypeScript-native ORMs with strong type inference</li>
                                        <li><strong>Clerk / Auth.js</strong> — authentication providers with varying levels of vendor lock-in</li>
                                        <li><strong>Stripe.js + webhooks</strong> — billing must be assembled manually from the Stripe SDK</li>
                                        <li><strong>Vercel AI SDK</strong> — streaming AI integrations for LLM-backed features</li>
                                    </ul>

                                    <h3>The Inertia advantage: React UI with Laravel&apos;s backend ecosystem</h3>
                                    <p>
                                        Laravel React Starter gives you React 18 + TypeScript + Tailwind CSS v4 on the frontend
                                        (shadcn/ui components included) while keeping Cashier, Sanctum, Horizon, and Pest on
                                        the backend. You do not have to choose between a modern React UI and a mature backend
                                        ecosystem — Inertia.js makes both available in the same codebase.
                                    </p>

                                    {/* Deployment */}
                                    <h2 id="deployment">Deployment and Infrastructure</h2>

                                    <h3>Laravel deployment</h3>
                                    <p>
                                        Laravel is designed for VPS deployment. Laravel Forge and Ploi provision and deploy
                                        Laravel apps to any cloud provider (DigitalOcean, AWS, Hetzner) with nginx, PHP-FPM,
                                        MySQL, Redis, and queue worker supervision configured out of the box. Laravel React
                                        Starter ships with <code>deploy/</code> configs: nginx gzip and static cache rules,
                                        supervisor config for queue workers, and <code>scripts/vps-setup.sh</code> for first-time
                                        server provisioning. Docker support is possible but not the default.
                                    </p>
                                    <p>
                                        The trade-off: you manage a server. No cold starts, predictable performance, and full
                                        control over the infrastructure — but you pay for uptime whether or not the app is
                                        receiving traffic.
                                    </p>

                                    <h3>Next.js deployment</h3>
                                    <p>
                                        Vercel is the native deployment target for Next.js. <code>git push</code> deploys to
                                        production; pull requests get preview URLs; edge functions deploy globally with zero
                                        configuration. Railway, Netlify, and AWS Amplify also support Next.js with varying
                                        levels of friction.
                                    </p>
                                    <p>
                                        Serverless deployment eliminates server management but introduces cold starts for
                                        infrequently-visited routes. For database-backed SaaS, connection pooling (PgBouncer
                                        or Prisma Data Proxy) is required to avoid exhausting database connections on cold
                                        start bursts. This is a non-trivial production concern.
                                    </p>

                                    {/* Security */}
                                    <h2 id="security">Security Defaults</h2>

                                    <h3>Laravel</h3>
                                    <p>
                                        Laravel ships with CSRF protection on all state-changing routes (enabled by default,
                                        cannot accidentally disable), bcrypt/argon2 password hashing, encrypted session storage,
                                        and rate limiting via <code>RateLimiter</code> with named limiters per route group.
                                        Sanctum handles token revocation and token expiry. The framework does not expose
                                        raw SQL errors, stack traces, or internal configuration to HTTP responses.
                                    </p>

                                    <h3>Next.js</h3>
                                    <p>
                                        Next.js does not ship CSRF protection — it must be added via middleware or a library.
                                        Rate limiting requires an external service (Upstash Redis, Vercel Rate Limit) or
                                        manual middleware implementation. Auth security depends entirely on the chosen auth
                                        library (Clerk, Auth.js, or custom).
                                    </p>

                                    <h3>What this starter ships</h3>
                                    <p>
                                        Laravel React Starter includes a complete security infrastructure out of the box:
                                    </p>
                                    <ul>
                                        <li>CSP headers via <code>SecurityHeaders</code> middleware (configurable in <code>config/security.php</code>)</li>
                                        <li>HSTS in production (Strict-Transport-Security with preload)</li>
                                        <li>X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy</li>
                                        <li>Rate limiting on all auth endpoints: registration (5/min), login (10/min), password reset (3/min)</li>
                                        <li>Request tracing via <code>RequestIdMiddleware</code> — X-Request-Id on every request, shared with Sentry</li>
                                        <li>Audit logging with IP and user agent for login, logout, and registration</li>
                                        <li>HMAC-SHA256 webhook signature verification for all incoming webhooks</li>
                                        <li>TOTP two-factor authentication via <code>laragear/two-factor</code></li>
                                    </ul>
                                    <p>
                                        None of this requires configuration — it is the baseline, not an add-on.
                                    </p>

                                    {/* When to choose Laravel */}
                                    <h2 id="choose-laravel">When to Choose Laravel for SaaS</h2>

                                    <p>Choose Laravel if any of the following apply:</p>
                                    <ul>
                                        <li>
                                            <strong>Your team knows PHP.</strong> Switching ecosystems mid-product is expensive.
                                            If your developers think in Eloquent and Artisan, the productivity advantage of
                                            staying in Laravel outweighs any marginal Next.js benefits.
                                        </li>
                                        <li>
                                            <strong>You need Cashier-grade Stripe integration.</strong> Cashier handles
                                            subscriptions, seat management, trial periods, payment method updates, invoice
                                            retrieval, and webhook verification — all in one tested package.{' '}
                                            <Link href="/features/billing" className="text-primary underline-offset-4 hover:underline">
                                                See how the billing layer works in this starter.
                                            </Link>
                                        </li>
                                        <li>
                                            <strong>You want full test coverage without configuration.</strong> Pest&apos;s
                                            expressive test syntax, parallel execution, and architecture tests work out of
                                            the box. This starter ships 90+ Pest + Vitest tests covering billing, auth,
                                            admin, webhooks, and feature flags.
                                        </li>
                                        <li>
                                            <strong>Single-server deployment is preferred.</strong> No cold starts, no
                                            connection pool management, predictable memory usage. Supervisor keeps queue
                                            workers alive; cron handles scheduled commands. You own the machine.
                                        </li>
                                        <li>
                                            <strong>You need an admin panel, webhooks, 2FA, and audit logging without plugins.</strong>{' '}
                                            This starter ships all four. The admin panel is a custom React + TypeScript UI
                                            with user management, billing stats, audit log, feature flag overrides, and
                                            health monitoring.
                                        </li>
                                        <li>
                                            <strong>You are building B2B SaaS where reliability matters more than edge performance.</strong>{' '}
                                            B2B users are in known regions, use the app during business hours, and care about
                                            data integrity and uptime — not cold-start latency. Laravel&apos;s traditional
                                            server model is a better fit than serverless functions for this workload.
                                        </li>
                                    </ul>

                                    {/* When to choose Next.js */}
                                    <h2 id="choose-nextjs">When to Choose Next.js for SaaS</h2>

                                    <p>Choose Next.js if any of the following apply:</p>
                                    <ul>
                                        <li>
                                            <strong>Your team is JavaScript-only.</strong> If nobody on the team writes PHP,
                                            adding Laravel is adding a language switch, a runtime switch, and a deployment
                                            switch simultaneously. The productivity cost is real.
                                        </li>
                                        <li>
                                            <strong>You need Vercel&apos;s global edge network.</strong> For globally-distributed
                                            consumer apps where latency is a differentiator and your users are worldwide,
                                            Vercel&apos;s edge deployment has a genuine architectural advantage.
                                        </li>
                                        <li>
                                            <strong>You are building consumer apps where SSR + SEO via React Server Components is critical.</strong>{' '}
                                            News sites, content platforms, marketplaces — any app where public page SEO and
                                            first-load performance are primary concerns benefits from RSC streaming.
                                        </li>
                                        <li>
                                            <strong>You want the Next.js/Vercel ecosystem.</strong> The Vercel AI SDK,
                                            v0 component generation, and the Next.js community around AI-assisted development
                                            workflows are real advantages if AI-native features are core to your product.
                                        </li>
                                        <li>
                                            <strong>You need a Next.js-based starter today.</strong> If you need a battle-tested
                                            Next.js SaaS starter,{' '}
                                            <Link href="/compare/shipfast" className="text-primary underline-offset-4 hover:underline">
                                                Shipfast is the most popular option
                                            </Link>{' '}
                                            — see our comparison for the trade-offs.
                                        </li>
                                    </ul>

                                    {/* Hybrid approach */}
                                    <h2 id="hybrid">The Hybrid Approach — Laravel Backend + React Frontend</h2>

                                    <p>
                                        The false dichotomy in "Laravel vs. Next.js" is the assumption that choosing Laravel
                                        means giving up React. It does not. Inertia.js is the bridge that lets you build a
                                        React + TypeScript frontend with a Laravel backend — without a separate API server,
                                        without REST endpoint maintenance, and without the two-runtime deployment complexity
                                        of a decoupled SPA.
                                    </p>

                                    <h3>How Inertia.js works</h3>
                                    <p>
                                        When a user navigates to a page, Inertia intercepts the request client-side and
                                        makes an XHR to the Laravel backend. The controller returns a JSON response with
                                        the component name and props. Inertia swaps the React component client-side without
                                        a full page reload. From the backend perspective, it looks like a regular Laravel
                                        controller returning data. From the frontend perspective, it looks like a React SPA
                                        with typed props arriving from a server.
                                    </p>

                                    <h3>This project&apos;s architecture</h3>
                                    <p>
                                        Laravel React Starter implements this pattern with:
                                    </p>
                                    <ul>
                                        <li><strong>React 18 + TypeScript</strong> — all frontend components are fully typed</li>
                                        <li><strong>Tailwind CSS v4</strong> — semantic color tokens for dark mode, no hardcoded hex values</li>
                                        <li><strong>Laravel 12</strong> — controllers, Eloquent, queues, Cashier, Sanctum</li>
                                        <li><strong>Inertia.js with SSR</strong> — server-side rendered on first load, client-side thereafter</li>
                                        <li><strong>No separate API</strong> — Inertia props replace REST endpoints for all page data</li>
                                        <li><strong>API routes still exist</strong> — Sanctum-protected <code>/api/*</code> routes for third-party integrations</li>
                                    </ul>
                                    <p>
                                        The result: you write React components that receive PHP-backed data, with full TypeScript
                                        inference on both sides. You ship one codebase, one deployment, one test suite.
                                    </p>

                                    {/* Our Take */}
                                    <h2 id="our-take">Our Take</h2>

                                    <p>
                                        For most B2B SaaS products, Laravel is the more pragmatic choice in 2026 — not
                                        because PHP is superior to JavaScript, but because the Laravel ecosystem has solved
                                        the hard problems of SaaS (billing, queues, admin, testing) in a more integrated
                                        way than the Next.js ecosystem has. The assembly cost of a Next.js SaaS stack
                                        is real, even if individual packages are excellent.
                                    </p>
                                    <p>
                                        The best argument for Next.js is team familiarity. If your developers think in React
                                        Server Components and deploy to Vercel by default, the switching cost to Laravel
                                        is not worth the ecosystem gain. Framework selection is always a people problem,
                                        not a technology problem.
                                    </p>
                                    <p>
                                        If you&apos;ve landed on Laravel — or want to evaluate what a production Laravel
                                        SaaS stack looks like — start with Laravel React Starter. It implements the
                                        patterns described in this guide:{' '}
                                        <Link href="/compare" className="text-primary underline-offset-4 hover:underline">
                                            see how it compares to other starters
                                        </Link>
                                        , or{' '}
                                        <Link href="/pricing" className="text-primary underline-offset-4 hover:underline font-semibold">
                                            view pricing and get started
                                        </Link>
                                        .
                                    </p>
                                </div>

                                {/* FAQ */}
                                <section className="mt-16 space-y-6">
                                    <h2 className="text-2xl font-bold">Frequently Asked Questions</h2>

                                    <details className="rounded-lg border border-border p-5">
                                        <summary className="cursor-pointer font-semibold">
                                            Is Laravel faster than Next.js?
                                        </summary>
                                        <p className="mt-3 text-muted-foreground text-sm leading-relaxed">
                                            Neither is universally faster. Laravel with Octane (Swoole or RoadRunner) handles
                                            high-concurrency workloads on persistent processes, avoiding PHP bootstrap overhead
                                            on every request. Next.js on Vercel edge has near-zero cold-start latency for
                                            stateless operations. For database-backed SaaS, the bottleneck is almost always
                                            query performance — Eloquent with eager loading and proper indexing is comparable
                                            to Prisma in practice.
                                        </p>
                                    </details>

                                    <details className="rounded-lg border border-border p-5">
                                        <summary className="cursor-pointer font-semibold">
                                            Can you use React with Laravel?
                                        </summary>
                                        <p className="mt-3 text-muted-foreground text-sm leading-relaxed">
                                            Yes. Inertia.js is the standard bridge between Laravel and React. Controllers
                                            return <code>Inertia::render()</code> responses, React pages receive typed props,
                                            and navigation is client-side without a full page reload — no separate API layer
                                            required. Laravel React Starter uses this approach with React 18 + TypeScript and
                                            Laravel 12.
                                        </p>
                                    </details>

                                    <details className="rounded-lg border border-border p-5">
                                        <summary className="cursor-pointer font-semibold">
                                            Is Next.js better for SaaS than Laravel?
                                        </summary>
                                        <p className="mt-3 text-muted-foreground text-sm leading-relaxed">
                                            It depends on your team and product. Next.js wins if you are JavaScript-only or
                                            need Vercel&apos;s edge network. Laravel wins for B2B SaaS where a mature backend
                                            ecosystem (Cashier, Horizon, Pest) matters more than edge deployment. The real
                                            question is what your team already knows and what your users actually need.
                                        </p>
                                    </details>

                                    <details className="rounded-lg border border-border p-5">
                                        <summary className="cursor-pointer font-semibold">
                                            What is the best Laravel SaaS starter kit?
                                        </summary>
                                        <p className="mt-3 text-muted-foreground text-sm leading-relaxed">
                                            Laravel React Starter is a production-ready starter with React 18 + TypeScript,
                                            Laravel 12, Stripe billing (Redis-locked), a custom admin panel, 11 feature flags,
                                            HMAC-signed webhooks, TOTP 2FA, audit logging, and 90+ Pest + Vitest tests —
                                            all in a single codebase.{' '}
                                            <Link href="/compare" className="text-primary underline-offset-4 hover:underline">
                                                Compare it to other starters.
                                            </Link>
                                        </p>
                                    </details>
                                </section>

                                {/* Related Links */}
                                <section className="mt-12 rounded-2xl border border-border bg-muted/30 p-6">
                                    <h3 className="font-semibold mb-3">Related comparisons</h3>
                                    <ul className="space-y-2 text-sm">
                                        <li>
                                            <Link
                                                href="/compare/shipfast"
                                                className="text-primary underline-offset-4 hover:underline"
                                            >
                                                Laravel React Starter vs Shipfast (Next.js)
                                            </Link>
                                            {' '}— product-level comparison with feature table
                                        </li>
                                        <li>
                                            <Link
                                                href="/guides/building-saas-with-laravel-12"
                                                className="text-primary underline-offset-4 hover:underline"
                                            >
                                                Complete Guide to Building a SaaS with Laravel 12
                                            </Link>
                                            {' '}— deep dive on auth, billing, admin, and testing
                                        </li>
                                        <li>
                                            <Link
                                                href="/features/billing"
                                                className="text-primary underline-offset-4 hover:underline"
                                            >
                                                Laravel Stripe Billing — how this starter handles subscriptions
                                            </Link>
                                        </li>
                                        {/* /compare hub link — wire up after session 01 (compare hub) is merged */}
                                        <li>
                                            <Link
                                                href="/compare"
                                                className="text-primary underline-offset-4 hover:underline"
                                            >
                                                Compare all Laravel SaaS starters
                                            </Link>
                                            {' '}— hub page with all comparisons
                                        </li>
                                    </ul>
                                </section>

                                {/* CTA */}
                                <section className="mt-12 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
                                    <h2 className="text-2xl font-bold">Build your SaaS on Laravel</h2>
                                    <p className="mt-2 text-muted-foreground">
                                        React 18 + TypeScript frontend. Laravel 12 backend. Production-ready from day one.
                                    </p>
                                    <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                                        <Button asChild size="lg">
                                            <Link href="/pricing">
                                                View pricing
                                                <ArrowRight className="ml-2 h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button asChild variant="outline" size="lg">
                                            <Link href="/compare">Compare starters</Link>
                                        </Button>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </article>
                </main>

                {/* Footer */}
                <footer className="border-t py-8">
                    <div className="container">
                        <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                            <p className="text-sm text-muted-foreground">
                                &copy; {new Date().getFullYear()} Laravel React Starter. All rights reserved.
                            </p>
                            <nav className="flex items-center gap-4 text-sm text-muted-foreground">
                                <Link href="/terms" className="hover:text-foreground transition-colors">
                                    Terms
                                </Link>
                                <Link href="/privacy" className="hover:text-foreground transition-colors">
                                    Privacy
                                </Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
