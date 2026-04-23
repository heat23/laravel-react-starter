import { ArrowRight, Clock, Tag } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import type { BreadcrumbItem } from '@/types';

interface BlogPost {
    title: string;
    slug: string;
    description: string;
    date: string;
    readingTime: string;
    tags: string[];
    href: string;
}

interface BlogIndexProps {
    title: string;
    metaDescription: string;
    canonicalUrl?: string;
    breadcrumbs?: BreadcrumbItem[];
    posts: BlogPost[];
}

export default function BlogIndex({ title, metaDescription, canonicalUrl, breadcrumbs, posts }: BlogIndexProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'blog-index' });
    }, [track]);

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
                        <Link href="/guides" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Guides
                        </Link>
                        <Link href="/features" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Features
                        </Link>
                        <Link href="/pricing" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Pricing
                        </Link>
                    </div>
                </nav>

                <main className="container pb-24">
                    <header className="mx-auto max-w-3xl py-16 text-center">
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                            Laravel SaaS Blog
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                            Practical guides on building SaaS with Laravel 12, React, and TypeScript.
                            Redis billing, feature flags, admin panels, and production deployment.
                        </p>
                    </header>

                    <div className="mx-auto max-w-3xl">
                        {posts.length === 0 ? (
                            <div className="rounded-2xl border border-border/70 bg-card p-12 text-center">
                                <p className="text-muted-foreground">No posts yet. Check back soon.</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {posts.map((post) => (
                                    <Link
                                        key={post.slug}
                                        href={post.href}
                                        className="group block rounded-2xl border border-border/70 bg-card p-6 shadow-sm transition-all hover:border-primary/40 hover:shadow-md"
                                    >
                                        <div className="flex items-start justify-between gap-4">
                                            <div className="flex-1">
                                                <h2 className="mb-2 text-lg font-semibold group-hover:text-primary transition-colors">
                                                    {post.title}
                                                </h2>
                                                {post.description && (
                                                    <p className="mb-3 text-sm text-muted-foreground">{post.description}</p>
                                                )}
                                                <div className="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                                    {post.date && (
                                                        <span>{new Date(post.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                                                    )}
                                                    {post.readingTime && (
                                                        <span className="flex items-center gap-1">
                                                            <Clock className="h-3 w-3" />
                                                            {post.readingTime}
                                                        </span>
                                                    )}
                                                    {post.tags.slice(0, 3).map((tag) => (
                                                        <span key={tag} className="flex items-center gap-1 rounded-md bg-muted px-2 py-0.5">
                                                            <Tag className="h-2.5 w-2.5" />
                                                            {tag}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                            <ArrowRight className="mt-1 h-5 w-5 shrink-0 text-muted-foreground group-hover:text-primary transition-colors" />
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        )}

                        <div className="mt-12 text-center">
                            <p className="text-sm text-muted-foreground">
                                For longer tutorials, see the{' '}
                                <Link href="/guides" className="underline hover:text-foreground transition-colors">
                                    guides section →
                                </Link>
                            </p>
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
                                <Link href="/guides" className="transition-colors hover:text-foreground">Guides</Link>
                                <Link href="/features" className="transition-colors hover:text-foreground">Features</Link>
                                <Link href="/pricing" className="transition-colors hover:text-foreground">Pricing</Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
