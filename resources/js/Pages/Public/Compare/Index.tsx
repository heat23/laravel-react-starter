import { ArrowRight, CheckCircle2, XCircle } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { JsonLd } from '@/Components/seo/JsonLd';
import { Button } from '@/Components/ui/button';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import type { BreadcrumbItem } from '@/types/index';

interface CompareIndexProps {
  title: string;
  metaDescription: string;
  appUrl: string;
  breadcrumbs?: BreadcrumbItem[];
}

interface KitCard {
  name: string;
  isUs: boolean;
  price: string;
  stack: string;
  billing: boolean | string;
  tests: string;
  openSource: boolean;
  link: string;
  linkLabel: string;
}

const kits: KitCard[] = [
  {
    name: 'Laravel React Starter',
    isUs: true,
    price: 'One-time',
    stack: 'Laravel 12 + React 18 + TypeScript',
    billing: true,
    tests: '90+ Pest + Vitest',
    openSource: false,
    link: '/pricing',
    linkLabel: 'View pricing',
  },
  {
    name: 'Larafast',
    isUs: false,
    price: 'One-time',
    stack: 'Laravel + Livewire / React',
    billing: true,
    tests: 'Limited',
    openSource: false,
    link: '/compare/larafast',
    linkLabel: 'Full comparison',
  },
  {
    name: 'SaaSyKit',
    isUs: false,
    price: 'One-time',
    stack: 'Laravel + React (Inertia)',
    billing: true,
    tests: 'Yes',
    openSource: false,
    link: '/compare/saasykit',
    linkLabel: 'Full comparison',
  },
  {
    name: 'Wave',
    isUs: false,
    price: 'Free (MIT)',
    stack: 'Laravel + Blade + Livewire',
    billing: 'Via Spark',
    tests: 'PHP only',
    openSource: true,
    link: '/compare/wave',
    linkLabel: 'Full comparison',
  },
  {
    name: 'Laravel Spark',
    isUs: false,
    price: '$99/year',
    stack: 'Laravel + Bring Your Own UI',
    billing: true,
    tests: 'Minimal',
    openSource: false,
    link: '/compare/laravel-spark',
    linkLabel: 'Full comparison',
  },
  {
    name: 'Laravel Jetstream',
    isUs: false,
    price: 'Free (MIT)',
    stack: 'Laravel + Vue 3 or Livewire',
    billing: false,
    tests: 'Basic',
    openSource: true,
    link: '/compare/laravel-jetstream',
    linkLabel: 'Full comparison',
  },
  {
    name: 'ShipFast',
    isUs: false,
    price: 'One-time',
    stack: 'Next.js + React + TypeScript',
    billing: true,
    tests: 'Varies',
    openSource: false,
    link: '/compare/shipfast',
    linkLabel: 'Full comparison',
  },
  {
    name: 'SupaStarter',
    isUs: false,
    price: 'One-time',
    stack: 'Next.js + React + Supabase',
    billing: true,
    tests: 'Varies',
    openSource: false,
    link: '/compare/supastarter',
    linkLabel: 'Full comparison',
  },
  {
    name: 'MakerKit',
    isUs: false,
    price: 'Subscription',
    stack: 'Next.js + React + Supabase/Firebase',
    billing: true,
    tests: 'Yes',
    openSource: false,
    link: '/compare/makerkit',
    linkLabel: 'Full comparison',
  },
];

const criteria = [
  {
    title: 'Production billing',
    description:
      'Stripe subscriptions with race-condition prevention (Redis locks), dunning emails, and incomplete payment recovery — not just a Stripe Checkout redirect.',
  },
  {
    title: 'Custom admin panel',
    description:
      'A React or Filament admin panel with user management, health monitoring, and audit logs. Bare starters leave this entirely to you.',
  },
  {
    title: 'Test coverage from day one',
    description:
      'A PHP test suite (Pest or PHPUnit), frontend tests (Vitest), and optional E2E (Playwright) — not a comment saying "add tests here".',
  },
  {
    title: 'TypeScript throughout',
    description:
      'Typed Inertia page props, strict tsconfig, and a frontend component library you can safely extend without runtime surprises.',
  },
  {
    title: 'Feature flags with runtime overrides',
    description:
      'Env-based flags are the floor. The ceiling is database overrides, per-user targeting, and a UI to flip flags without a deploy.',
  },
  {
    title: 'Security infrastructure',
    description:
      'Rate limits on every sensitive endpoint, CSRF on all state-changing routes, security headers (CSP, HSTS), and 2FA support.',
  },
  {
    title: 'CI/CD and static analysis',
    description:
      'GitHub Actions with PHPStan/Larastan, Pint, ESLint, and a build gate that blocks merges on type errors.',
  },
  {
    title: 'Documented deployment',
    description:
      'nginx config, supervisor for queues, and a VPS setup script — not just "heroku push" or "deploy to Vercel".',
  },
];

