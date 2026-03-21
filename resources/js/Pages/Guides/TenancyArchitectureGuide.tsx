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
import { FaqJsonLd } from '@/Components/seo/FaqJsonLd';
import { RelatedContent } from '@/Components/seo/RelatedContent';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { GuidePageProps } from '@/types/index';

const faqItems = [
    {
        question: 'Should I build my SaaS as multi-tenant or single-tenant?',
        answer: 'For most early-stage SaaS products, single-tenant architecture is the correct default. It is faster to build, easier to debug, and avoids the 40–80 hours of infrastructure work required to set up a production-grade multi-tenant system. Add multi-tenancy when your business actually requires per-organization data isolation — not before.',
    },
    {
        question: 'Does Laravel support multi-tenancy?',
        answer: 'Yes. The most popular Laravel multi-tenancy package is Tenancy for Laravel (tenancyforlaravel.com), which supports both shared-schema and per-tenant database isolation. Spatie also offers a Laravel multi-tenancy package. Both require significant setup and middleware integration to work correctly in production.',
    },
    {
        question: 'What is the difference between single-tenant and multi-tenant SaaS?',
        answer: 'In a multi-tenant SaaS, multiple customers (tenants) share the same database and application instance, with data isolation enforced at the row or schema level. In a single-tenant SaaS, each customer has their own isolated context — either a separate database or a user-scoped data model with no cross-customer isolation required. Single-tenant is simpler to build and debug; multi-tenant is more cost-efficient at very large scale.',
    },
    {
        question: 'Can I add multi-tenancy to Laravel React Starter later?',
        answer: 'Yes. Laravel React Starter is intentionally single-tenant, but adding multi-tenancy later is possible. You would extract an Organization model, scope all Eloquent queries through the tenant, and integrate a package like Tenancy for Laravel. This is a 2–4 week refactor when your business requirements justify it. The principle is: do not build for 10,000 tenants before you have 10 customers.',
    },
];

const sections: TocSection[] = [
    { id: 'what-is-multi-tenancy', title: '1. What Is Multi-Tenancy?', level: 2 },
    { id: 'what-is-single-tenancy', title: '2. What Is Single-Tenancy?', level: 2 },
    { id: 'comparison-table', title: '3. Side-by-Side Comparison', level: 2 },
    { id: 'hidden-complexity', title: '4. The Hidden Complexity of Multi-Tenancy', level: 2 },
    { id: 'single-tenant-right-choice', title: '5. When Single-Tenant Is the Right Choice', level: 2 },
    { id: 'performance-trade-offs', title: '6. Performance and Complexity Trade-offs', level: 2 },
    { id: 'migration-path', title: '7. Migration Path — From Single to Multi-Tenant', level: 2 },
    { id: 'conclusion', title: 'Conclusion', level: 2 },
];

