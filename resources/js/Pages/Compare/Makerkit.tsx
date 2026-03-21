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

export default function Makerkit({
  title,
  metaDescription,
  features,
  breadcrumbs,
  canonicalUrl,
  lastVerified,
  relatedComparisons,
}: ComparisonPageProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-makerkit' });
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
                Laravel React Starter vs Makerkit
              </h1>
              <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                Makerkit uses Supabase + Next.js and costs $299/year. This starter
                uses Laravel + MySQL + Redis for a one-time price with no vendor lock-in.
              </p>
            </header>

            {/* Intro */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <p>
                Makerkit is a well-designed SaaS starter for Next.js developers who want
                Supabase&apos;s managed auth and database. It ships with a clean UI, Stripe
                billing via Lemon Squeezy or Stripe, multi-tenancy, and a React admin panel.
                For teams already invested in the Supabase ecosystem, it&apos;s a strong choice.
              </p>
              <p>
                <strong>Pricing model:</strong> Makerkit is licensed at approximately $299/year
                (pricing varies by tier — verify at source before purchasing). Laravel React
                Starter is a one-time purchase — no annual renewal, no per-project fee.
              </p>
              <p>
                The core architectural difference: Makerkit bets on Supabase as a
                backend-as-a-service. This means your database, auth, and storage are managed
                by Supabase&apos;s cloud — fast to start, but with a dependency on their platform
                for every production request. Laravel React Starter uses a self-hosted stack
                (Laravel + MySQL + Redis) where you own every layer. Migration between hosts
                is straightforward because nothing depends on a proprietary SDK.
              </p>
              <p>
                Both cover the SaaS essentials: auth, billing, admin panel, and team
                management. The decision is really about your backend philosophy: managed BaaS
                with Supabase, or self-hosted with Laravel. If you know PHP and want
                predictable infrastructure costs, Laravel&apos;s model is simpler to reason about
                at scale.
              </p>
            </section>

            {/* Comparison Table */}
            <section className="my-16">
              <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <ComparisonTable
                  features={features}
                  usName="Laravel React Starter"
                  themName="Makerkit"
                />
              </div>
              {lastVerified && (
                <p className="mt-3 text-center text-xs text-muted-foreground">
                  Comparison data verified {lastVerified === '2026-03' ? 'March 2026' : lastVerified}.
                  Verify at source before purchasing — competitor features change frequently.
                </p>
              )}
            </section>

            {/* When to choose Makerkit */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <h2>When to choose Makerkit</h2>
              <p>
                Makerkit is the better choice if you&apos;re building with Next.js and want
                Supabase&apos;s managed realtime and storage built in. If your team prefers
                TypeScript end-to-end (no PHP) and you&apos;re comfortable with Supabase&apos;s
                ecosystem, Makerkit&apos;s annual model includes ongoing updates as Next.js
                and Supabase evolve.
              </p>
              <p>
                For teams that want multi-tenancy out of the box (organizations with multiple
                workspaces), Makerkit has deeper multi-tenant support than most Laravel starters.
                If that&apos;s a core requirement and you don&apos;t have strong Laravel preferences,
                it&apos;s worth evaluating.
              </p>
            </section>

            {/* When to choose this starter */}
            <section className="prose prose-neutral dark:prose-invert max-w-none mt-8">
              <h2>When to choose Laravel React Starter</h2>
              <p>
                If you prefer owning your infrastructure, Laravel&apos;s self-hosted stack avoids
                Supabase vendor lock-in. Your database is MySQL or PostgreSQL on a server you
                control, your auth is Sanctum (open-source), and your queue is Redis (self-hosted).
                At scale, predictable VPS costs typically beat BaaS pricing once you have real traffic.
              </p>
              <p>
                The one-time purchase model also matters for agencies and solo founders building
                multiple products. At ~$299/year, Makerkit&apos;s annual cost exceeds the
                one-time template price after the first year — and resets annually. No recurring
                license means one purchase covers every project you build on top of it.
              </p>
              <p>
                The concurrent payment protection layer, PHPStan on the backend, and TypeScript strict
                on the frontend gives you static analysis coverage across both layers. The 90+ test
                suite with Pest, Vitest, and Playwright means you can refactor confidently from day one.
              </p>
            </section>

            {/* Also compare */}
            {relatedComparisons && relatedComparisons.length > 0 && (
              <section className="mt-12">
                <h2 className="text-lg font-semibold">Also compare</h2>
                <div className="mt-4 grid gap-3 sm:grid-cols-3">
                  {relatedComparisons.slice(0, 6).map((comp) => (
                    <Link
                      key={comp.slug}
                      href={`/compare/${comp.slug}`}
                      className="rounded-xl border border-border/70 bg-card p-4 text-sm hover:border-primary/30 transition-colors"
                    >
                      <p className="font-medium">{comp.name}</p>
                      <p className="mt-1 text-xs text-muted-foreground">{comp.tagline}</p>
                    </Link>
                  ))}
                </div>
              </section>
            )}

            {/* CTA */}
            <section className="mt-12 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
              <h2 className="text-2xl font-bold">One-time price vs $299/year — do the math.</h2>
              <p className="mt-2 text-muted-foreground">
                Laravel + React + TypeScript — self-hosted, no vendor lock-in, no annual renewal.
              </p>
              <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                <Button asChild size="lg">
                  <Link href="/pricing">
                    View pricing
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
                <Button asChild variant="outline" size="lg">
                  <Link href="/">Get started</Link>
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
