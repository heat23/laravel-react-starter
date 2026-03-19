import {
    ArrowRight,
    Database,
    Plus,
    Settings,
    ToggleRight,
} from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { Button } from '@/Components/ui/button';
import type { FeaturePageProps } from '@/types/index';

const flags = [
    { flag: 'billing.enabled', env: 'FEATURE_BILLING', description: 'Stripe billing, pricing page, billing portal' },
    { flag: 'admin.enabled', env: 'FEATURE_ADMIN', description: 'Admin panel, user management, health monitoring' },
    { flag: 'notifications.enabled', env: 'FEATURE_NOTIFICATIONS', description: 'In-app notification system' },
    { flag: 'webhooks.enabled', env: 'FEATURE_WEBHOOKS', description: 'Incoming/outgoing webhook management' },
    { flag: 'two_factor.enabled', env: 'FEATURE_TWO_FACTOR', description: 'TOTP 2FA authentication' },
    { flag: 'social_auth.enabled', env: 'FEATURE_SOCIAL_AUTH', description: 'Google + GitHub OAuth' },
    { flag: 'onboarding.enabled', env: 'FEATURE_ONBOARDING', description: 'Welcome wizard for new users' },
    { flag: 'api_docs.enabled', env: 'FEATURE_API_DOCS', description: 'Scribe interactive API docs' },
    { flag: 'api_tokens.enabled', env: 'FEATURE_API_TOKENS', description: 'Sanctum token management UI' },
    { flag: 'user_settings.enabled', env: 'FEATURE_USER_SETTINGS', description: 'Theme and timezone persistence' },
    { flag: 'email_verification.enabled', env: 'FEATURE_EMAIL_VERIFICATION', description: 'Email verification flow' },
];

export default function FeatureFlags({ title, metaDescription, breadcrumbs }: FeaturePageProps) {
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
                    </div>
                </nav>

                <main className="container pb-24">
                    <article className="mx-auto max-w-4xl">
                        <header className="py-16 text-center">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                Ship Features Safely with Built-in Feature Flags
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Feature flags let you deploy code before it&apos;s ready for
                                all users, run A/B tests on pricing or onboarding flows, give
                                beta users early access, or disable a broken feature in
                                production without a deploy. This starter ships 11 pre-built
                                feature flags covering the major subsystems — billing, admin,
                                notifications, webhooks, 2FA, social auth, onboarding, API
                                docs, and API tokens. Add your own in minutes using the same
                                pattern.
                            </p>
                        </header>

                        <section className="mb-16">
                            <h2 className="mb-8 text-center text-3xl font-bold">
                                The 11 built-in flags
                            </h2>
                            <div className="overflow-x-auto rounded-2xl border border-border/70">
                                <table className="w-full text-left text-sm">
                                    <thead>
                                        <tr className="border-b bg-muted/50">
                                            <th className="px-4 py-3 font-semibold">Flag</th>
                                            <th className="px-4 py-3 font-semibold">Env Var</th>
                                            <th className="px-4 py-3 font-semibold">What it enables</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {flags.map((row) => (
                                            <tr
                                                key={row.flag}
                                                className="border-b last:border-b-0"
                                            >
                                                <td className="px-4 py-3">
                                                    <code className="rounded bg-muted px-1.5 py-0.5 text-xs">
                                                        {row.flag}
                                                    </code>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <code className="rounded bg-muted px-1.5 py-0.5 text-xs">
                                                        {row.env}
                                                    </code>
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {row.description}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section className="mb-16">
                            <h2 className="mb-6 text-3xl font-bold">Database overrides</h2>
                            <div className="grid gap-6 md:grid-cols-3">
                                <div className="rounded-2xl border border-border/70 bg-card p-6 shadow-sm">
                                    <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                        <Database className="h-5 w-5 text-primary" aria-hidden="true" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold">Runtime toggles</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Beyond .env file toggles, each flag can be overridden
                                        per-user or globally at runtime via the
                                        feature_flag_overrides table. The admin panel provides a
                                        UI for this — no database queries by hand.
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-card p-6 shadow-sm">
                                    <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                        <Settings className="h-5 w-5 text-primary" aria-hidden="true" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold">Audit trail</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Overrides include a reason field and a changed_by column
                                        so you always know who toggled what and why. Every change
                                        is tracked automatically.
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-card p-6 shadow-sm">
                                    <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                        <ToggleRight className="h-5 w-5 text-primary" aria-hidden="true" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold">Priority resolution</h3>
                                    <p className="text-sm text-muted-foreground">
                                        The FeatureFlagService resolves flags in priority order:
                                        per-user override &gt; global override &gt;
                                        config/features.php &gt; .env default. The most specific
                                        rule always wins.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section className="prose prose-neutral dark:prose-invert mb-16 max-w-none">
                            <h2>Adding a new flag</h2>
                            <p>
                                Adding a custom feature flag takes three steps. First, add your
                                flag to <code>config/features.php</code> with an env var default:
                            </p>
                            <pre className="rounded-xl bg-muted p-4 text-sm">
                                <code>{`'my_feature' => [
    'enabled' => env('FEATURE_MY_FEATURE', false),
],`}</code>
                            </pre>
                            <p>
                                Second, gate the route in <code>routes/web.php</code>:
                            </p>
                            <pre className="rounded-xl bg-muted p-4 text-sm">
                                <code>{`if (config('features.my_feature.enabled')) {
    Route::get('/my-feature', MyFeatureController::class);
}`}</code>
                            </pre>
                            <p>
                                Third, gate the UI in your React component using the{' '}
                                <code>features</code> shared prop:
                            </p>
                            <pre className="rounded-xl bg-muted p-4 text-sm">
                                <code>{`{features.my_feature && <MyFeatureComponent />}`}</code>
                            </pre>
                            <p>
                                That&apos;s it. The FeatureFlagService handles resolution, the
                                admin UI picks up the new flag automatically, and database
                                overrides work without any additional configuration.
                            </p>
                        </section>

                        <section className="flex flex-wrap items-center justify-center gap-4 border-t pt-12">
                            <Button size="lg" asChild>
                                <Link href="/pricing">
                                    View pricing
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/">See all features</Link>
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
