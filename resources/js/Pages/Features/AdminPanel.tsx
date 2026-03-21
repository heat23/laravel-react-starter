import {
    Activity,
    AlertTriangle,
    ArrowRight,
    ClipboardList,
    FileSearch,
    Monitor,
    Server,
    Shield,
    ToggleRight,
    Users,
} from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { FeatureScreenshot } from '@/Components/marketing/FeatureScreenshot';
import { PublicFooter } from '@/Components/marketing/PublicFooter';
import { PublicNav } from '@/Components/marketing/PublicNav';
import { FaqJsonLd } from '@/Components/seo/FaqJsonLd';
import { RelatedContent } from '@/Components/seo/RelatedContent';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { FeaturePageProps } from '@/types/index';

const adminFeatures = [
    {
        icon: Users,
        title: 'User management',
        description:
            'List, search, filter, and export users. Toggle admin status, deactivate accounts, view full profile. CSV export with one click.',
    },
    {
        icon: Activity,
        title: 'Billing dashboard',
        description:
            'MRR trends, plan distribution chart, subscription table with per-subscription actions. Cached at 5 minutes — no N+1 on large datasets.',
    },
    {
        icon: ClipboardList,
        title: 'Audit logs',
        description:
            'Every login, logout, setting change, and billing event with IP address, user agent, and timestamp. Searchable and paginated.',
    },
    {
        icon: ToggleRight,
        title: 'Feature flag UI',
        description:
            'Toggle any of the 11 flags globally or per-user. Add an override with a reason — the audit trail is automatic.',
    },
    {
        icon: Monitor,
        title: 'Health monitoring',
        description:
            'Database, cache, queue, and disk health checks. Returns structured JSON for uptime monitoring integration.',
    },
    {
        icon: FileSearch,
        title: 'Config viewer',
        description:
            'View resolved configuration values without SSH access. Read-only, admin-only, no secrets exposed.',
    },
    {
        icon: AlertTriangle,
        title: 'Failed jobs',
        description:
            'Inspect and retry failed queue jobs from the UI. No Horizon required.',
    },
    {
        icon: Server,
        title: 'System info',
        description:
            'PHP version, Laravel version, disk usage, queue depth — at a glance.',
    },
];

const adminFaqs = [
    {
        question: 'Does this use Filament?',
        answer: 'No. The admin panel is built entirely in React + TypeScript, the same stack as the rest of the app. No PHP/Blade components, no Livewire — one mental model across the whole codebase.',
    },
    {
        question: 'Is the admin panel mobile-responsive?',
        answer: 'Yes. Every admin page uses Tailwind CSS responsive utilities. The user table, audit log, and billing dashboard all adapt to mobile viewports with horizontal scroll on data-dense tables.',
    },
    {
        question: 'How do I restrict admin access?',
        answer: 'Admin routes are protected by the EnsureIsAdmin middleware (aliased as "admin"). Set is_admin=true on a user record. Admin routes don\'t even register when FEATURE_ADMIN=false, so there\'s no attack surface to protect.',
    },
];

