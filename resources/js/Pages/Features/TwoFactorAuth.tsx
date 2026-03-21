import {
    ArrowRight,
    CheckCircle,
    KeyRound,
    Lock,
    RefreshCw,
    Shield,
    Smartphone,
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

const twoFactorFeatures = [
    {
        icon: Smartphone,
        title: 'TOTP-based (RFC 6238)',
        description: 'Works with any TOTP app: Google Authenticator, Authy, 1Password, Bitwarden. Standard time-based one-time passwords — no proprietary app required.',
    },
    {
        icon: KeyRound,
        title: 'Recovery codes',
        description: 'Eight single-use recovery codes generated on 2FA setup. Users can regenerate codes from the security settings page. Stored hashed in the database.',
    },
    {
        icon: Shield,
        title: 'Challenge on login',
        description: 'When 2FA is enabled, users are redirected to a challenge page after password verification. The challenge accepts either a TOTP code or a recovery code.',
    },
    {
        icon: Lock,
        title: 'Feature-flagged',
        description: 'Enable or disable the entire 2FA system with FEATURE_TWO_FACTOR=true/false. The settings UI, challenge flow, and recovery codes are all gated behind the flag.',
    },
    {
        icon: RefreshCw,
        title: 'Powered by laragear/two-factor',
        description: 'Built on the battle-tested laragear/two-factor package. The TwoFactorAuthentication model stores the TOTP secret and recovery codes.',
    },
    {
        icon: CheckCircle,
        title: 'Full test coverage',
        description: '2FA enable, confirm, disable, recovery code generation, and challenge flows all have Pest test coverage with proper feature-flag assertions.',
    },
];

const faqs = [
    {
        question: 'Does this use SMS-based 2FA?',
        answer: 'No. TOTP-based 2FA (time-based one-time passwords, RFC 6238) is more secure and has no per-message cost. Users authenticate with any TOTP app: Google Authenticator, Authy, 1Password, or Bitwarden.',
    },
    {
        question: 'What happens if a user loses their 2FA device?',
        answer: 'Each user gets 8 single-use recovery codes when they set up 2FA. They can enter any recovery code at the challenge screen. After use, the code is invalidated. Users can regenerate their recovery codes from the security settings page.',
    },
    {
        question: 'Can I make 2FA mandatory for all users?',
        answer: 'The feature ships as optional (user-opt-in). To make it mandatory, add a middleware check after authentication that redirects users without 2FA enabled to the setup page. The middleware hook and the security settings route are both included.',
    },
];

export default function TwoFactorAuth({ title, metaDescription, breadcrumbs, canonicalUrl, ogImage, canRegister }: FeaturePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'features-two-factor-auth' });
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
                        <Link href="/features/admin-panel" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Admin Panel
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
                                TOTP Two-Factor Authentication for Laravel SaaS
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Laravel React Starter ships a complete TOTP 2FA system: setup flow, challenge on login,
                                recovery codes, and a React settings UI — all feature-flagged and fully tested.
                            </p>
                        </header>

                        <section className="mb-16">
                            <h2 className="mb-8 text-3xl font-bold">What's included</h2>
                            <div className="grid gap-6 md:grid-cols-2">
                                {twoFactorFeatures.map((feature) => (
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
                            <h2 className="mb-6 text-3xl font-bold">Enable it with one env var</h2>
                            <div className="prose prose-neutral dark:prose-invert max-w-none">
                                <pre className="rounded-xl bg-muted px-6 py-4 text-sm"><code>FEATURE_TWO_FACTOR=true</code></pre>
                                <p>
                                    When enabled, a <strong>Security</strong> tab appears in user settings. Users can enable TOTP,
                                    scan the QR code with their authenticator app, confirm with a live code, and download recovery codes.
                                    On subsequent logins, they'll be challenged for a code after password verification.
                                </p>
                                <p>
                                    The entire flow uses the <code>laragear/two-factor</code> package for TOTP logic,
                                    with a custom React UI and Laravel controllers for the challenge and settings flows.
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
                                            page: 'two-factor-auth',
                                        })
                                    }
                                >
                                    <Link href="/register">
                                        Get the Starter Kit
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/pricing">See pricing</Link>
                            </Button>
                            <Button variant="ghost" size="lg" asChild>
                                <Link href="/">Back to overview</Link>
                            </Button>
                        </section>

                        <RelatedContent
                            items={[
                                {
                                    title: 'Laravel Two-Factor Authentication Guide',
                                    href: '/guides/laravel-two-factor-authentication',
                                    description: 'Step-by-step 2FA implementation with laragear/two-factor and Pest tests',
                                },
                                {
                                    title: 'Social Auth',
                                    href: '/features/social-auth',
                                    description: 'Google and GitHub OAuth alongside traditional login',
                                },
                                {
                                    title: 'Webhooks',
                                    href: '/features/webhooks',
                                    description: 'HMAC-signed outgoing webhooks and incoming webhook verification',
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
                                <Link href="/features/admin-panel" className="transition-colors hover:text-foreground">Admin Panel</Link>
                                <Link href="/pricing" className="transition-colors hover:text-foreground">Pricing</Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
