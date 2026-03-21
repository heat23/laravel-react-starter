import { ArrowRight } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { ComparisonTable } from '@/Components/compare/ComparisonTable';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { ComparisonPageProps } from '@/types/index';

export default function Wave({
  title,
  metaDescription,
  features,
  breadcrumbs,
  canonicalUrl,
}: ComparisonPageProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-wave' });
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
        {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
        {canonicalUrl && <link rel="canonical" href={canonicalUrl} />}
      </Head>

      <div className="min-h-screen bg-background">
        {/* Navigation */}
        <nav className="container flex items-center justify-between py-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo className="h-8 w-8" />
            <TextLogo className="text-xl font-bold" />
          </Link>
          <div className="flex items-center gap-4">
            <Link href="/pricing" className="text-sm text-muted-foreground hover:text-foreground transition-colors">
              Pricing
            </Link>
          </div>
        </nav>

        <main className="container pb-24">
          <article className="mx-auto max-w-4xl">
            {/* Hero */}
            <header className="py-16 text-center">
              <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                Laravel React Starter vs Wave
              </h1>
              <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                Wave is an established open-source Laravel SaaS kit. The core difference
                is the frontend stack: Blade + Livewire vs React + TypeScript.
              </p>
            </header>

            {/* Intro */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <p>
                Wave is one of the most established open-source Laravel SaaS kits &mdash; it
                handles billing, teams, announcements, and a blog out of the box. It has a
                strong community and a &ldquo;batteries included&rdquo; philosophy similar to
                this starter. The major stack difference: Wave uses Blade + Livewire + Alpine.js
                for its UI, while this starter uses React + TypeScript across the entire application.
              </p>
              <p>
                If you want a React + TypeScript frontend &mdash; and React in the admin panel,
                not just the marketing pages &mdash; this starter makes different choices. Wave&apos;s
                Filament-based admin panel is powerful but uses a separate rendering stack from the
                customer-facing frontend. This comparison outlines the functional and architectural
                differences for developers who&apos;ve shortlisted both options.
              </p>
              <p>
                Both projects target solo founders and small teams building SaaS products on Laravel.
                The decision comes down to which frontend stack your team is most productive with,
                and whether you value a unified React + TypeScript stack or prefer the maturity and
                community support of the Blade/Livewire ecosystem.
              </p>
            </section>

            {/* Comparison Table */}
            <section className="my-16">
              <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <ComparisonTable
                  features={features}
                  usName="Laravel React Starter"
                  themName="Wave"
                />
              </div>
            </section>

            {/* When to choose Wave */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <h2>When to choose Wave</h2>
              <p>
                Wave is the better choice if your team prefers Blade and Livewire over React.
                If you&apos;re comfortable with the Livewire component model and enjoy Alpine.js for
                frontend interactivity, Wave&apos;s architecture will feel natural and productive.
                There&apos;s no frontend build step to manage, no Node.js in your deployment pipeline,
                and no TypeScript compilation to worry about.
              </p>
              <p>
                Wave is also free and open-source under the MIT license, which makes it the right
                choice if price is a constraint or if you want to contribute back to the project.
                The community support on Discord and GitHub is strong &mdash; you can find answers to
                Wave-specific problems quickly. Wave&apos;s built-in blog and announcements feature
                is also valuable if your SaaS needs content management from day one.
              </p>
            </section>

            {/* When to choose this starter */}
            <section className="prose prose-neutral dark:prose-invert max-w-none mt-8">
              <h2>When to choose Laravel React Starter</h2>
              <p>
                If your team has decided on React + TypeScript, Wave&apos;s Blade/Livewire frontend
                means rebuilding the entire UI layer. The TypeScript coverage across both the
                marketing site and admin panel is the core differentiator &mdash; PHPStan on PHP,
                TypeScript strict mode on React, Vitest for component tests. If end-to-end type
                safety matters to your team, this starter&apos;s architecture is more consistent.
              </p>
              <p>
                The Redis-locked billing layer is custom-built for safety &mdash; concurrent Stripe
                operations are serialized with distributed locks, preventing race conditions that can
                occur when multiple requests modify the same subscription simultaneously. The 11
                feature flags with database overrides give you granular control over feature rollouts
                without redeploying. For teams that need TypeScript across the full stack and
                production-grade billing infrastructure, this starter provides both.
              </p>
            </section>

            {/* CTA */}
            <section className="mt-16 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
              <h2 className="text-2xl font-bold">Ship your SaaS faster</h2>
              <p className="mt-2 text-muted-foreground">
                React + TypeScript from auth to admin panel, with Redis-locked billing.
              </p>
              <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                <Button asChild size="lg">
                  <Link href="/">
                    Get started
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
                <Button asChild variant="outline" size="lg">
                  <Link href="/pricing">View pricing</Link>
                </Button>
              </div>
              <div className="mt-4">
                <Link href="/compare" className="text-sm text-muted-foreground hover:text-foreground transition-colors">
                  ← See all comparisons
                </Link>
              </div>
            </section>
          </article>
        </main>

        {/* Footer */}
        <footer className="border-t py-8">
          <div className="container">
            <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
              <p className="text-sm text-muted-foreground">
                &copy; {new Date().getFullYear()} Laravel React Starter. All
                rights reserved.
              </p>
              <nav className="flex items-center gap-4 text-sm text-muted-foreground">
                <Link href="/terms" className="hover:text-foreground transition-colors">Terms</Link>
                <Link href="/privacy" className="hover:text-foreground transition-colors">Privacy</Link>
              </nav>
            </div>
          </div>
        </footer>
      </div>
    </>
  );
}
