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

export default function LaravelJetstream({
  title,
  metaDescription,
  features,
  breadcrumbs,
  canonicalUrl,
}: ComparisonPageProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-laravel-jetstream' });
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
                Laravel React Starter vs Laravel Jetstream
              </h1>
              <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                The right starting point depends on your stack and scope. This
                is a factual comparison for teams who have already decided on
                React.
              </p>
            </header>

            {/* Intro */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <p>
                Laravel Jetstream is the official scaffolding package from the Laravel team.
                It is well-maintained, backed by the core team, and free. For many Laravel
                projects, it is the right choice. But Jetstream makes a specific frontend
                decision: Vue.js or Livewire. If your team already uses React and TypeScript
                across other projects, adopting Jetstream means either learning Vue, forking
                the scaffolding to swap in React, or maintaining two frontend stacks in the
                same organization.
              </p>
              <p>
                This comparison covers the real differences that matter when you have already
                decided on React. We are not arguing that React is better than Vue &mdash;
                that is a team preference. We are showing what each option gives you out of
                the box so you can make an informed decision about your starting point.
              </p>
              <p>
                Beyond the frontend choice, the two projects differ in scope. Jetstream
                focuses on authentication scaffolding: login, registration, two-factor auth,
                API tokens, and team management. This starter includes all of those plus
                production infrastructure that Jetstream intentionally leaves to you: Stripe
                billing with Redis-locked mutations, a full admin panel, feature flags,
                webhooks, audit logging, and social authentication. The tradeoff is clear:
                Jetstream is free and focused; this starter is a paid, broader foundation.
              </p>
            </section>

            {/* Comparison Table */}
            <section className="my-16">
              <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <ComparisonTable
                  features={features}
                  usName="Laravel React Starter"
                  themName="Laravel Jetstream"
                />
              </div>
            </section>

            {/* When to choose Jetstream */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <h2>When to choose Jetstream</h2>
              <p>
                If your team uses Vue.js or Livewire, Jetstream is the right choice. It is
                the official Laravel scaffolding, maintained by the core team, and completely
                free. The integration with Vue is first-class &mdash; components, composables,
                and Inertia adapters are all built for Vue out of the box.
              </p>
              <p>
                Jetstream also has a mature Teams feature. If you need multi-tenant team
                management where users create and switch between teams, and you do not need
                billing or an admin panel, Jetstream handles this well. For simple CRUD
                applications or internal tools where React is not a requirement, Jetstream
                removes one decision from your stack.
              </p>
              <p>
                The free price point matters too. If you are building a side project or
                learning Laravel, paying for a starter template does not make sense when
                Jetstream gives you solid authentication scaffolding at no cost.
              </p>
            </section>

            {/* When to choose this starter */}
            <section className="prose prose-neutral dark:prose-invert max-w-none mt-8">
              <h2>When to choose Laravel React Starter</h2>
              <p>
                If you are shipping a SaaS product and need billing infrastructure from day
                one, Jetstream does not provide it. Redis-locked Stripe subscription mutations,
                four plan tiers with seat-based pricing, dunning email sequences, and incomplete
                payment recovery are outside Jetstream&apos;s scope. You would build all of that
                yourself on top of Jetstream&apos;s auth scaffolding.
              </p>
              <p>
                The same applies to the admin panel. User management, feature flag overrides,
                health monitoring, audit log viewing, and system configuration are features
                that every SaaS needs eventually. Starting from a base that already has them
                saves weeks of implementation and testing.
              </p>
              <p>
                If your team already knows React and TypeScript, starting with Vue adds
                cognitive overhead on every pull request. Developers context-switch between
                two component models, two reactivity systems, and two sets of tooling. A
                React-first starter eliminates that friction entirely.
              </p>
            </section>

            {/* ROI framing */}
            <section className="mt-12 rounded-2xl border border-border bg-muted/40 p-8">
              <h2 className="text-xl font-bold">The Inertia React setup you were going to build anyway</h2>
              <p className="mt-3 text-muted-foreground">
                Jetstream gives you auth scaffolding. You still need to wire up billing, admin,
                notifications, API docs, and onboarding. That&apos;s 2&ndash;3 weeks of setup.
                We&apos;ve done it. The question is whether your time is worth more than the
                template price.
              </p>
              <div className="mt-4 flex flex-wrap gap-3 text-sm">
                <Link href="/features/billing" className="text-primary hover:underline">
                  See the billing layer →
                </Link>
                <Link href="/features/admin-panel" className="text-primary hover:underline">
                  See the admin panel →
                </Link>
              </div>
            </section>

            {/* CTA */}
            <section className="mt-12 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
              <h2 className="text-2xl font-bold">Already know Jetstream? You&apos;ll be shipping in 30 minutes.</h2>
              <p className="mt-2 text-muted-foreground">
                Same Laravel patterns, same Inertia.js — just React instead of Vue, plus billing
                and admin panel already wired up.
              </p>
              <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                <Button asChild size="lg">
                  <Link href="/">
                    Start with React instead
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
                <Button asChild variant="outline" size="lg">
                  <Link href="/pricing">View pricing</Link>
                </Button>
              </div>
              <p className="mt-4 text-sm text-muted-foreground">
                Trusted by developers who switched from Jetstream.
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