export default function TenancyArchitectureGuide({ title, metaDescription, appName, breadcrumbs, canonicalUrl, ogImage }: GuidePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-tenancy-architecture' });
    }, [track]);

    // Article JSON-LD: hardcoded static data only — no user input, no sanitization needed.
    // FaqJsonLd component handles FAQ schema via the shared component pattern.
    const articleSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: title,
        description: metaDescription,
        author: {
            '@type': 'Person',
            name: appName,
        },
        publisher: { '@type': 'Organization', name: appName },
        datePublished: '2026-03-20',
        dateModified: '2026-03-20',
        mainEntityOfPage: {
            '@type': 'WebPage',
            '@id': canonicalUrl ?? '/guides/single-tenant-vs-multi-tenant-saas',
        },
        wordCount: 3000,
    });

    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="article" />
                {ogImage && <meta property="og:image" content={ogImage} />}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
                {ogImage && <meta name="twitter:image" content={ogImage} />}
                {canonicalUrl && <link rel="canonical" href={canonicalUrl} />}
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
                <FaqJsonLd questions={faqItems} />
                {/* Article JSON-LD: static hardcoded values only — no user input */}
                <script
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: articleSchema }}
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
                            href="/features/admin-panel"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Admin Panel
                        </Link>
                        <Link
                            href="/guides/building-saas-with-laravel-12"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Laravel SaaS Guide
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
                    <div className="mx-auto max-w-6xl">
                        <header className="py-16">
                            <div className="mx-auto max-w-3xl text-center">
                                <p className="mb-4 text-sm font-medium uppercase tracking-widest text-primary">
                                    Architecture Guide
                                </p>
                                <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                    Single-Tenant vs Multi-Tenant SaaS — When Each Architecture Makes Sense
                                </h1>
                                <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                    For most early-stage SaaS products, single-tenant architecture is faster to
                                    build, easier to debug, and simpler to scale than multi-tenancy. Here&apos;s
                                    when to choose each — and why most SaaS boilerplates pick one and stick with it.
                                </p>
                                <p className="mt-4 text-sm text-muted-foreground">
                                    Published March 20, 2026 &middot; 12 min read
                                </p>
                            </div>
                        </header>

                        <div className="flex flex-col gap-12 lg:flex-row">
                            <aside className="shrink-0 lg:w-64">
                                <div className="sticky top-8">
                                    <TableOfContents sections={sections} />
                                </div>
                            </aside>

                            <article className="prose prose-neutral dark:prose-invert min-w-0 flex-1 max-w-none">

                                <p>
                                    The question comes up constantly: &ldquo;Should my SaaS be multi-tenant?&rdquo;
                                    It&apos;s a loaded question because multi-tenancy sounds like the
                                    &ldquo;professional&rdquo; answer. Slack is multi-tenant. Salesforce is
                                    multi-tenant. Enterprise buyers expect multi-tenancy.
                                </p>
                                <p>
                                    But the framing is wrong. Multi-tenancy is a specific solution to a specific
                                    problem — sharing infrastructure costs across thousands of customers. For most
                                    SaaS products at the stage where they&apos;re choosing a Laravel starter kit,
                                    single-tenant architecture is not a limitation. It&apos;s a deliberate,
                                    pragmatic choice that lets you ship faster, debug easier, and avoid a category
                                    of infrastructure bugs that kill early products.
                                </p>
                                <p>
                                    This guide walks through the honest trade-offs of both approaches. By the end,
                                    you&apos;ll know which architecture fits your current stage — and when it
                                    makes sense to add multi-tenancy.
                                </p>

                                <h2 id="what-is-multi-tenancy">What Is Multi-Tenancy?</h2>

                                <p>
                                    Multi-tenancy means multiple customers (tenants) share the same application
                                    instance and database, with their data separated by logic rather than physical
                                    isolation. The application must enforce that Tenant A never sees Tenant B&apos;s
                                    data.
                                </p>

                                <h3>Two Patterns in Laravel</h3>

                                <p>
                                    <strong>Shared schema (row-level isolation):</strong> All tenants use the same
                                    database tables. Every table has a <code>tenant_id</code> column. Every query
                                    must be scoped to the current tenant. The risk: forget one{' '}
                                    <code>where tenant_id = ?</code> clause and you have a data leak.
                                    <a href="https://tenancyforlaravel.com" rel="noopener noreferrer" target="_blank">
                                        {' '}Tenancy for Laravel
                                    </a>{' '}
                                    handles this automatically via global Eloquent scopes and automatic query
                                    modification.
                                </p>

                                <p>
                                    <strong>Per-tenant database (schema isolation):</strong> Each tenant gets their
                                    own database. Complete data isolation by default. Higher infrastructure cost —
                                    1,000 tenants means 1,000 database connections to manage. Migrations must run
                                    across all tenant databases.
                                </p>

                                <h3>When Multi-Tenancy Wins</h3>

                                <ul>
                                    <li>
                                        <strong>Horizontal SaaS with thousands of SMB customers</strong> — if you
                                        have 5,000 customers on a $49/month plan, shared infrastructure reduces
                                        hosting costs dramatically
                                    </li>
                                    <li>
                                        <strong>Enterprise products with strict data isolation requirements</strong>{' '}
                                        — some enterprise contracts require per-tenant schemas or separate databases
                                    </li>
                                    <li>
                                        <strong>Products like Slack, Notion, or Linear</strong> — every workspace
                                        is a distinct tenant, and the data model is built around tenant isolation
                                        from day one
                                    </li>
                                    <li>
                                        <strong>When your team has the bandwidth to do it correctly</strong> —
                                        multi-tenancy is a significant infrastructure investment, not a configuration
                                        option
                                    </li>
                                </ul>

                                <h3>Real Complexity You&apos;ll Face</h3>

                                <p>
                                    Multi-tenancy introduces complexity that doesn&apos;t disappear after setup.
                                    Every new feature must be built with tenant isolation in mind. Every package
                                    integration must be verified to be tenant-aware. Every migration must be
                                    idempotent across all tenants. Every log line must include tenant context.
                                    This is manageable — Tenancy for Laravel handles much of it — but it
                                    requires ongoing discipline.
                                </p>

                                <h2 id="what-is-single-tenancy">What Is Single-Tenancy?</h2>

                                <p>
                                    Single-tenancy means data isolation is per-user, not per-organization. Each
                                    registered user has their own account, and all data is scoped to that user.
                                    There are no cross-customer boundaries to enforce beyond standard authentication.
                                </p>

                                <h3>How Laravel React Starter Implements It</h3>

                                <p>
                                    Laravel React Starter is single-tenant by deliberate design. There is no
                                    tenant middleware, no tenant-prefixed URLs, and no global tenant scope on
                                    Eloquent models. Data isolation is per-user:
                                </p>

                                <ul>
                                    <li>
                                        Subscriptions belong to <code>users</code> (via Cashier&apos;s{' '}
                                        <code>Billable</code> trait on User)
                                    </li>
                                    <li>
                                        API tokens, webhook endpoints, and settings are all user-scoped
                                    </li>
                                    <li>
                                        Team billing supports 3–50 seats, but the &ldquo;team&rdquo; is still a
                                        single account — not a separate tenant with its own user namespace
                                    </li>
                                    <li>
                                        Audit logs, feature flag overrides, and admin actions all reference the
                                        acting user, not a tenant
                                    </li>
                                </ul>

                                <p>
                                    The CLAUDE.md for this project is explicit: &ldquo;Single-tenant. Do not add
                                    account/org/workspace scoping unless explicitly requested.&rdquo; This is not
                                    an oversight. It&apos;s a product decision.
                                </p>

                                <h3>When Single-Tenancy Is the Right Model</h3>

                                <ul>
                                    <li>
                                        <strong>B2B tools where one person (or a small team) owns the account</strong>{' '}
                                        — project management tools for freelancers, analytics dashboards for founders,
                                        developer tools for indie hackers
                                    </li>
                                    <li>
                                        <strong>Products where data isolation is per-user, not per-organization</strong>{' '}
                                        — personal finance tools, portfolio trackers, content creation apps
                                    </li>
                                    <li>
                                        <strong>Early-stage SaaS before product-market fit</strong> — you will
                                        iterate on the data model constantly. Tenant-scoped queries make that
                                        iteration slower.
                                    </li>
                                </ul>

                                <h2 id="comparison-table">Side-by-Side Comparison</h2>

                                <p>
                                    Here&apos;s how the two architectures compare across the dimensions that matter
                                    most when choosing a Laravel SaaS stack:
                                </p>

                                <div className="not-prose my-8 overflow-x-auto rounded-2xl border border-border/70">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="border-b border-border/70 bg-muted/50">
                                                <th className="px-4 py-3 text-left font-semibold">Dimension</th>
                                                <th className="px-4 py-3 text-left font-semibold">Multi-Tenant</th>
                                                <th className="px-4 py-3 text-left font-semibold">Single-Tenant</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border/50">
                                            <tr>
                                                <td className="px-4 py-3 font-medium">Data isolation model</td>
                                                <td className="px-4 py-3 text-muted-foreground">Row-level or schema-level per tenant</td>
                                                <td className="px-4 py-3 text-muted-foreground">Per-user scoping via authentication</td>
                                            </tr>
                                            <tr className="bg-muted/20">
                                                <td className="px-4 py-3 font-medium">Migration complexity</td>
                                                <td className="px-4 py-3 text-muted-foreground">High — must run across all tenants, must be idempotent</td>
                                                <td className="px-4 py-3 text-muted-foreground">Low — standard Laravel migrations</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-3 font-medium">Debug difficulty</td>
                                                <td className="px-4 py-3 text-muted-foreground">High — logs mixed across tenants, must filter by tenant_id</td>
                                                <td className="px-4 py-3 text-muted-foreground">Low — every log line has a single user context</td>
                                            </tr>
                                            <tr className="bg-muted/20">
                                                <td className="px-4 py-3 font-medium">GDPR/compliance</td>
                                                <td className="px-4 py-3 text-muted-foreground">Complex — data residency per tenant possible but expensive</td>
                                                <td className="px-4 py-3 text-muted-foreground">Simpler — user data export and deletion are user-scoped</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-3 font-medium">Performance at scale</td>
                                                <td className="px-4 py-3 text-muted-foreground">Good with proper indexing on tenant_id; shared schema can bottleneck</td>
                                                <td className="px-4 py-3 text-muted-foreground">Good — no cross-tenant query risk, simpler query patterns</td>
                                            </tr>
                                            <tr className="bg-muted/20">
                                                <td className="px-4 py-3 font-medium">Time to first feature</td>
                                                <td className="px-4 py-3 text-muted-foreground">Slow — tenant infrastructure must be set up first (40–80 hrs)</td>
                                                <td className="px-4 py-3 text-muted-foreground">Fast — standard Laravel CRUD from day one</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-3 font-medium">Cost at 100 customers</td>
                                                <td className="px-4 py-3 text-muted-foreground">Similar — shared infrastructure not yet a meaningful advantage</td>
                                                <td className="px-4 py-3 text-muted-foreground">Similar</td>
                                            </tr>
                                            <tr className="bg-muted/20">
                                                <td className="px-4 py-3 font-medium">Cost at 10,000 customers</td>
                                                <td className="px-4 py-3 text-muted-foreground">Lower — shared infrastructure scales horizontally</td>
                                                <td className="px-4 py-3 text-muted-foreground">Higher if per-user resources needed; comparable for user-scoped model</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-3 font-medium">Laravel packages available</td>
                                                <td className="px-4 py-3 text-muted-foreground">Reduced — not all packages are tenant-aware; Cashier requires tenant-scoping</td>
                                                <td className="px-4 py-3 text-muted-foreground">Full — all packages work without modification</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <h2 id="hidden-complexity">The Hidden Complexity of Multi-Tenancy</h2>

                                <p>
                                    The comparison table above tells part of the story. But the real cost of
                                    multi-tenancy shows up in day-to-day development, not in architecture diagrams.
                                    Here&apos;s what actually slows you down:
                                </p>

                                <h3>The Middleware Chain</h3>

                                <p>
                                    Every request must resolve the current tenant before any application logic
                                    runs. This means identifying the tenant from the subdomain, URL parameter, or
                                    header, looking it up in the database, and binding it to the container. Get
                                    this wrong and you either leak cross-tenant data or block legitimate requests.
                                    Tenancy for Laravel automates this — but you still need to understand it when
                                    debugging routing issues, testing edge cases, and onboarding new team members.
                                </p>

                                <h3>Migration Risk</h3>

                                <p>
                                    With per-tenant databases, running a migration means running it across every
                                    tenant database. If you have 500 tenants, a migration that takes 2 seconds per
                                    database takes 16+ minutes in total. Migrations must be idempotent (safe to
                                    re-run), because a partially-applied migration across tenants creates
                                    inconsistent schema states. Tools exist to manage this, but the operational
                                    complexity is real.
                                </p>

                                <h3>Log Hygiene</h3>

                                <p>
                                    In a shared-schema multi-tenant system, every log line must include
                                    <code>tenant_id</code> context or debugging becomes impossible. When a customer
                                    reports a bug, you need to filter logs by tenant. Without tenant context
                                    baked into your logging configuration from the start, the default Laravel logs
                                    are useless for multi-tenant debugging.
                                </p>

                                <h3>SoftDeletes Complexity</h3>

                                <p>
                                    Soft-deleted records must be tenant-scoped. If a user in Tenant A is soft
                                    deleted, their records should not appear in Tenant B&apos;s queries — even when
                                    using <code>withTrashed()</code>. The tenant scope must stack with the
                                    soft-delete scope. This sounds minor but generates subtle bugs in admin views
                                    and reporting queries.
                                </p>

                                <h3>Package Compatibility</h3>

                                <p>
                                    Not all Laravel packages are tenant-aware. Laravel Cashier, in particular,
                                    stores Stripe customer and subscription data on the User model. In a
                                    multi-tenant setup where billing is per-tenant (not per-user), you need to
                                    attach Cashier to the Tenant model and verify that all Cashier methods work
                                    correctly with the tenant context. There are open issues in the Cashier
                                    repository specifically about this integration.
                                </p>

                                <h3>Time Cost</h3>

                                <p>
                                    Setting up a production-grade multi-tenant system in Laravel correctly —
                                    including tenant resolution, scoped queries, tenant-aware logging, idempotent
                                    migrations, and integration tests that verify isolation — takes 40–80 developer
                                    hours. That&apos;s before you write a single line of business logic.
                                </p>

                                <h2 id="single-tenant-right-choice">When Single-Tenant Is the Right Choice</h2>

                                <p>
                                    Single-tenancy is not a fallback for teams that couldn&apos;t figure out
                                    multi-tenancy. It&apos;s the correct architectural choice in specific situations:
                                </p>

                                <h3>You Are Pre-PMF</h3>

                                <p>
                                    Before product-market fit, your data model will change frequently. You will
                                    add, remove, and rename columns. You will pivot entire features. Tenant-scoped
                                    queries make iteration slower because every schema change must account for
                                    tenant isolation. In a single-tenant model, you run a standard migration and
                                    move on.
                                </p>

                                <h3>Your B2B SaaS Has One Admin Per Account</h3>

                                <p>
                                    Many B2B SaaS products — especially developer tools, analytics dashboards, and
                                    API-first products — have one primary admin per account. The &ldquo;team&rdquo;
                                    is the admin plus a few collaborators with shared access, not a separate
                                    organizational entity requiring its own namespace. A standard user model with
                                    team billing (like Laravel React Starter&apos;s{' '}
                                    <Link href="/features/billing" className="text-primary hover:underline">
                                        3–50 seat team plans
                                    </Link>
                                    ) is sufficient.
                                </p>

                                <h3>Security-by-Simplicity</h3>

                                <p>
                                    Tenant isolation bugs — where Tenant A accidentally sees Tenant B&apos;s data —
                                    are a real attack vector. They happen when a developer forgets to add the tenant
                                    scope to a new query. In a single-tenant model, this category of bug cannot
                                    exist. Authentication is the only isolation mechanism, and Laravel&apos;s auth
                                    system is well-tested and well-understood.
                                </p>

                                <h3>Compliance Is Per-User</h3>

                                <p>
                                    GDPR Article 15 (right of access) and Article 20 (data portability) apply to
                                    individual users, not organizations. If your product&apos;s compliance
                                    requirements are user-scoped — export my data, delete my data — then single-tenant
                                    is actually the simpler compliance model. The personal data export in
                                    Laravel React Starter handles exactly this at{' '}
                                    <code>/export/personal-data</code>.
                                </p>

                                <h3>Your Team Is Small</h3>

                                <p>
                                    Multi-tenancy requires ongoing discipline from every developer who touches the
                                    codebase. For a solo founder or a 2–3 person team, the cognitive overhead of
                                    &ldquo;is this query tenant-scoped?&rdquo; on every feature is a real tax on
                                    development speed. Single-tenancy removes that cognitive load entirely.
                                </p>

                                <h3>You Plan to Add Tenancy Later</h3>

                                <p>
                                    If you genuinely expect to need per-organization data isolation in 12–18 months,
                                    you can build the foundation now without the full multi-tenant infrastructure.
                                    Add an <code>organizations</code> table, scope user relationships through it,
                                    and keep the data model clean. When the time comes to enforce full tenant
                                    isolation, the refactor is scoped rather than total.
                                </p>

                                <h2 id="performance-trade-offs">Performance and Complexity Trade-offs</h2>

                                <h3>Multi-Tenant Performance at Scale</h3>

                                <p>
                                    Shared-schema multi-tenancy performs well when <code>tenant_id</code> columns
                                    are properly indexed. At 10,000+ tenants, the largest tables might have tens of
                                    millions of rows, but with a composite index on <code>(tenant_id, created_at)</code>
                                    or <code>(tenant_id, user_id)</code>, individual tenant queries remain fast.
                                </p>
                                <p>
                                    The performance risk is at the database connection pool level. Every active
                                    tenant needs a connection. In per-tenant database mode, 1,000 concurrent active
                                    tenants means 1,000 database connections. This is manageable with PgBouncer or
                                    a similar connection pooler, but it&apos;s another moving part.
                                </p>

                                <h3>Single-Tenant Performance</h3>

                                <p>
                                    Laravel React Starter enforces explicit query budgets:{' '}
                                    <Link href="/features/admin-panel" className="text-primary hover:underline">
                                        admin panel
                                    </Link>{' '}
                                    requests are capped at 5–8 queries per request, with eager loading required
                                    for all relationship access. Without tenant-scoping complexity, query patterns
                                    are simpler and easier to optimize. The N+1 query problem still exists — but
                                    it&apos;s in one place, not nested inside tenant resolution middleware.
                                </p>
                                <p>
                                    Redis-locked billing mutations (35-second timeout, rejected on lock failure)
                                    ensure that concurrent operations — multiple users updating the same
                                    subscription simultaneously — are handled safely without race conditions. See
                                    the{' '}
                                    <Link
                                        href="/guides/building-saas-with-laravel-12"
                                        className="text-primary hover:underline"
                                    >
                                        full Laravel SaaS architecture guide
                                    </Link>{' '}
                                    for details on the BillingService implementation.
                                </p>

                                <h2 id="migration-path">Migration Path — From Single to Multi-Tenant Later</h2>

                                <p>
                                    If you start single-tenant and need to add multi-tenancy later, the migration
                                    is possible — but it is a significant refactor. Here&apos;s what it involves:
                                </p>

                                <ol>
                                    <li>
                                        <strong>Extract an Organization model.</strong> Add an{' '}
                                        <code>organizations</code> table. Update User to belong to an Organization.
                                        Scope existing user-owned resources through Organization.
                                    </li>
                                    <li>
                                        <strong>Add tenant_id to all relevant tables.</strong> Every table with
                                        user-scoped data needs a <code>tenant_id</code> (or{' '}
                                        <code>organization_id</code>) column with a migration and index.
                                    </li>
                                    <li>
                                        <strong>Add global Eloquent scopes.</strong> Either via Tenancy for Laravel
                                        or manually — every model query must include the tenant scope.
                                    </li>
                                    <li>
                                        <strong>Update billing.</strong> Attach Cashier to the Organization model
                                        instead of User. Migrate existing subscriptions.
                                    </li>
                                    <li>
                                        <strong>Write isolation tests.</strong> Add tests that explicitly verify
                                        Tenant A cannot access Tenant B&apos;s data. These tests will catch the
                                        queries you missed.
                                    </li>
                                </ol>

                                <p>
                                    The total cost: 2–4 weeks of careful refactoring. The principle is not that
                                    you can never add multi-tenancy — it&apos;s that you should not pay that cost
                                    before your business requires it.
                                </p>

                                <blockquote>
                                    <p>
                                        Don&apos;t build for 10,000 tenants before you have 10 customers.
                                    </p>
                                </blockquote>

                                <p>
                                    The most common failure mode in early SaaS is not shipping the wrong
                                    architecture — it&apos;s spending so long on infrastructure that you never find
                                    out if anyone wants the product.
                                </p>

                                <h2 id="conclusion">Conclusion</h2>

                                <p>
                                    Single-tenant architecture is the correct default for most early-stage SaaS
                                    products. It is faster to build, easier to debug, and avoids a category of
                                    isolation bugs that cannot exist in a user-scoped model. Multi-tenancy is the
                                    right choice when you have thousands of SMB customers sharing infrastructure,
                                    when enterprise contracts require per-tenant databases, or when your product&apos;s
                                    core data model is inherently per-organization from day one.
                                </p>

                                <p>
                                    Laravel React Starter is single-tenant by deliberate design. The 11 feature
                                    flags, Redis-locked billing, full{' '}
                                    <Link href="/features/admin-panel" className="text-primary hover:underline">
                                        admin panel
                                    </Link>
                                    , and 90+ automated tests are built for a product team that wants to ship
                                    features — not spend the first month of development wiring up tenant middleware.
                                    When your business requires per-organization isolation, the refactor path is
                                    clear and well-documented.
                                </p>

                                <p>
                                    Read the{' '}
                                    <Link
                                        href="/guides/building-saas-with-laravel-12"
                                        className="text-primary hover:underline"
                                    >
                                        complete guide to building a SaaS with Laravel 12
                                    </Link>{' '}
                                    for the full architecture overview, including auth, billing, feature flags, and
                                    testing strategy.
                                </p>

                                <div className="not-prose mt-12 flex flex-wrap items-center justify-center gap-4 border-t pt-12">
                                    <Button size="lg" asChild>
                                        <Link href="/pricing">
                                            See pricing
                                            <ArrowRight className="ml-2 h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="outline" size="lg" asChild>
                                        <Link href="/guides/building-saas-with-laravel-12">
                                            Full Laravel SaaS Guide
                                        </Link>
                                    </Button>
                                </div>

                                <h2>Frequently Asked Questions</h2>

                                <div className="not-prose space-y-6">
                                    <div className="rounded-2xl border border-border/70 bg-card p-6">
                                        <h3 className="mb-2 text-lg font-semibold">
                                            Should I build my SaaS as multi-tenant or single-tenant?
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            For most early-stage SaaS products, single-tenant is the right default.
                                            It is faster to build, easier to debug, and avoids 40–80 hours of
                                            infrastructure setup. Add multi-tenancy when your business actually
                                            requires per-organization data isolation — not before.
                                        </p>
                                    </div>
                                    <div className="rounded-2xl border border-border/70 bg-card p-6">
                                        <h3 className="mb-2 text-lg font-semibold">
                                            Does Laravel support multi-tenancy?
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            Yes. The most popular solution is Tenancy for Laravel, which supports
                                            shared-schema and per-tenant database isolation. Both require significant
                                            setup and ongoing maintenance to work correctly in production.
                                        </p>
                                    </div>
                                    <div className="rounded-2xl border border-border/70 bg-card p-6">
                                        <h3 className="mb-2 text-lg font-semibold">
                                            What is the difference between single-tenant and multi-tenant SaaS?
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            Multi-tenant SaaS serves multiple customers from a shared database with
                                            logical data isolation. Single-tenant SaaS uses user-scoped data with
                                            authentication as the isolation boundary. Single-tenant is simpler;
                                            multi-tenant is more infrastructure-efficient at very large scale.
                                        </p>
                                    </div>
                                    <div className="rounded-2xl border border-border/70 bg-card p-6">
                                        <h3 className="mb-2 text-lg font-semibold">
                                            Can I add multi-tenancy to Laravel React Starter later?
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            Yes. The refactor involves extracting an Organization model, scoping all
                                            queries through it, and integrating Tenancy for Laravel. This is a 2–4
                                            week effort when your business requirements justify it.
                                        </p>
                                    </div>
                                </div>

                                <RelatedContent
                                    items={[
                                        {
                                            title: 'Complete Guide to Building a SaaS with Laravel 12',
                                            href: '/guides/building-saas-with-laravel-12',
                                            description: 'Auth, billing, feature flags, webhooks, and testing strategy',
                                        },
                                        {
                                            title: 'Admin Panel Feature Overview',
                                            href: '/features/admin-panel',
                                            description: 'User management, billing oversight, audit logs, health monitoring',
                                        },
                                        {
                                            title: 'Webhooks — Outgoing + Incoming',
                                            href: '/features/webhooks',
                                            description: 'HMAC-signed delivery, async dispatch, and Stripe/GitHub verification',
                                        },
                                    ]}
                                />
                            </article>
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
                                    href="/guides/building-saas-with-laravel-12"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Laravel SaaS Guide
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
