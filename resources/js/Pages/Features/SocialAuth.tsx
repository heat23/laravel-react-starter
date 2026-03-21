import {
    ArrowRight,
    CheckCircle,
    Github,
    Link2,
    RefreshCw,
    Shield,
    UserPlus,
    Zap,
} from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { FaqJsonLd } from '@/Components/seo/FaqJsonLd';
import { RelatedContent } from '@/Components/seo/RelatedContent';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { FeaturePageProps } from '@/types/index';

const socialAuthFeatures = [
    {
        icon: Github,
        title: 'Google + GitHub OAuth',
        description: 'Both providers ship out of the box. Auto-detected from environment variables — if GOOGLE_CLIENT_ID is set, the Google button appears. No code changes needed.',
    },
    {
        icon: UserPlus,
        title: 'Auto account creation',
        description: 'First OAuth login creates a new account automatically. Returning users are matched by email — if an account exists, the OAuth provider is linked to it.',
    },
    {
        icon: Link2,
        title: 'Multiple providers per account',
        description: 'Users can link both Google and GitHub to one account. The social_accounts table stores provider tokens and user IDs. Providers can be unlinked from settings.',
    },
    {
        icon: Shield,
        title: 'Email verification bypass',
        description: 'OAuth logins mark the user\'s email as verified automatically — no verification email sent for OAuth-created accounts. Email-password accounts still require verification.',
    },
    {
        icon: RefreshCw,
        title: 'Session data migration',
        description: 'Guest session data (cart, preferences, onboarding state) is migrated to the authenticated user on OAuth login via SessionDataMigrationService.',
    },
    {
        icon: Zap,
        title: 'Auto-detected from env',
        description: 'Social auth buttons only appear when provider credentials are configured. Set GOOGLE_CLIENT_ID + GOOGLE_CLIENT_SECRET to enable Google. Same for GitHub.',
    },
    {
        icon: CheckCircle,
        title: 'Full test coverage',
        description: 'OAuth callback handling, account creation, account linking, and provider detection all have Pest test coverage via SocialAuthService mocks.',
    },
];

const faqs = [
    {
        question: 'Which OAuth providers are supported?',
        answer: 'Google and GitHub ship by default. Adding another provider (LinkedIn, Twitter, etc.) takes about 30 minutes: add the Socialite driver, create client credentials in your provider\'s developer console, and add the env vars.',
    },
    {
        question: 'What if the user already has an account with the same email?',
        answer: 'The OAuth provider is linked to the existing account automatically. The user can then log in with either their password or the OAuth provider. They can manage linked providers from their account settings.',
    },
    {
        question: 'Is social auth mandatory?',
        answer: 'No. Social auth is auto-detected from environment variables — if neither GOOGLE_CLIENT_ID nor GITHUB_CLIENT_ID is set, no social login buttons appear anywhere. Traditional email+password auth always works.',
    },
];

export default function SocialAuth({ title, metaDescription, breadcrumbs, canonicalUrl, ogImage, canRegister }: FeaturePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'features-social-auth' });
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
                <FaqJsonLd questions={faqs} />
            </Head>

            <div className="min-h-screen bg-background">
                <nav className="container flex items-center justify-between py-6">
                    <Link href="/" className="flex items-center gap-2">
                        <Logo className="h-8 w-8" />
                        <TextLogo className="text-xl font-bold" />
                    </Link>
                    <div className="flex items-center gap-4">
                        <Link href="/features/billing" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Billing
                        </Link>
                        <Link href="/features/two-factor-auth" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Two-Factor Auth
                        </Link>
                        <Link href="/pricing" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Pricing
                        </Link>
                    </div>
                </nav>

                <main className="container pb-24">
                    <article className="mx-auto max-w-4xl">
                        <header className="py-16 text-center">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                Google &amp; GitHub OAuth for Laravel SaaS
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Laravel React Starter ships social auth out of the box: auto-detected from env vars,
                                account linking, session migration, and full Pest test coverage.
                            </p>
                        </header>

                        <section className="mb-16">
                            <h2 className="mb-8 text-3xl font-bold">What's included</h2>
                            <div className="grid gap-6 md:grid-cols-2">
                                {socialAuthFeatures.map((feature) => (
                                    <div
                                        key={feature.title}
                                        className="rounded-2xl border border-border/70 bg-card p-6 text-card-foreground shadow-sm"
                                    >
                                        <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                            <feature.icon className="h-5 w-5 text-primary" aria-hidden="true" />
                                        </div>
                                        <h3 className="mb-2 text-lg font-semibold">{feature.title}</h3>
                                        <p className="text-sm text-muted-foreground">{feature.description}</p>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="mb-16">
                            <h2 className="mb-6 text-3xl font-bold">Enable with env vars</h2>
                            <div className="prose prose-neutral dark:prose-invert max-w-none">
                                <pre className="rounded-xl bg-muted px-6 py-4 text-sm"><code>{`# Google OAuth
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret

# GitHub OAuth
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret`}</code></pre>
                                <p>
                                    Social login buttons appear automatically when credentials are set. No code changes required.
                                    The <code>SocialAuthService</code> handles OAuth callbacks, account creation,
                                    and provider linking in a single, testable service class.
                                </p>
                            </div>
                        </section>

                        <section className="mb-16">
                            <h2 className="mb-8 text-3xl font-bold">Common questions</h2>
                            <div className="space-y-6">
                                {faqs.map((faq) => (
                                    <div key={faq.question} className="rounded-2xl border border-border/70 bg-card p-6">
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
                                            page: 'social-auth',
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
                                    title: 'Two-Factor Authentication',
                                    href: '/features/two-factor-auth',
                                    description: 'TOTP 2FA with recovery codes and feature-flag gating',
                                },
                                {
                                    title: 'Laravel SaaS Architecture Guide',
                                    href: '/guides/building-saas-with-laravel-12',
                                    description: 'Full overview: auth, billing, feature flags, and testing',
                                },
                                {
                                    title: 'Compare vs Jetstream',
                                    href: '/compare/laravel-jetstream',
                                    description: 'Jetstream has social auth via Socialite too — see all differences',
                                },
                            ]}
                        />
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
                                <Link href="/features/billing" className="transition-colors hover:text-foreground">Billing</Link>
                                <Link href="/features/two-factor-auth" className="transition-colors hover:text-foreground">Two-Factor Auth</Link>
                                <Link href="/pricing" className="transition-colors hover:text-foreground">Pricing</Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
