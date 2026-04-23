import { ArrowRight, BookOpen, Clock } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import type { BreadcrumbItem } from '@/types';

interface Guide {
    title: string;
    description: string;
    href: string;
    readingTime: string;
}

interface GuidesIndexProps {
    title: string;
    metaDescription: string;
    canonicalUrl?: string;
    ogImage?: string;
    breadcrumbs?: BreadcrumbItem[];
    guides: Guide[];
}

export default function GuidesIndex({ title, metaDescription, canonicalUrl, ogImage, breadcrumbs, guides }: GuidesIndexProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-index' });
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
                        <Link href="/features" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Features
                        </Link>
                        <Link href="/pricing" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Pricing
                        </Link>
                        <Link href="/blog" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Blog
                        </Link>
                    </div>
                </nav>

                <main className="container pb-24">
                    <header className="mx-auto max-w-3xl py-16 text-center">
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                            Laravel SaaS Guides
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                            In-depth tutorials for building production Laravel SaaS: Stripe billing, feature flags,
                            architecture decisions, and honest cost analysis. Free, no signup required.
                        </p>
                    </header>

                    <div className="mx-auto max-w-3xl space-y-4">
                        {guides.map((guide) => (
                            <Link
                                key={guide.href}
                                href={guide.href}
                                className="group flex items-start justify-between gap-4 rounded-2xl border border-border/70 bg-card p-6 shadow-sm transition-all hover:border-primary/40 hover:shadow-md"
                            >
                                <div className="flex items-start gap-4">
                                    <div className="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                                        <BookOpen className="h-5 w-5 text-primary" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <h2 className="mb-1 text-lg font-semibold group-hover:text-primary transition-colors">
                                            {guide.title}
                                        </h2>
                                        <p className="text-sm text-muted-foreground">{guide.description}</p>
                                        <span className="mt-2 inline-flex items-center gap-1 text-xs text-muted-foreground">
                                            <Clock className="h-3 w-3" />
                                            {guide.readingTime}
                                        </span>
                                    </div>
                                </div>
                                <ArrowRight className="mt-2 h-5 w-5 shrink-0 text-muted-foreground group-hover:text-primary transition-colors" />
                            </Link>
                        ))}
                    </div>

                    <div className="mt-12 text-center">
                        <p className="text-sm text-muted-foreground">
                            Also see:{' '}
                            <Link href="/blog" className="underline hover:text-foreground transition-colors">
                                Blog posts
                            </Link>
                            {' and '}
                            <Link href="/compare" className="underline hover:text-foreground transition-colors">
                                Starter kit comparisons →
                            </Link>
                        </p>
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
                                <Link href="/features" className="transition-colors hover:text-foreground">Features</Link>
                                <Link href="/pricing" className="transition-colors hover:text-foreground">Pricing</Link>
                                <Link href="/compare" className="transition-colors hover:text-foreground">Compare</Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
