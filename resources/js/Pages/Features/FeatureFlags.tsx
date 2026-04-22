import {
    ArrowRight,
    Database,
    Plus,
    Settings,
    ToggleRight,
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

const flags = [
    { flag: 'billing.enabled', env: 'FEATURE_BILLING', description: 'Stripe billing, pricing page, billing portal' },
    { flag: 'admin.enabled', env: 'FEATURE_ADMIN', description: 'Admin panel, user management, health monitoring' },
    { flag: 'notifications.enabled', env: 'FEATURE_NOTIFICATIONS', description: 'In-app notification system' },
    { flag: 'webhooks.enabled', env: 'FEATURE_WEBHOOKS', description: 'Incoming/outgoing webhook management' },
    { flag: 'two_factor.enabled', env: 'FEATURE_TWO_FACTOR', description: 'TOTP 2FA authentication' },
    { flag: 'social_auth.enabled', env: 'FEATURE_SOCIAL_AUTH', description: 'Google + GitHub OAuth' },
    { flag: 'onboarding.enabled', env: 'FEATURE_ONBOARDING', description: 'Welcome wizard for new users' },
    { flag: 'api_tokens.enabled', env: 'FEATURE_API_TOKENS', description: 'Sanctum token management UI' },
    { flag: 'user_settings.enabled', env: 'FEATURE_USER_SETTINGS', description: 'Theme and timezone persistence' },
    { flag: 'email_verification.enabled', env: 'FEATURE_EMAIL_VERIFICATION', description: 'Email verification flow' },
];

const featureFlagFaqs = [
    {
        question: 'Do I need LaunchDarkly or Unleash with this?',
        answer: 'No. This SaaS starter kit ships a complete feature flag system with env-based toggles, database overrides, per-user targeting, and a React admin UI — all without a third-party service subscription.',
    },
    {
        question: 'How do per-user overrides work?',
        answer: 'The FeatureFlagService resolves flags in priority order: per-user database override → global database override → config/features.php default. Store overrides via the admin panel or programmatically via FeatureFlagOverride::create().',
    },
    {
        question: 'Can I add my own feature flags?',
        answer: 'Yes. Add your flag to config/features.php with an env var default, gate the route in routes/web.php, and gate the UI in your React component using the shared features prop. Three steps, no service setup.',
    },
];

export default function FeatureFlags({ title, metaDescription, breadcrumbs, canonicalUrl, ogImage, canRegister }: FeaturePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'features-feature-flags' });
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
                <FaqJsonLd questions={featureFlagFaqs} />
            </Head>

            <div className="min-h-screen bg-background">
                <PublicNav currentPath="/features/feature-flags" />

                <main className="container pb-24">
                    <article className="mx-auto max-w-4xl">
                        <header className="py-16 text-center">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                Build Exactly the SaaS You Need &mdash; Toggle 10 Features On or Off
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Every starter kit gives you everything at once. This one lets you
                                choose. Enable billing, disable webhooks, turn on 2FA &mdash; all
                                with one env var.
                            </p>
                        </header>

                        <FeatureScreenshot
                            src="/images/features/feature-flags-admin.svg"
                            alt="Admin feature flags panel showing 10 flags in a table with toggle switches — billing, admin, two-factor, social auth, and webhooks enabled; notifications and onboarding disabled"
                            caption="Feature flag admin UI — toggle any flag globally, no redeploy required"
                        />

                        <section className="mb-16">
                            <h2 className="mb-8 text-center text-3xl font-bold">
                                The 10 built-in flags
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

                        <section className="mb-16">
                            <h2 className="mb-8 text-3xl font-bold">Common questions</h2>
                            <div className="space-y-6">
                                {featureFlagFaqs.map((faq) => (
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
                                            page: 'feature-flags',
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
                                <Link href="/">See all features</Link>
                            </Button>
                        </section>

                        <RelatedContent
                            items={[
                                {
                                    title: 'Laravel Feature Flags Tutorial',
                                    href: '/guides/laravel-feature-flags-tutorial',
                                    description: 'Runtime toggles without Unleash or LaunchDarkly',
                                },
                                {
                                    title: 'Admin Panel',
                                    href: '/features/admin-panel',
                                    description: 'UI for toggling flags globally or per-user',
                                },
                                {
                                    title: 'Building a SaaS with Laravel 12',
                                    href: '/guides/building-saas-with-laravel-12',
                                    description: 'Complete guide including feature flag architecture',
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
