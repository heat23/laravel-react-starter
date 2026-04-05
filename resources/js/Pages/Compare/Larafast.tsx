import { ArrowRight } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { ComparisonTable } from '@/Components/compare/ComparisonTable';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { JsonLd } from '@/Components/seo/JsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { ComparisonPageProps } from '@/types/index';

const DATE_PUBLISHED = '2026-03-20';

const faqSchema = {
  '@context': 'https://schema.org',
  '@type': 'FAQPage',
  mainEntity: [
    {
      '@type': 'Question',
      name: 'Is Larafast open source?',
      acceptedAnswer: {
        '@type': 'Answer',
        text: 'No. Larafast is a commercial paid product. The source code is provided after purchase but the license restricts redistribution. Laravel React Starter is also a commercial product, but with full source access and no per-domain fees.',
      },
    },
    {
      '@type': 'Question',
      name: 'Does Laravel React Starter include Stripe billing like Larafast?',
      acceptedAnswer: {
        '@type': 'Answer',
        text: "Yes. Laravel React Starter includes production-grade Stripe billing via Laravel Cashier with double-charge prevention to prevent race conditions, support for four plan tiers (free, pro, team, enterprise), per-seat pricing, a billing portal, and incomplete payment reminders — comparable to or exceeding Larafast's billing implementation.",
      },
    },
    {
      '@type': 'Question',
      name: 'Which is better for a React frontend — Larafast or Laravel React Starter?',
      acceptedAnswer: {
        '@type': 'Answer',
        text: 'Laravel React Starter is the clear choice if you want a React + TypeScript frontend. It ships React 18 with full TypeScript coverage, Inertia.js SSR, and Vitest frontend tests. Larafast defaults to Blade/Livewire; its React option is an add-on rather than the primary architecture. If React and TypeScript are important to you, Laravel React Starter was designed around that stack from day one.',
      },
    },
  ],
};

