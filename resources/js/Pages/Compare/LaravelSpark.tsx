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

export default function LaravelSpark({
  title,
  metaDescription,
  features,
  breadcrumbs,
  canonicalUrl,
}: ComparisonPageProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-laravel-spark' });
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
                Laravel React Starter vs Laravel Spark
              </h1>
              <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                Both handle Stripe billing for Laravel. The difference is scope,
                frontend philosophy, and pricing model.
              </p>
            </header>

            {/* Intro */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <p>
                Laravel Spark is the official billing scaffolding from the Laravel ecosystem.
                It handles Stripe subscriptions elegantly, has team billing built in, and
                provides a clean billing portal. For adding subscriptions to an existing
                Laravel application, Spark is a proven solution.
              </p>
              <p>
                <strong>Pricing model:</strong> Laravel Spark is licensed at $99 per year (per project).
                Laravel React Starter is a one-time purchase with no per-project fee — deploy to
                any number of projects under the same license.
              </p>
              <p>
                The tradeoff is scope and cost structure. Spark is a recurring subscription
                focused narrowly on billing. It does not include an admin panel, feature flags,
                webhooks, audit logging, or a frontend opinion. Those are left to you. This
                starter includes comparable billing infrastructure &mdash; Redis-locked Stripe
                mutations, four plan tiers, dunning emails &mdash; plus all of those additional
                features, as a one-time purchase.
              </p>
              <p>
                This is not a criticism of Spark&apos;s approach. Focused tools have real
                advantages: smaller code surface, fewer opinions to disagree with, and easier
                auditing. The question is whether you want a billing layer you add to your
                existing app, or a complete foundation you build your app on top of.
              </p>
            </section>

            {/* Comparison Table */}
            <section className="my-16">
              <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <ComparisonTable
                  features={features}
                  usName="Laravel React Starter"
                  themName="Laravel Spark"
                />
              </div>
            </section>

            {/* When to choose Spark */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <h2>When to choose Spark</h2>
              <p>
                Spark is the right choice if you are adding billing to an existing Laravel
                application that already has its own frontend, admin panel, and feature
                infrastructure. Its billing-focused scope means the code surface is smaller
                and easier to audit. You know exactly what Spark does and does not do.
              </p>
              <p>
                If you are using Livewire for your frontend and just need a billing layer,
                Spark&apos;s tighter scope is an advantage, not a limitation. You are not paying
                for features you will not use. The $99 per year recurring cost is also
                predictable and includes updates as Stripe&apos;s API evolves.
              </p>
              <p>
                For teams that prefer to assemble their stack from focused, single-purpose
                packages rather than starting from an opinionated boilerplate, Spark fits
                that philosophy well.
              </p>
            </section>

            {/* When to choose this starter */}
            <section className="prose prose-neutral dark:prose-invert max-w-none mt-8">
              <h2>When to choose Laravel React Starter</h2>
              <p>
                Starting from zero? The one-time price versus $99 per year recurring matters
                at early stage. After two years, the recurring cost exceeds the one-time
                purchase &mdash; and you still need to build the admin panel, feature flags,
                and webhooks yourself.
              </p>
              <p>
                More importantly, Spark does not include an admin panel for user management,
                feature flags for gradual rollouts, webhook infrastructure for integrations,
                or audit logging for compliance. If you are building a multi-feature SaaS,
                you will add those yourself anyway. Starting from a base that already has
                them &mdash; tested, integrated, and type-safe &mdash; saves four to eight
                weeks of implementation work.
              </p>
              <p>
                The React and TypeScript frontend is another differentiator. Spark does not
                have a frontend opinion, which means you are building your own UI from
                scratch. This starter gives you a complete React frontend with type-safe
                Inertia props, accessible components, and dark mode support from day one.
              </p>
            </section>

            {/* Cross-links to feature pages */}
            <div className="mt-8 flex flex-wrap gap-3 text-sm">
              <Link href="/features/billing" className="text-primary hover:underline">
                See the billing feature →
              </Link>
              <Link href="/features/admin-panel" className="text-primary hover:underline">
                See the admin panel →
              </Link>
            </div>

            {/* CTA */}
            <section className="mt-12 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
              <h2 className="text-2xl font-bold">Spark costs $99/year. Our starter template is a one-time fee.</h2>
              <p className="mt-2 text-muted-foreground">
                Billing, admin panel, feature flags, webhooks, and 90+ tests &mdash; everything
                Spark doesn&apos;t include, included.
              </p>
              <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                <Button asChild size="lg">
                  <Link href="/pricing">
                    Compare total cost
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
                <Button asChild variant="outline" size="lg">
                  <Link href="/">Get started</Link>
                </Button>
              </div>
              <p className="mt-4 text-sm text-muted-foreground">
                Trusted by developers who switched from Spark.
              </p>
              <div className="mt-2">
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