export default function CompareIndex({
  title,
  metaDescription,
  appUrl,
  breadcrumbs,
}: CompareIndexProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-index' });
  }, [track]);

  const itemListSchema = {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    name: 'Laravel SaaS Starter Kit Comparisons',
    itemListElement: [
      {
        '@type': 'ListItem',
        position: 1,
        url: `${appUrl}/compare/larafast`,
        name: 'Laravel React Starter vs Larafast',
      },
      {
        '@type': 'ListItem',
        position: 2,
        url: `${appUrl}/compare/saasykit`,
        name: 'Laravel React Starter vs SaaSyKit',
      },
      {
        '@type': 'ListItem',
        position: 3,
        url: `${appUrl}/compare/wave`,
        name: 'Laravel React Starter vs Wave',
      },
      {
        '@type': 'ListItem',
        position: 4,
        url: `${appUrl}/compare/laravel-spark`,
        name: 'Laravel React Starter vs Laravel Spark',
      },
      {
        '@type': 'ListItem',
        position: 5,
        url: `${appUrl}/compare/laravel-jetstream`,
        name: 'Laravel React Starter vs Laravel Jetstream',
      },
      {
        '@type': 'ListItem',
        position: 6,
        url: `${appUrl}/compare/shipfast`,
        name: 'Laravel React Starter vs ShipFast',
      },
      {
        '@type': 'ListItem',
        position: 7,
        url: `${appUrl}/compare/supastarter`,
        name: 'Laravel React Starter vs SupaStarter',
      },
      {
        '@type': 'ListItem',
        position: 8,
        url: `${appUrl}/compare/makerkit`,
        name: 'Laravel React Starter vs MakerKit',
      },
    ],
  };

  return (
    <>
      <Head title={title}>
        <meta name="description" content={metaDescription} />
        <link rel="canonical" href={`${appUrl}/compare`} />
        <meta property="og:title" content={title} />
        <meta property="og:description" content={metaDescription} />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content={title} />
        <meta name="twitter:description" content={metaDescription} />
        {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
        <JsonLd data={itemListSchema} />
      </Head>

      <div className="min-h-screen bg-background">
        {/* Navigation */}
        <nav className="container flex items-center justify-between py-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo className="h-8 w-8" />
            <TextLogo className="text-xl font-bold" />
          </Link>
          <div className="flex items-center gap-4">
            <Link
              href="/pricing"
              className="text-sm text-muted-foreground hover:text-foreground transition-colors"
            >
              Pricing
            </Link>
            <Link
              href="/guides/saas-starter-kit-comparison-2026"
              className="text-sm text-muted-foreground hover:text-foreground transition-colors"
            >
              Buyer's Guide
            </Link>
          </div>
        </nav>

        <main className="container pb-24">
          {/* Hero */}
          <header className="py-16 text-center">
            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
              Laravel SaaS Starter Kit Comparison 2026
            </h1>
            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
              Nine Laravel (and Laravel-adjacent) SaaS boilerplates, compared
              side by side. Find the right starting point for your stack,
              budget, and production requirements.
            </p>
            <div className="mt-6">
              <Link
                href="/guides/saas-starter-kit-comparison-2026"
                className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
              >
                Read the full buyer&apos;s guide
                <ArrowRight className="h-3 w-3" />
              </Link>
            </div>
          </header>

          {/* Criteria */}
          <section
            className="mx-auto max-w-4xl"
            aria-labelledby="criteria-heading"
          >
            <h2 id="criteria-heading" className="text-2xl font-bold mb-2">
              What Separates Good SaaS Starter Kits from Bare Scaffolding
            </h2>
            <p className="text-muted-foreground mb-8">
              Most &ldquo;starter kits&rdquo; are scaffolding — they give you
              auth and a layout, then leave the hard parts to you. A
              production-ready SaaS kit ships all of the following out of the
              box:
            </p>
            <div className="grid gap-4 sm:grid-cols-2">
              {criteria.map((item) => (
                <div
                  key={item.title}
                  className="flex gap-3 rounded-xl border border-border bg-card p-4"
                >
                  <CheckCircle2
                    className="mt-0.5 h-5 w-5 shrink-0 text-success"
                    aria-hidden="true"
                  />
                  <div>
                    <p className="font-semibold text-sm">{item.title}</p>
                    <p className="text-sm text-muted-foreground mt-0.5">
                      {item.description}
                    </p>
                  </div>
                </div>
              ))}
            </div>
            <p className="mt-6 text-sm text-muted-foreground">
              Laravel React Starter hits all eight. Most competitors hit two to
              four.
            </p>
          </section>

          {/* Comparison Cards */}
          <section
            className="mx-auto mt-20 max-w-5xl"
            aria-labelledby="cards-heading"
          >
            <h2 id="cards-heading" className="text-2xl font-bold mb-8">
              Side-by-Side Comparison Cards
            </h2>
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {kits.map((kit) => (
                <article
                  key={kit.name}
                  className={`flex flex-col rounded-2xl border p-5 shadow-sm ${
                    kit.isUs
                      ? 'border-primary/50 bg-primary/5 ring-1 ring-primary/20'
                      : 'border-border bg-card'
                  }`}
                >
                  {kit.isUs && (
                    <div className="mb-3 inline-flex w-fit items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                      Our pick
                    </div>
                  )}
                  <h3 className="font-semibold text-base">{kit.name}</h3>
                  <p className="mt-1 text-xs text-muted-foreground">
                    {kit.stack}
                  </p>

                  <dl className="mt-4 space-y-2 flex-1 text-sm">
                    <div className="flex justify-between gap-2">
                      <dt className="text-muted-foreground">Price</dt>
                      <dd className="font-medium text-right">{kit.price}</dd>
                    </div>
                    <div className="flex justify-between gap-2">
                      <dt className="text-muted-foreground">Billing</dt>
                      <dd>
                        {kit.billing === true ? (
                          <CheckCircle2
                            className="h-4 w-4 text-success"
                            aria-label="Yes"
                          />
                        ) : kit.billing === false ? (
                          <XCircle
                            className="h-4 w-4 text-destructive"
                            aria-label="No"
                          />
                        ) : (
                          <span className="text-muted-foreground text-xs">
                            {kit.billing}
                          </span>
                        )}
                      </dd>
                    </div>
                    <div className="flex justify-between gap-2">
                      <dt className="text-muted-foreground">Tests</dt>
                      <dd className="font-medium text-right text-xs">
                        {kit.tests}
                      </dd>
                    </div>
                    <div className="flex justify-between gap-2">
                      <dt className="text-muted-foreground">Open source</dt>
                      <dd>
                        {kit.openSource ? (
                          <CheckCircle2
                            className="h-4 w-4 text-success"
                            aria-label="Yes"
                          />
                        ) : (
                          <XCircle
                            className="h-4 w-4 text-muted-foreground"
                            aria-label="No"
                          />
                        )}
                      </dd>
                    </div>
                  </dl>

                  <div className="mt-4 pt-4 border-t border-border">
                    <Link
                      href={kit.link}
                      className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                    >
                      {kit.linkLabel}
                      <ArrowRight className="h-3 w-3" />
                    </Link>
                  </div>
                </article>
              ))}
            </div>
          </section>

          {/* Editorial recommendation */}
          <section className="mx-auto mt-20 max-w-2xl rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
            <h2 className="text-2xl font-bold">
              Our Top Pick for Laravel Teams
            </h2>
            <p className="mt-4 text-muted-foreground">
              For teams committed to the Laravel + React + TypeScript stack,
              Laravel React Starter is the only option that ships production
              billing, a custom React admin panel, 90+ tests, PHPStan static
              analysis, and 11 feature flags together. Every other kit requires
              significant additions before it&apos;s genuinely production-ready.
            </p>
            <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
              <Button asChild size="lg">
                <Link href="/pricing">
                  View pricing
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Link>
              </Button>
              <Button asChild variant="outline" size="lg">
                <Link href="/guides/saas-starter-kit-comparison-2026">
                  Read the full guide
                </Link>
              </Button>
            </div>
          </section>
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