export default function AdminPanel({ title, metaDescription, breadcrumbs, canonicalUrl, ogImage, canRegister }: FeaturePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'features-admin-panel' });
    }, [track]);
    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="website" />
                {ogImage && <meta property="og:image" content={ogImage} />}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
                {ogImage && <meta name="twitter:image" content={ogImage} />}
                {canonicalUrl && <link rel="canonical" href={canonicalUrl} />}
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
                <FaqJsonLd questions={adminFaqs} />
            </Head>

            <div className="min-h-screen bg-background">
                <PublicNav currentPath="/features/admin-panel" />

                <main className="container pb-24">
                    <article className="mx-auto max-w-4xl">
                        <header className="py-16 text-center">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                Manage Users, Billing, and Feature Flags From One Dashboard
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Built in the same React + TypeScript stack as your app &mdash; one
                                codebase, one mental model.
                            </p>
                        </header>

                        <FeatureScreenshot
                            src="/images/features/admin-dashboard.svg"
                            alt="Admin dashboard showing stat cards for total users, new signups, and active subscriptions alongside a user signup trend chart and recent audit log activity"
                            caption="Admin dashboard — user metrics, signup trends, and live audit log feed"
                        />

                        <section className="mb-16">
                            <h2 className="mb-8 text-center text-3xl font-bold">
                                What&apos;s in the admin panel
                            </h2>
                            <div className="grid gap-6 md:grid-cols-2">
                                {adminFeatures.map((feature) => (
                                    <div
                                        key={feature.title}
                                        className="rounded-2xl border border-border/70 bg-card p-6 text-card-foreground shadow-sm"
                                    >
                                        <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                            <feature.icon
                                                className="h-5 w-5 text-primary"
                                                aria-hidden="true"
                                            />
                                        </div>
                                        <h3 className="mb-2 text-lg font-semibold">
                                            {feature.title}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            {feature.description}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <FeatureScreenshot
                            src="/images/features/admin-users.svg"
                            alt="User management table showing a list of users with their email, plan, role, and join date, with search and filter controls and CSV export"
                            caption="User management — search, filter, bulk export, and per-user actions"
                            className="mb-16"
                        />

                        {/* Persona section */}
                        <section className="mb-16">
                            <h2 className="mb-6 text-2xl font-bold">Who uses this</h2>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="rounded-2xl border border-border/70 bg-muted/40 p-6">
                                    <p className="font-semibold text-foreground">Solo founder</p>
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        Ship a complete customer dashboard on day one — user lookup,
                                        subscription status, and audit trail — without building a separate
                                        admin tool.
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-muted/40 p-6">
                                    <p className="font-semibold text-foreground">Full-stack developer delivering client projects</p>
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        Hand off a production-ready admin interface in the same React
                                        codebase your client&apos;s team already works in — no separate
                                        PHP framework to learn or maintain.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section className="prose prose-neutral dark:prose-invert mb-16 max-w-none">
                            <h2>Security model</h2>
                            <p>
                                Admin routes are protected by the{' '}
                                <code>EnsureIsAdmin</code> middleware (aliased as{' '}
                                <code>admin</code>). All admin mutations are rate-limited at
                                10 requests per minute. Every admin action — user toggles,
                                deactivations, feature flag changes — is written to the audit
                                log via <code>AuditService</code> with the acting admin&apos;s
                                ID, IP address, and user agent. Admin routes are disabled
                                entirely when <code>FEATURE_ADMIN=false</code> — the routes
                                don&apos;t even register, so there is no attack surface to
                                protect against.
                            </p>
                        </section>

                        <section className="prose prose-neutral dark:prose-invert mb-16 max-w-none">
                            <h2>Built for TypeScript</h2>
                            <p>
                                Every admin page component has typed Inertia props. Every admin
                                API response maps to a TypeScript interface defined in{' '}
                                <code>resources/js/types/admin.ts</code>. PHPStan runs on the
                                PHP side at level 5; TypeScript strict mode runs on the React
                                side. The result: mismatches between what the controller sends
                                and what the component expects are caught at development time,
                                not production. When you add a new column to the users table,
                                the TypeScript compiler tells you which admin components need
                                updating — before your users find out.
                            </p>
                        </section>

                        <section className="mb-16">
                            <h2 className="mb-8 text-3xl font-bold">Common questions</h2>
                            <div className="space-y-6">
                                {adminFaqs.map((faq) => (
                                    <div
                                        key={faq.question}
                                        className="rounded-2xl border border-border/70 bg-card p-6"
                                    >
                                        <h3 className="mb-2 text-lg font-semibold">{faq.question}</h3>
                                        <p className="text-sm text-muted-foreground">{faq.answer}</p>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="flex flex-wrap items-center justify-center gap-4 border-t pt-12">
                            {canRegister && (
                                <Button
                                    size="lg"
                                    asChild
                                    onClick={() =>
                                        track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, {
                                            source: 'feature_page_register',
                                            page: 'admin-panel',
                                        })
                                    }
                                >
                                    <Link href="/register">
                                        Start Building Free
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/pricing">View pricing</Link>
                            </Button>
                            <Button variant="ghost" size="lg" asChild>
                                <Link href="/">Back to overview</Link>
                            </Button>
                        </section>

                        <RelatedContent
                            items={[
                                {
                                    title: 'Compare vs SaaSykit (Filament admin)',
                                    href: '/compare/saasykit',
                                    description: 'React TypeScript admin vs Filament/Livewire',
                                },
                                {
                                    title: 'Compare vs Larafast (Filament admin)',
                                    href: '/compare/larafast',
                                    description: 'Full feature comparison including admin panel',
                                },
                                {
                                    title: 'Production-Grade Stripe Billing',
                                    href: '/features/billing',
                                    description: 'Admin billing dashboard included',
                                },
                            ]}
                        />
                    </article>
                </main>

                <PublicFooter />
            </div>
        </>
    );
}
