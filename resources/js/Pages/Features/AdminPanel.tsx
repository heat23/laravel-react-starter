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

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
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

export default function AdminPanel({ title, metaDescription, breadcrumbs }: FeaturePageProps) {
    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="website" />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
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
                    <article className="mx-auto max-w-4xl">
                        <header className="py-16 text-center">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                A Full Admin Panel, Built in React + TypeScript
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Filament is a great admin framework — but it&apos;s a separate
                                stack from your React frontend. Every Filament resource is
                                PHP/Blade/Livewire. This starter&apos;s admin panel is written
                                in the same React + TypeScript stack as the rest of the app.
                                Your IDE&apos;s TypeScript language server covers the admin
                                panel. Your Vitest tests cover the admin components. One stack,
                                one mental model.
                            </p>
                        </header>

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

                        <section className="flex flex-wrap items-center justify-center gap-4 border-t pt-12">
                            <Button size="lg" asChild>
                                <Link href="/pricing">
                                    See pricing
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/">Back to overview</Link>
                            </Button>
                        </section>
                    </article>
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
