// Article content is hardcoded as structured JSX (Option A).
// JSON-LD schemas use JSON.stringify() directly — do NOT wrap with DOMPurify.sanitize()
// as it corrupts JSON strings (audit finding SD009).

import { ArrowRight } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { TableOfContents, type TocSection } from '@/Components/blog/TableOfContents';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { Button } from '@/Components/ui/button';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import type { GuidePageProps } from '@/types/index';

interface SaasStarterKitComparisonProps extends GuidePageProps {
  appUrl: string;
}

const sections: TocSection[] = [
  { id: 'what-makes-production-ready', title: 'What Makes a Production-Ready SaaS Starter Kit?', level: 2 },
  { id: 'comparison-matrix', title: 'Comparison Matrix', level: 2 },
  { id: 'pick-1-laravel-react-starter', title: '#1 — Laravel React Starter (Our Pick)', level: 2 },
  { id: 'pick-2-larafast', title: '#2 — Larafast', level: 2 },
  { id: 'pick-3-saasykit', title: '#3 — SaaSyKit', level: 2 },
  { id: 'pick-4-wave', title: '#4 — Wave', level: 2 },
  { id: 'pick-5-laravel-spark', title: '#5 — Laravel Spark', level: 2 },
  { id: 'pick-6-shipfast', title: '#6 — ShipFast (Next.js)', level: 2 },
  { id: 'pick-7-supastarter', title: '#7 — SupaStarter', level: 2 },
  { id: 'pick-8-jetstream', title: '#8 — Laravel Jetstream', level: 2 },
  { id: 'price-comparison', title: 'Price Comparison', level: 2 },
  { id: 'final-recommendation', title: 'Our Final Recommendation', level: 2 },
];

