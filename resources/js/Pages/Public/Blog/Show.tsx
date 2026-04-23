import { ArrowLeft, Clock, Tag } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import DOMPurify from 'dompurify';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { RelatedContent } from '@/Components/seo/RelatedContent';
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
    content: string;
}

interface BlogShowProps {
    title: string;
    metaDescription: string;
    canonicalUrl?: string;
    ogImage?: string;
    breadcrumbs?: BreadcrumbItem[];
    post: BlogPost;
}

export default function BlogShow({ title, metaDescription, canonicalUrl, ogImage, breadcrumbs, post }: BlogShowProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'blog-post', label: post.slug });
    }, [track, post.slug]);

    const formattedDate = post.date
        ? new Date(post.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
        : null;

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
                {canonicalUrl && post.date && (
                    <script type="application/ld+json">{JSON.stringify({
                        '@context': 'https://schema.org',
                        '@type': 'BlogPosting',
                        headline: post.title,
                        description: post.description,
                        url: canonicalUrl,
                        datePublished: post.date,
                        author: { '@type': 'Organization', name: import.meta.env.VITE_APP_NAME || 'Laravel React Starter' },
                    })}</script>
                )}
            </Head>

            <div className="min-h-screen bg-background">
                <nav className="container flex items-center justify-between py-6">
                    <Link href="/" className="flex items-center gap-2">
                        <Logo className="h-8 w-8" />
                        <TextLogo className="text-xl font-bold" />
                    </Link>
                    <div className="flex items-center gap-4">
                        <Link href="/blog" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Blog
                        </Link>
                        <Link href="/guides" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Guides
                        </Link>
                        <Link href="/pricing" className="text-sm text-muted-foreground transition-colors hover:text-foreground">
                            Pricing
                        </Link>
                    </div>
                </nav>

                <main className="container pb-24">
                    <article className="mx-auto max-w-3xl">
                        <Link
                            href="/blog"
                            className="mb-8 inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <ArrowLeft className="h-3.5 w-3.5" />
                            Back to blog
                        </Link>

                        <header className="py-10">
                            <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">{post.title}</h1>
                            <div className="mt-4 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                {formattedDate && <span>{formattedDate}</span>}
                                {post.readingTime && (
                                    <span className="flex items-center gap-1">
                                        <Clock className="h-3.5 w-3.5" />
                                        {post.readingTime}
                                    </span>
                                )}
                                {post.tags.map((tag) => (
                                    <span key={tag} className="flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-xs">
                                        <Tag className="h-3 w-3" />
                                        {tag}
                                    </span>
                                ))}
                            </div>
                        </header>

                        <div
                            className="prose prose-neutral dark:prose-invert max-w-none"
                            // Content is sanitized server-side via htmlspecialchars() then reconstructed;
                            // additionally sanitized client-side with DOMPurify for defense in depth.
                            // NOTE: 'class' attr is allowed because blog content is admin-controlled
                            // (Markdown files in resources/content/blog/), never user-submitted.
                            // If this ever accepts user-generated content, remove 'class' from ALLOWED_ATTR.
                            dangerouslySetInnerHTML={{
                                __html: DOMPurify.sanitize(post.content, {
                                    ALLOWED_TAGS: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'code', 'pre', 'a', 'ul', 'ol', 'li', 'blockquote', 'hr', 'br'],
                                    ALLOWED_ATTR: ['href', 'title', 'class'],
                                }),
                            }}
                        />

                        <RelatedContent
                            items={[
                                { title: 'Laravel SaaS Guides', href: '/guides', description: 'In-depth tutorials on billing, feature flags, and architecture' },
                                { title: 'Features Overview', href: '/features', description: 'What ships with Laravel React Starter' },
                                { title: 'Compare Alternatives', href: '/compare', description: 'How it stacks up against Jetstream, Spark, SaaSykit, and more' },
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
                                <Link href="/blog" className="transition-colors hover:text-foreground">Blog</Link>
                                <Link href="/guides" className="transition-colors hover:text-foreground">Guides</Link>
                                <Link href="/pricing" className="transition-colors hover:text-foreground">Pricing</Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
