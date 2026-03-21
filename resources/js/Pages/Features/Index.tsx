import {
    ArrowRight,
    CreditCard,
    KeyRound,
    LayoutDashboard,
    Shield,
    ToggleLeft,
    Users,
} from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { BreadcrumbItem } from '@/types';

interface FeaturesIndexProps {
    title: string;
    metaDescription: string;
    canonicalUrl?: string;
    ogImage?: string;
    canRegister?: boolean;
    breadcrumbs?: BreadcrumbItem[];
    features: Array<{
        title: string;
        description: string;
        href: string;
    }>;
}

const featureIcons: Record<string, React.ComponentType<{ className?: string }>> = {
    '/features/billing': CreditCard,
    '/features/feature-flags': ToggleLeft,
    '/features/admin-panel': LayoutDashboard,
    '/features/webhooks': ArrowRight,
    '/features/two-factor-auth': Shield,
    '/features/social-auth': Users,
};

export default function FeaturesIndex({ title, metaDescription, canonicalUrl, ogImage, canRegister, breadcrumbs, features }: FeaturesIndexProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'features-index' });
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
            </Head>

            <div className="min-h-screen bg-background">
                <nav className="container flex items-center justify-between py-6">
                    <Link href="/" className="flex items-center gap-2">
                        <Logo className="h-8 w-8" />
                        <TextLogo className="text-xl font-bold" />
                    </Link>
                    <div className="flex items-center gap-4">
                        <Link href="/pricing" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Pricing
                        </Link>
                        {canRegister && (
                            <Button size="sm" asChild>
                                <Link href="/register">Get Started</Link>
                            </Button>
                        )}
                    </div>
                </nav>

                <main className="container pb-24">
                    <header className="mx-auto max-w-3xl py-16 text-center">
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                            Everything you need to ship a production Laravel SaaS
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                            Laravel React Starter includes billing, feature flags, a full admin panel, webhooks, 2FA,
                            and social auth — all feature-flagged, fully tested, and ready to customize.
                        </p>
                    </header>

                    <div className="mx-auto max-w-5xl">
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {features.map((feature) => {
                                const Icon = featureIcons[feature.href] ?? KeyRound;
                                return (
                                    <Link
                                        key={feature.href}
                                        href={feature.href}
                                        className="group rounded-2xl border border-border/70 bg-card p-6 shadow-sm transition-all hover:border-primary/40 hover:shadow-md"
                                    >
                                        <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                            <Icon className="h-5 w-5 text-primary" aria-hidden="true" />
                                        </div>
                                        <h2 className="mb-2 text-lg font-semibold group-hover:text-primary transition-colors">
                                            {feature.title}
                                        </h2>
                                        <p className="text-sm text-muted-foreground">{feature.description}</p>
                                        <span className="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary">
                                            Learn more <ArrowRight className="h-3.5 w-3.5" />
                                        </span>
                                    </Link>
                                );
                            })}
                        </div>

                        {canRegister && (
                            <div className="mt-16 text-center">
                                <Button size="lg" asChild>
                                    <Link href="/register">
                                        Get the Starter Kit
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                                <p className="mt-3 text-sm text-muted-foreground">
                                    One-time purchase · Full source code ·{' '}
                                    <Link href="/pricing" className="underline hover:text-foreground transition-colors">
                                        See pricing
                                    </Link>
                                </p>
                            </div>
                        )}
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
                                <Link href="/pricing" className="transition-colors hover:text-foreground">Pricing</Link>
                                <Link href="/compare" className="transition-colors hover:text-foreground">Compare</Link>
                                <Link href="/guides" className="transition-colors hover:text-foreground">Guides</Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