export default function SaasStarterKitComparison({
  title,
  metaDescription,
  appName,
  appUrl,
  breadcrumbs,
}: SaasStarterKitComparisonProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-saas-starter-kit-comparison' });
  }, [track]);

  const articleSchema = JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: 'Best Laravel SaaS Starter Kits in 2026 — Complete Comparison Guide',
    description: metaDescription,
    author: { '@type': 'Person', name: appName },
    publisher: { '@type': 'Organization', name: appName },
    datePublished: '2026-03-20',
    dateModified: '2026-03-20',
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': `${appUrl}/guides/saas-starter-kit-comparison-2026`,
    },
    wordCount: 3200,
  });

  const faqSchema = JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: [
      {
        '@type': 'Question',
        name: 'What is the best Laravel SaaS starter kit in 2026?',
        acceptedAnswer: {
          '@type': 'Answer',
          text: 'Laravel React Starter ranks first for teams using React + TypeScript. It ships production Stripe billing with Redis locks, a custom React admin panel, 90+ tests, PHPStan static analysis, and 11 feature flags — all in a single one-time purchase. For Livewire teams, Larafast is the strongest alternative.',
        },
      },
      {
        '@type': 'Question',
        name: 'Is Laravel Jetstream a SaaS starter kit?',
        acceptedAnswer: {
          '@type': 'Answer',
          text: 'No. Laravel Jetstream is scaffolding — it provides authentication (login, register, password reset), profile management, and optional team support, but does not include Stripe billing, an admin panel, feature flags, webhooks, or a test suite. It is a starting point for building these things yourself, not a production-ready SaaS kit.',
        },
      },
      {
        '@type': 'Question',
        name: 'How does Laravel React Starter compare to ShipFast?',
        acceptedAnswer: {
          '@type': 'Answer',
          text: 'ShipFast is a Next.js (Node.js) SaaS starter. Laravel React Starter is its Laravel (PHP) equivalent. Both use React + TypeScript on the frontend and include Stripe billing, but the backend is fundamentally different. Laravel React Starter adds an admin panel, feature flags, webhooks, audit logging, 2FA, and 90+ tests that ShipFast does not include.',
        },
      },
      {
        '@type': 'Question',
        name: 'What is the difference between Laravel Spark and a full SaaS starter kit?',
        acceptedAnswer: {
          '@type': 'Answer',
          text: 'Laravel Spark is a billing library — it handles subscriptions and team billing via Stripe, but provides no frontend framework, no admin panel, no feature flags, and minimal tests. You build everything else yourself. A full SaaS starter kit like Laravel React Starter includes all of these out of the box.',
        },
      },
      {
        '@type': 'Question',
        name: 'Which Laravel SaaS boilerplate has the best test coverage?',
        acceptedAnswer: {
          '@type': 'Answer',
          text: 'Laravel React Starter ships with 90+ tests across Pest (PHP), Vitest (React), and Playwright (E2E), plus PHPStan static analysis at level 8. Most competitors include only basic PHP tests or none at all. Wave (open source) and SaaSyKit include some tests, but neither reaches this depth.',
        },
      },
    ],
  });

  return (
    <>
      <Head title={title}>
        <meta name="description" content={metaDescription} />
        <link rel="canonical" href={`${appUrl}/guides/saas-starter-kit-comparison-2026`} />
        <meta property="og:title" content={title} />
        <meta property="og:description" content={metaDescription} />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content={title} />
        <meta name="twitter:description" content={metaDescription} />
        {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: articleSchema.replace(/<\/script>/gi, '<\\/script>') }}
        />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: faqSchema.replace(/<\/script>/gi, '<\\/script>') }}
        />
      </Head>

      <div className="min-h-screen bg-background">
        {/* Navigation */}
        <nav className="container flex items-center justify-between py-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo className="h-8 w-8" />
            <TextLogo className="text-xl font-bold" />
          </Link>
          <div className="flex items-center gap-4">
            <Link href="/compare" className="hidden text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline">
              Compare
            </Link>
            <Link href="/features/billing" className="hidden text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline">
              Billing
            </Link>
            <Link href="/features/admin-panel" className="hidden text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline">
              Admin Panel
            </Link>
            <Button asChild variant="outline" size="sm">
              <Link href="/pricing">Pricing</Link>
            </Button>
          </div>
        </nav>

        <main className="container pb-24">
          <div className="mx-auto max-w-6xl">
            <div className="lg:grid lg:grid-cols-[1fr_280px] lg:gap-12">

              {/* Article */}
              <article>
                {/* Hero */}
                <header className="py-12">
                  <div className="flex flex-wrap gap-2 mb-4">
                    <span className="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                      Comparison Guide
                    </span>
                    <span className="inline-flex items-center rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground">
                      Updated March 2026
                    </span>
                    <span className="inline-flex items-center rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground">
                      ~20 min read
                    </span>
                  </div>
                  <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">
                    Best Laravel SaaS Starter Kits in 2026 &mdash; Complete Comparison Guide
                  </h1>
                  <p className="mt-6 text-lg text-muted-foreground leading-relaxed">
                    The best Laravel SaaS starter kit in 2026 depends on your frontend choice
                    (React vs. Livewire), budget, and whether you need a production admin panel
                    or can build one yourself. Here&apos;s the ranked list upfront:
                    <strong className="text-foreground"> Laravel React Starter</strong> for React
                    teams, <strong className="text-foreground">Larafast</strong> for Livewire teams,
                    <strong className="text-foreground"> Wave</strong> if budget is zero.
                    Everything else requires significant additions before going to production.
                  </p>
                </header>

                {/* Section 1 */}
                <section id="what-makes-production-ready" className="prose prose-neutral dark:prose-invert max-w-none">
                  <h2>What Makes a Production-Ready SaaS Starter Kit?</h2>
                  <p>
                    The phrase &ldquo;SaaS starter kit&rdquo; covers a wide range &mdash; from
                    Laravel Jetstream (pure scaffolding) to Laravel React Starter (full production
                    infrastructure). Before comparing options, it helps to agree on what
                    &ldquo;production-ready&rdquo; actually means.
                  </p>
                  <p>
                    A genuine production-ready SaaS starter ships these ten criteria without
                    requiring you to build them yourself:
                  </p>

                  <div className="not-prose my-6 overflow-x-auto">
                    <table className="w-full text-sm border-collapse">
                      <caption className="sr-only">Evaluation criteria for SaaS starter kits</caption>
                      <thead>
                        <tr className="border-b border-border bg-muted/50">
                          <th className="text-left py-3 px-4 font-semibold">Criterion</th>
                          <th className="text-left py-3 px-4 font-semibold">Bare minimum</th>
                          <th className="text-left py-3 px-4 font-semibold">Production standard</th>
                        </tr>
                      </thead>
                      <tbody>
                        {[
                          ['Auth', 'Login + register', 'Social auth, 2FA, rate limiting, audit logging, session management'],
                          ['Billing', 'Stripe Checkout link', 'double-charge prevention, multiple tiers, dunning, incomplete payment recovery'],
                          ['Admin panel', 'None', 'User management, health monitoring, audit log viewer, feature flag controls'],
                          ['2FA', 'None', 'TOTP with recovery codes, forced 2FA option'],
                          ['API tokens', 'None', 'Sanctum token management UI with permissions'],
                          ['Tests', 'None', '90+ Pest tests, Vitest frontend tests, PHPStan static analysis'],
                          ['TypeScript', 'None', 'Strict tsconfig, typed Inertia page props, typed components'],
                          ['Open source', 'N/A', 'Full source access (even if commercial license)'],
                          ['Price', 'Free or one-time', 'One-time preferred — avoid recurring license fees'],
                          ['Stack', 'Laravel', 'Laravel 12 + React 18 or Livewire 3'],
                        ].map(([criterion, bare, production]) => (
                          <tr key={criterion} className="border-b border-border hover:bg-muted/30 transition-colors">
                            <td className="py-3 px-4 font-medium">{criterion}</td>
                            <td className="py-3 px-4 text-muted-foreground">{bare}</td>
                            <td className="py-3 px-4">{production}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>

                  <p>
                    With these criteria in mind, here is how each kit stacks up.
                  </p>
                </section>

                {/* Section 2 */}
                <section id="comparison-matrix" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>Comparison Matrix</h2>
                  <p>
                    The table below covers all eight kits across the criteria most buyers ask
                    about. ✓ = included and production-grade; ✗ = not included; partial entries
                    explain the limitation.
                  </p>

                  <div className="not-prose my-6 overflow-x-auto">
                    <table className="w-full text-sm border-collapse">
                      <caption className="sr-only">Full feature comparison matrix for 8 Laravel SaaS starter kits</caption>
                      <thead>
                        <tr className="border-b border-border bg-muted/50">
                          <th className="text-left py-3 px-4 font-semibold whitespace-nowrap">Starter Kit</th>
                          <th className="text-center py-3 px-3 font-semibold">Billing</th>
                          <th className="text-center py-3 px-3 font-semibold">Admin</th>
                          <th className="text-center py-3 px-3 font-semibold">2FA</th>
                          <th className="text-center py-3 px-3 font-semibold">API Tokens</th>
                          <th className="text-center py-3 px-3 font-semibold">Tests</th>
                          <th className="text-center py-3 px-3 font-semibold">TypeScript</th>
                          <th className="text-left py-3 px-3 font-semibold">Price</th>
                          <th className="text-left py-3 px-3 font-semibold">Stack</th>
                        </tr>
                      </thead>
                      <tbody>
                        {[
                          {
                            name: 'Laravel React Starter',
                            isUs: true,
                            billing: '✓',
                            admin: '✓',
                            twofa: '✓',
                            tokens: '✓',
                            tests: '90+ Pest + Vitest',
                            ts: '✓',
                            price: 'One-time',
                            stack: 'Laravel + React',
                          },
                          {
                            name: 'Larafast',
                            isUs: false,
                            billing: '✓',
                            admin: 'Filament',
                            twofa: '✓',
                            tokens: 'Varies',
                            tests: 'Limited',
                            ts: 'Partial',
                            price: 'One-time',
                            stack: 'Laravel + Livewire',
                          },
                          {
                            name: 'SaaSyKit',
                            isUs: false,
                            billing: '✓',
                            admin: 'Filament',
                            twofa: '✓',
                            tokens: '✓',
                            tests: 'Yes',
                            ts: 'Partial',
                            price: 'One-time',
                            stack: 'Laravel + React',
                          },
                          {
                            name: 'Wave',
                            isUs: false,
                            billing: 'Via Spark',
                            admin: 'Filament',
                            twofa: '✓',
                            tokens: '✗',
                            tests: 'PHP only',
                            ts: '✗',
                            price: 'Free (MIT)',
                            stack: 'Laravel + Blade',
                          },
                          {
                            name: 'Laravel Spark',
                            isUs: false,
                            billing: '✓',
                            admin: '✗',
                            twofa: '✗',
                            tokens: '✗',
                            tests: 'Minimal',
                            ts: 'BYO',
                            price: '$99/year',
                            stack: 'Laravel + BYO UI',
                          },
                          {
                            name: 'ShipFast',
                            isUs: false,
                            billing: '✓',
                            admin: '✗',
                            twofa: '✗',
                            tokens: '✗',
                            tests: 'Varies',
                            ts: '✓',
                            price: 'One-time',
                            stack: 'Next.js + React',
                          },
                          {
                            name: 'SupaStarter',
                            isUs: false,
                            billing: '✓',
                            admin: 'Limited',
                            twofa: 'Via Supabase',
                            tokens: '✗',
                            tests: 'Varies',
                            ts: '✓',
                            price: 'One-time',
                            stack: 'Next.js + React',
                          },
                          {
                            name: 'Laravel Jetstream',
                            isUs: false,
                            billing: '✗',
                            admin: '✗',
                            twofa: '✓',
                            tokens: '✓',
                            tests: 'Basic',
                            ts: '✗',
                            price: 'Free (MIT)',
                            stack: 'Laravel + Vue/Livewire',
                          },
                        ].map((row) => (
                          <tr
                            key={row.name}
                            className={`border-b border-border transition-colors ${
                              row.isUs ? 'bg-primary/5 font-medium' : 'hover:bg-muted/30'
                            }`}
                          >
                            <td className="py-3 px-4 whitespace-nowrap">
                              {row.name}
                              {row.isUs && (
                                <span className="ml-2 inline-flex items-center rounded-full bg-primary/15 px-2 py-0.5 text-xs font-semibold text-primary">
                                  Our pick
                                </span>
                              )}
                            </td>
                            <td className="py-3 px-3 text-center">{row.billing}</td>
                            <td className="py-3 px-3 text-center">{row.admin}</td>
                            <td className="py-3 px-3 text-center">{row.twofa}</td>
                            <td className="py-3 px-3 text-center">{row.tokens}</td>
                            <td className="py-3 px-3 text-center whitespace-nowrap">{row.tests}</td>
                            <td className="py-3 px-3 text-center">{row.ts}</td>
                            <td className="py-3 px-3 whitespace-nowrap">{row.price}</td>
                            <td className="py-3 px-3 whitespace-nowrap">{row.stack}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </section>

                {/* Section 3 - Laravel React Starter */}
                <section id="pick-1-laravel-react-starter" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>#1 &mdash; Laravel React Starter (Our Pick)</h2>
                  <p>
                    <strong>Who it&apos;s for:</strong> Laravel developers building a SaaS with
                    React + TypeScript who want production infrastructure on day one &mdash; not
                    scaffolding they need to extend for three months before launch.
                  </p>
                  <p>
                    <strong>What it includes:</strong> Laravel 12 + React 18 + TypeScript +
                    Inertia.js SSR. Production Stripe billing with double-charge prevention (prevents
                    double-charge race conditions), 4 plan tiers, team seats, dunning emails, and
                    incomplete payment recovery. A custom React admin panel with user management,
                    health monitoring (DB, cache, queue, disk), audit log viewer, and feature flag
                    controls. 11 feature flags with database overrides and per-user targeting.
                    HMAC-signed outgoing webhooks, incoming webhook processing (GitHub, Stripe),
                    social auth (Google + GitHub), TOTP 2FA, Sanctum API tokens, and a full
                    security layer (12 rate limits, CSP headers, CSRF, session management).
                  </p>
                  <p>
                    <strong>Tests:</strong> 90+ Pest tests, Vitest component tests, Playwright
                    E2E, PHPStan at level 8, Laravel Pint, ESLint, and GitHub Actions CI.
                  </p>
                  <p>
                    <strong>Price:</strong> One-time purchase. Full source access.
                  </p>

                  <h3>Pros</h3>
                  <ul>
                    <li>Most complete feature set of any Laravel SaaS starter</li>
                    <li>React + TypeScript admin panel (not Filament/Livewire) means one consistent stack</li>
                    <li>Billing layer is production-hardened, not a Stripe Checkout wrapper</li>
                    <li>90+ tests from day one — the only kit with real CI gates</li>
                    <li>11 feature flags with DB overrides enable controlled rollouts without a third-party service</li>
                  </ul>

                  <h3>Cons</h3>
                  <ul>
                    <li>Commercial license — not free</li>
                    <li>React/TypeScript — not suitable for Livewire-only teams</li>
                    <li>No built-in blog or CMS (add separately if needed)</li>
                  </ul>

                  <p>
                    <Link href="/pricing" className="text-primary hover:underline font-medium">View pricing →</Link>
                    {' · '}
                    <Link href="/features/billing" className="text-primary hover:underline font-medium">Billing details →</Link>
                    {' · '}
                    <Link href="/features/admin-panel" className="text-primary hover:underline font-medium">Admin panel details →</Link>
                  </p>
                </section>

                {/* Section 4 - Larafast */}
                <section id="pick-2-larafast" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>#2 &mdash; Larafast</h2>
                  <p>
                    Larafast targets the same solo-founder market as Laravel React Starter but
                    with Livewire and Blade as the primary stack (a React add-on exists).
                    It ships Stripe billing, social auth, 2FA, a Filament-based admin panel,
                    and a reasonable onboarding flow &mdash; all for a one-time purchase.
                  </p>
                  <p>
                    The main limitation is test coverage: the test suite is thin, PHPStan is
                    not standard, and there are no TypeScript frontend tests. For solo founders
                    who prefer Livewire and are comfortable adding their own tests, it&apos;s
                    a solid second choice. For teams that need TypeScript end-to-end and CI gates
                    from day one, Laravel React Starter is stronger.
                  </p>
                  <p>
                    <Link href="/compare/larafast" className="text-primary hover:underline font-medium">
                      Full comparison: Laravel React Starter vs Larafast →
                    </Link>
                  </p>
                </section>

                {/* Section 5 - SaaSyKit */}
                <section id="pick-3-saasykit" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>#3 &mdash; SaaSyKit</h2>
                  <p>
                    SaaSyKit uses Laravel + React (Inertia), making it the closest architectural
                    match to Laravel React Starter. The key difference is the admin panel:
                    SaaSyKit uses Filament (a Livewire-based PHP admin framework) rather than
                    a custom React admin. This means your admin panel runs on a different stack
                    than your main app, which creates a TypeScript boundary and makes admin
                    customizations harder to test.
                  </p>
                  <p>
                    SaaSyKit includes Stripe billing, 2FA, social auth, and API tokens.
                    Test coverage is present but the depth of CI gates is below Laravel React
                    Starter. For teams already comfortable with Filament, it&apos;s a reasonable
                    choice.
                  </p>
                  <p>
                    <Link href="/compare/saasykit" className="text-primary hover:underline font-medium">
                      Full comparison: Laravel React Starter vs SaaSyKit →
                    </Link>
                  </p>
                </section>

                {/* Section 6 - Wave */}
                <section id="pick-4-wave" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>#4 &mdash; Wave (Free Tier Pick)</h2>
                  <p>
                    Wave is the strongest free (MIT) Laravel SaaS starter. It includes a Blade +
                    Livewire frontend, Filament admin, social auth, 2FA, and a basic
                    subscription layer via Laravel Spark. The blog and announcements feature
                    is a genuine differentiator &mdash; no other kit in this list ships one.
                  </p>
                  <p>
                    The limitations are significant for production use: no TypeScript, no Vitest
                    tests, and billing is delegated to Laravel Spark (a separate $99/year
                    dependency). For projects where budget is the primary constraint, Wave is
                    the best free starting point. For teams that need React + TypeScript or a
                    hardened billing layer, the free price doesn&apos;t offset the additional
                    build time.
                  </p>
                  <p>
                    <Link href="/compare/wave" className="text-primary hover:underline font-medium">
                      Full comparison: Laravel React Starter vs Wave →
                    </Link>
                  </p>
                </section>

                {/* Section 7 - Laravel Spark */}
                <section id="pick-5-laravel-spark" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>#5 &mdash; Laravel Spark</h2>
                  <p>
                    Laravel Spark is a billing library, not a SaaS starter kit. It handles
                    Stripe subscriptions and team billing, but provides no frontend framework,
                    no admin panel, no feature flags, and no meaningful test suite. You bring
                    your own UI (Blade, Livewire, or React) and build everything else.
                  </p>
                  <p>
                    Spark costs $99/year (recurring). For projects that only need billing
                    infrastructure and already have the rest of the stack built, Spark is
                    purpose-built for that case. For anyone starting from scratch, a full starter
                    kit is a better value.
                  </p>
                  <p>
                    <Link href="/compare/laravel-spark" className="text-primary hover:underline font-medium">
                      Full comparison: Laravel React Starter vs Laravel Spark →
                    </Link>
                  </p>
                </section>

                {/* Section 8 - ShipFast */}
                <section id="pick-6-shipfast" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>#6 &mdash; ShipFast (Next.js &mdash; included for context)</h2>
                  <p>
                    ShipFast is not a Laravel starter kit. It&apos;s a Next.js (Node.js) SaaS
                    starter that targets the same indie hacker market. It&apos;s included here
                    because it&apos;s the most common comparison point for developers choosing
                    between a PHP backend and a Node.js backend.
                  </p>
                  <p>
                    ShipFast includes Stripe billing, React + TypeScript, a built-in MDX blog,
                    and strong SEO tooling. It does not include an admin panel, feature flags,
                    audit logging, 2FA, or HMAC-signed webhooks. Its deployment model is
                    Vercel-first (serverless), which suits Node.js teams but doesn&apos;t map to
                    the Laravel VPS deployment model.
                  </p>
                  <p>
                    If you&apos;re a Node.js developer, ShipFast is a strong choice. If you&apos;re
                    a Laravel developer, Laravel React Starter is the equivalent &mdash; same
                    React + TypeScript frontend philosophy, Laravel backend instead of Node.js.
                  </p>
                  <p>
                    <Link href="/compare/shipfast" className="text-primary hover:underline font-medium">
                      Full comparison: Laravel React Starter vs ShipFast →
                    </Link>
                  </p>
                </section>

                {/* Section 9 - SupaStarter */}
                <section id="pick-7-supastarter" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>#7 &mdash; SupaStarter</h2>
                  <p>
                    SupaStarter uses Supabase as the backend (managed PostgreSQL + Auth +
                    Storage) with Next.js on the frontend. The main appeal is rapid prototyping:
                    Supabase eliminates server management and provides a generous free tier.
                    The main limitation is vendor lock-in &mdash; your auth, database, file
                    storage, and realtime layer are all managed Supabase services. Migrating
                    away from Supabase after launch is non-trivial.
                  </p>
                  <p>
                    SupaStarter includes Stripe billing and React + TypeScript, but has a
                    limited admin panel, no feature flags, and no HMAC-signed webhooks. For
                    Laravel developers, the Supabase architecture requires a different mental
                    model (no Eloquent, no Artisan, no Pest).
                  </p>
                  <p>
                    <Link href="/compare/supastarter" className="text-primary hover:underline font-medium">
                      Full comparison: Laravel React Starter vs SupaStarter →
                    </Link>
                  </p>
                </section>

                {/* Section 10 - Jetstream */}
                <section id="pick-8-jetstream" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>#8 &mdash; Laravel Jetstream (Scaffolding, Not a SaaS Kit)</h2>
                  <p>
                    Laravel Jetstream is the official Laravel scaffolding package. It ships
                    authentication (login, register, password reset, email verification), profile
                    management, team support (optional), API tokens (Sanctum), and 2FA.
                    It is free and MIT-licensed.
                  </p>
                  <p>
                    Jetstream is ranked last not because it&apos;s bad, but because it&apos;s
                    not a SaaS starter kit. It provides none of the following: Stripe billing,
                    admin panel, feature flags, webhooks, audit logging, or a meaningful test
                    suite. It is a solid foundation for building these things yourself, which is
                    the right choice for some projects. For founders who want to start selling
                    on day one, Jetstream is the beginning of a 2&ndash;3 month infrastructure
                    build.
                  </p>
                  <p>
                    <Link href="/compare/laravel-jetstream" className="text-primary hover:underline font-medium">
                      Full comparison: Laravel React Starter vs Laravel Jetstream →
                    </Link>
                  </p>
                </section>

                {/* Section 11 - Price Comparison */}
                <section id="price-comparison" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>Price Comparison</h2>
                  <p>
                    Pricing ranges from free (Jetstream, Wave) to recurring ($99/year for Spark)
                    to one-time commercial licenses. One-time purchases are generally preferable
                    for indie founders: no ongoing license cost, full source access, and no
                    renewal risk.
                  </p>

                  <div className="not-prose my-6 overflow-x-auto">
                    <table className="w-full text-sm border-collapse">
                      <caption className="sr-only">Price comparison for 8 Laravel SaaS starter kits</caption>
                      <thead>
                        <tr className="border-b border-border bg-muted/50">
                          <th className="text-left py-3 px-4 font-semibold">Starter Kit</th>
                          <th className="text-left py-3 px-4 font-semibold">Price</th>
                          <th className="text-left py-3 px-4 font-semibold">Model</th>
                          <th className="text-left py-3 px-4 font-semibold">License</th>
                        </tr>
                      </thead>
                      <tbody>
                        {[
                          { name: 'Laravel React Starter', price: 'See pricing', model: 'One-time', license: 'Commercial' },
                          { name: 'Larafast', price: 'One-time', model: 'One-time', license: 'Commercial' },
                          { name: 'SaaSyKit', price: 'One-time', model: 'One-time', license: 'Commercial' },
                          { name: 'Wave', price: 'Free', model: 'Free', license: 'MIT' },
                          { name: 'Laravel Spark', price: '$99/year', model: 'Annual subscription', license: 'Commercial' },
                          { name: 'ShipFast', price: 'One-time', model: 'One-time', license: 'Commercial' },
                          { name: 'SupaStarter', price: 'One-time', model: 'One-time', license: 'Commercial' },
                          { name: 'Laravel Jetstream', price: 'Free', model: 'Free', license: 'MIT' },
                        ].map((row) => (
                          <tr key={row.name} className="border-b border-border hover:bg-muted/30 transition-colors">
                            <td className="py-3 px-4 font-medium">{row.name}</td>
                            <td className="py-3 px-4">{row.price}</td>
                            <td className="py-3 px-4">{row.model}</td>
                            <td className="py-3 px-4">{row.license}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>

                  <p>
                    Laravel Spark&apos;s annual model is worth calling out: at $99/year, it costs
                    more than most one-time starters after two years, and you only get billing
                    infrastructure without the rest of the stack. One-time commercial kits are
                    a better long-term value.
                  </p>
                </section>

                {/* Section 12 - Final Recommendation */}
                <section id="final-recommendation" className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>Our Final Recommendation</h2>
                  <p>
                    For Laravel developers building a React-based SaaS in 2026, Laravel React
                    Starter is the strongest option in the market: the only kit that ships
                    production billing (concurrent payment protection), a custom React admin panel,
                    90+ tests, PHPStan static analysis, and 11 feature flags as a single
                    one-time purchase. For Livewire teams, Larafast is the best alternative.
                    For zero-budget projects, Wave provides a solid foundation with the
                    understanding that billing and TypeScript require additional work.
                  </p>
                </section>

                {/* FAQ */}
                <section className="prose prose-neutral dark:prose-invert max-w-none mt-12">
                  <h2>Frequently Asked Questions</h2>

                  <h3>What is the best Laravel SaaS starter kit in 2026?</h3>
                  <p>
                    Laravel React Starter ranks first for React + TypeScript teams. It ships the
                    most complete feature set: production billing, custom admin panel, 90+ tests,
                    and 11 feature flags. For Livewire teams, Larafast is the strongest alternative.
                  </p>

                  <h3>Is Laravel Jetstream a SaaS starter kit?</h3>
                  <p>
                    No. Jetstream is scaffolding: it gives you auth and profile management, but
                    no billing, no admin panel, no feature flags, and no meaningful test suite.
                    It&apos;s a starting point for building a SaaS, not a production-ready kit.
                  </p>

                  <h3>How does Laravel React Starter compare to ShipFast?</h3>
                  <p>
                    ShipFast is Next.js (Node.js). Laravel React Starter is its Laravel (PHP)
                    equivalent. Same React + TypeScript frontend, different backend. Laravel React
                    Starter adds an admin panel, feature flags, webhooks, audit logging, and 90+
                    tests that ShipFast does not include.{' '}
                    <Link href="/compare/shipfast" className="text-primary hover:underline">See the full comparison.</Link>
                  </p>

                  <h3>What is the difference between Laravel Spark and a full SaaS starter kit?</h3>
                  <p>
                    Laravel Spark is a billing library only: subscriptions + team billing via
                    Stripe. No frontend, no admin, no feature flags, no tests. Full starter kits
                    include all of these. Spark costs $99/year; most one-time starter kits are
                    cheaper after two years.{' '}
                    <Link href="/compare/laravel-spark" className="text-primary hover:underline">See the Laravel Spark comparison.</Link>
                  </p>

                  <h3>Which Laravel SaaS boilerplate has the best test coverage?</h3>
                  <p>
                    Laravel React Starter: 90+ Pest tests, Vitest frontend tests, Playwright E2E,
                    and PHPStan at level 8. No other kit in this comparison comes close to this
                    test depth with full CI gate enforcement.
                  </p>
                </section>

                {/* CTA */}
                <div className="mt-16 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
                  <h2 className="text-2xl font-bold">Ready to build your SaaS?</h2>
                  <p className="mt-2 text-muted-foreground">
                    Laravel React Starter ships everything in this guide, ready to deploy.
                  </p>
                  <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                    <Button asChild size="lg">
                      <Link href="/pricing">
                        View pricing
                        <ArrowRight className="ml-2 h-4 w-4" />
                      </Link>
                    </Button>
                    <Button asChild variant="outline" size="lg">
                      <Link href="/compare">See all comparisons</Link>
                    </Button>
                  </div>
                </div>
              </article>

              {/* Table of Contents (sticky sidebar) */}
              <aside className="hidden lg:block">
                <div className="sticky top-8">
                  <TableOfContents sections={sections} />
                </div>
              </aside>

            </div>
          </div>
        </main>

        {/* Footer */}
        <footer className="border-t py-8">
          <div className="container">
            <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
              <p className="text-sm text-muted-foreground">
                &copy; {new Date().getFullYear()} {appName}. All rights reserved.
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