export default function Larafast({
  title,
  metaDescription,
  features,
  breadcrumbs,
}: ComparisonPageProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-larafast' });
  }, [track]);

  return (
    <>
      <Head title={title}>
        <meta name="description" content={metaDescription} />
        <meta name="robots" content="index, follow" />
        <link rel="canonical" href="/compare/larafast" />
        <meta property="og:title" content={title} />
        <meta property="og:description" content={metaDescription} />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content={title} />
        <meta name="twitter:description" content={metaDescription} />
        {/* FAQPage JSON-LD — emitted from component only, not from app.blade.php (SD001) */}
        <JsonLd data={faqSchema} />
        <JsonLd
          data={{
            '@context': 'https://schema.org',
            '@type': 'Article',
            headline: title,
            datePublished: DATE_PUBLISHED,
            dateModified: DATE_PUBLISHED,
          }}
        />
        {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
      </Head>

      <div className="min-h-screen bg-background">
        {/* Navigation */}
        <nav className="container flex items-center justify-between py-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo className="h-8 w-8" />
            <TextLogo className="text-xl font-bold" />
          </Link>
          <div className="flex items-center gap-4">
            {/* /compare hub — session 01 dependency; wire up after merge */}
            <Link
              href="/compare"
              className="text-sm text-muted-foreground hover:text-foreground transition-colors"
            >
              All comparisons
            </Link>
            <Link
              href="/pricing"
              className="text-sm text-muted-foreground hover:text-foreground transition-colors"
            >
              Pricing
            </Link>
          </div>
        </nav>

        <main className="container pb-24" id="main-content">
          <article className="mx-auto max-w-4xl">
            {/* Hero */}
            <header className="py-16 text-center">
              <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                Laravel React Starter vs Larafast — Which SaaS Starter Kit Is
                Right for You? (2026)
              </h1>
              <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                Both are Laravel SaaS boilerplates with Stripe billing. The
                difference is the frontend stack, test coverage, and what&apos;s
                included out of the box.
              </p>
            </header>

            {/* TL;DR (BLUF) */}
            <section
              className="rounded-2xl border border-border bg-muted/40 p-6 my-8"
              aria-label="Summary"
            >
              <h2 className="text-lg font-semibold mb-2">TL;DR</h2>
              <p className="text-muted-foreground">
                <strong>Choose Larafast</strong> if you prefer Livewire or Blade
                templates, want a polished Filament admin experience, or are
                buying from a team that offers active customer support with a
                Laravel-centric stack.{' '}
                <strong>Choose Laravel React Starter</strong> if you need
                React&nbsp;18 + TypeScript end-to-end, want webhooks, feature
                flags, and audit logging out of the box, value a 90+&nbsp;test
                suite with PHPStan static analysis, or want to avoid licensing
                fees with a one-time purchase and full source ownership. If your
                team already writes React and TypeScript, Laravel React Starter
                is the natural fit — the admin panel, frontend pages, and tests
                all share the same type-safe stack.
              </p>
            </section>

            {/* Quick comparison table */}
            <section
              className="my-16"
              aria-labelledby="quick-comparison-heading"
            >
              <h2
                id="quick-comparison-heading"
                className="text-2xl font-bold mb-6"
              >
                Quick Comparison — At a Glance
              </h2>
              <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <ComparisonTable
                  features={features}
                  usName="Laravel React Starter"
                  themName="Larafast"
                />
              </div>
            </section>

            {/* What is Larafast */}
            <section
              className="prose prose-neutral dark:prose-invert max-w-none"
              aria-labelledby="what-is-larafast"
            >
              <h2 id="what-is-larafast">What Is Larafast?</h2>
              <p>
                Larafast is a commercial Laravel SaaS starter kit that ships
                authentication, Stripe billing, a Filament-based admin panel,
                and basic user management. It targets developers who want a
                Laravel-native stack — Blade templates or Livewire components —
                and want to avoid scaffolding the billing layer from scratch.
              </p>
              <p>
                <strong>Larafast&apos;s strengths:</strong> The Filament admin
                panel is feature-rich and extensible with a large plugin
                ecosystem. Larafast ships quickly for Blade/Livewire projects.
                Its customer support offering is a genuine advantage for
                developers who prefer a supported product. The built-in blog and
                landing page scaffolding can save time on content marketing
                setup.
              </p>
            </section>

            {/* What is Laravel React Starter */}
            <section
              className="prose prose-neutral dark:prose-invert max-w-none mt-8"
              aria-labelledby="what-is-lrs"
            >
              <h2 id="what-is-lrs">What Is Laravel React Starter?</h2>
              <p>
                Laravel React Starter is a production-ready Laravel&nbsp;12 SaaS
                boilerplate built around React&nbsp;18, TypeScript, and
                Inertia.js SSR. It ships with concurrent payment protection,
                Stripe billing (four plans), a custom TypeScript admin panel, 11
                feature flags with database overrides, HMAC-signed webhooks
                (inbound and outbound), TOTP two-factor authentication, and a
                90+ test suite covering Pest, Vitest, PHPStan, and Pint — all in
                a single one-time purchase.
              </p>
              <p>
                The project is designed for solo founders and small teams who
                want a Laravel backend with a React + TypeScript frontend,
                without context-switching between different template engines or
                losing type safety at the admin layer.
              </p>
            </section>

            {/* Feature-by-feature */}
            <section
              className="prose prose-neutral dark:prose-invert max-w-none mt-8"
              aria-labelledby="feature-comparison"
            >
              <h2 id="feature-comparison">Feature-by-Feature Comparison</h2>
              <p>
                The table above covers the high-level differences. Key
                distinctions worth expanding:
              </p>
              <ul>
                <li>
                  <strong>Frontend stack:</strong> Larafast defaults to
                  Blade/Livewire. Laravel React Starter ships React 18 +
                  TypeScript via Inertia.js — the same stack across marketing
                  pages, auth flows, dashboard, and admin panel.
                </li>
                <li>
                  <strong>Admin panel:</strong> Larafast uses Filament
                  (PHP/Livewire). Laravel React Starter uses a custom React +
                  TypeScript admin panel — the same type system as your
                  user-facing product. No Blade/PHP context switch when
                  customizing admin views.
                </li>
                <li>
                  <strong>Webhooks:</strong> Laravel React Starter ships both
                  inbound and outbound webhooks with HMAC-SHA256 signing and a
                  full delivery history UI. Larafast does not include a
                  comparable webhook system.
                </li>
                <li>
                  <strong>Feature flags:</strong> Laravel React Starter includes
                  11 toggleable feature flags (billing, social auth, 2FA,
                  webhooks, admin panel, and more) with per-user database
                  overrides and a flag management UI in the admin panel.{' '}
                  <Link
                    href="/features/feature-flags"
                    className="text-primary hover:underline"
                  >
                    See feature flags documentation
                  </Link>
                  .
                </li>
                <li>
                  <strong>Billing implementation:</strong> Both include Stripe
                  billing. Laravel React Starter uses Redis locks (35&nbsp;s
                  timeout) to prevent concurrent subscription mutations — a
                  production safeguard that prevents double-billing on
                  overlapping requests.{' '}
                  <Link
                    href="/features/billing"
                    className="text-primary hover:underline"
                  >
                    See billing documentation
                  </Link>
                  .
                </li>
                <li>
                  <strong>Test suite:</strong> Laravel React Starter ships 90+
                  Pest tests, Vitest frontend tests, PHPStan static analysis
                  (Larastan), and Pint code style CI gates. Larafast&apos;s test
                  coverage is limited.
                </li>
                <li>
                  <strong>Audit logging:</strong> Laravel React Starter logs
                  user actions (login, logout, registration, billing mutations,
                  admin actions) with IP + user agent. Larafast does not include
                  comparable audit logging.
                </li>
                <li>
                  <strong>Accessibility:</strong> Laravel React Starter targets
                  WCAG 2.1 Level AA — keyboard navigable flows, visible focus
                  rings, semantic HTML, and ARIA labels on interactive elements.
                  All UI components are Radix-based with built-in accessibility
                  primitives.
                </li>
              </ul>
            </section>

            {/* Pricing */}
            <section
              className="prose prose-neutral dark:prose-invert max-w-none mt-8"
              aria-labelledby="pricing-comparison"
            >
              <h2 id="pricing-comparison">Pricing Comparison</h2>
              <p>
                Larafast is a commercial product with tiered pricing (prices may
                change — check larafast.com for current rates). At the time of
                this writing, Larafast charges a one-time fee per project with
                higher tiers unlocking additional features such as the blog
                module and priority support.
              </p>
              <p>
                Laravel React Starter is a <strong>one-time purchase</strong>{' '}
                with full source code access — no per-domain fees, no
                subscription, no recurring charges.{' '}
                <Link href="/pricing" className="text-primary hover:underline">
                  See our pricing
                </Link>
                .
              </p>
            </section>

            {/* When to choose Larafast */}
            <section
              className="prose prose-neutral dark:prose-invert max-w-none mt-8"
              aria-labelledby="choose-larafast"
            >
              <h2 id="choose-larafast">When to Choose Larafast</h2>
              <ul>
                <li>
                  <strong>Livewire/Blade preference:</strong> If your team
                  writes Livewire or Blade templates and does not want to add a
                  JavaScript build pipeline, Larafast aligns with that stack
                  more naturally.
                </li>
                <li>
                  <strong>Filament admin familiarity:</strong> If you already
                  know Filament and want its plugin ecosystem for rapid admin
                  panel extension, Larafast&apos;s Filament-based admin is
                  immediately familiar.
                </li>
                <li>
                  <strong>Built-in blog:</strong> Larafast includes a blog
                  module for content marketing. If content marketing is day-one
                  priority, this saves setup time.
                </li>
                <li>
                  <strong>Vendor support preference:</strong> Larafast offers
                  direct customer support. If you want a supported commercial
                  product with a team behind it, that is a genuine
                  differentiator.
                </li>
              </ul>
            </section>

            {/* When to choose LRS */}
            <section
              className="prose prose-neutral dark:prose-invert max-w-none mt-8"
              aria-labelledby="choose-lrs"
            >
              <h2 id="choose-lrs">When to Choose Laravel React Starter</h2>
              <ul>
                <li>
                  <strong>React + TypeScript stack:</strong> If your frontend is
                  React and TypeScript, Laravel React Starter is purpose-built
                  for that combination — from Inertia.js page props typed
                  end-to-end, to a TypeScript admin panel, to Vitest frontend
                  tests.
                </li>
                <li>
                  <strong>Full test suite from day one:</strong> 90+ Pest tests,
                  PHPStan static analysis, Vitest frontend tests, and Pint code
                  style are included. You ship with confidence in the billing
                  layer and auth flows before writing a line of your own code.
                </li>
                <li>
                  <strong>Webhooks + audit logging + feature flags:</strong>{' '}
                  These production infrastructure features are not standard in
                  Larafast. If you need granular feature rollouts, a webhook
                  delivery system, or a compliance-ready audit trail, Laravel
                  React Starter ships all three.
                </li>
                <li>
                  <strong>No licensing friction:</strong> One-time purchase,
                  full source, no per-project fees. Deploy to any number of
                  projects under the same license.
                </li>
              </ul>
            </section>

            {/* Verdict */}
            <section
              className="prose prose-neutral dark:prose-invert max-w-none mt-8"
              aria-labelledby="verdict"
            >
              <h2 id="verdict">Verdict</h2>
              <p>
                Larafast is a solid kit for Laravel developers who want a
                Blade/Livewire stack with Filament admin and don&apos;t need
                React. Laravel React Starter is the better choice for developers
                who want a React + TypeScript frontend, need production
                infrastructure (webhooks, feature flags, audit logging), and
                want a comprehensive test suite that validates the billing layer
                before they ever touch it.
              </p>
              <p>
                For a direct, head-to-head decision: if you&apos;re reading this
                page because you want React on the frontend and you&apos;re
                evaluating Larafast as an alternative — Laravel React Starter is
                the better fit.{' '}
                <Link
                  href="/pricing"
                  className="text-primary hover:underline font-medium"
                >
                  See pricing and get started.
                </Link>
              </p>
            </section>

            {/* FAQ section (visible) */}
            <section className="mt-16" aria-labelledby="faq-heading">
              <h2 id="faq-heading" className="text-2xl font-bold mb-6">
                Frequently Asked Questions
              </h2>
              <dl className="space-y-6">
                <div className="rounded-xl border border-border p-5">
                  <dt className="font-semibold">Is Larafast open source?</dt>
                  <dd className="mt-2 text-muted-foreground">
                    No. Larafast is a commercial paid product. The source code
                    is provided after purchase but the license restricts
                    redistribution. Laravel React Starter is also a commercial
                    product, but with full source access and no per-domain fees.
                  </dd>
                </div>
                <div className="rounded-xl border border-border p-5">
                  <dt className="font-semibold">
                    Does Laravel React Starter include Stripe billing like
                    Larafast?
                  </dt>
                  <dd className="mt-2 text-muted-foreground">
                    Yes. Laravel React Starter includes production-grade Stripe
                    billing via Laravel Cashier with double-charge prevention to
                    prevent race conditions, support for four plan tiers (free,
                    pro, team, enterprise), per-seat pricing, a billing portal,
                    and incomplete payment reminders — comparable to or
                    exceeding Larafast&apos;s billing implementation.
                  </dd>
                </div>
                <div className="rounded-xl border border-border p-5">
                  <dt className="font-semibold">
                    Which is better for a React frontend — Larafast or Laravel
                    React Starter?
                  </dt>
                  <dd className="mt-2 text-muted-foreground">
                    Laravel React Starter is the clear choice if you want a
                    React + TypeScript frontend. It ships React 18 with full
                    TypeScript coverage, Inertia.js SSR, and Vitest frontend
                    tests. Larafast defaults to Blade/Livewire; its React option
                    is an add-on rather than the primary architecture. If React
                    and TypeScript are important to you, Laravel React Starter
                    was designed around that stack from day one.
                  </dd>
                </div>
              </dl>
            </section>

            {/* Related comparisons */}
            <section className="mt-12" aria-labelledby="related-heading">
              <h2 id="related-heading" className="text-lg font-semibold mb-4">
                Related comparisons
              </h2>
              <div className="flex flex-wrap gap-3">
                {/* /compare hub — session 01 dependency; wire up after merge */}
                <Link
                  href="/compare"
                  className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition-colors"
                >
                  All comparisons
                </Link>
                <Link
                  href="/compare/shipfast"
                  className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition-colors"
                >
                  vs Shipfast
                </Link>
                <Link
                  href="/compare/wave"
                  className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition-colors"
                >
                  vs Wave
                </Link>
                <Link
                  href="/compare/saasykit"
                  className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition-colors"
                >
                  vs SaaSykit
                </Link>
              </div>
            </section>

            {/* CTA */}
            <section className="mt-16 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
              <h2 className="text-2xl font-bold">Ready to ship your SaaS?</h2>
              <p className="mt-2 text-muted-foreground">
                React + TypeScript + Laravel — everything Larafast doesn&apos;t
                include, included.
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
                <Link
                  href="/compare"
                  className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >
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
                <Link
                  href="/terms"
                  className="hover:text-foreground transition-colors"
                >
                  Terms
                </Link>
                <Link
                  href="/privacy"
                  className="hover:text-foreground transition-colors"
                >
                  Privacy
                </Link>
              </nav>
            </div>
          </div>
        </footer>
      </div>
    </>
  );
}
