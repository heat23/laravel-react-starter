import {
  ArrowRight,
  CheckCircle2,
  Clock,
  Code2,
  Layers3,
  Rocket,
  Shield,
  Sparkles,
  Users,
  Zap,
} from 'lucide-react';

import DOMPurify from 'dompurify';
import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { Button } from '@/Components/ui/button';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';

interface FaqItem {
  question: string;
  answer: string;
}

interface WelcomeProps {
  canLogin: boolean;
  canRegister: boolean;
  faqs?: FaqItem[];
  featureCount?: number;
  testCount?: number;
  planCount?: number;
}

const features = [
  {
    icon: Shield,
    title: 'Secure by default',
    description:
      '12 rate limits, CSRF protection, security headers, 2FA, audit logging, and session management — all configured out of the box.',
    link: null,
  },
  {
    icon: Layers3,
    title: '11 feature flags',
    description:
      'Toggle billing, webhooks, admin panel, social auth, and more. Ship only what your product needs — disable the rest with one env var.',
    link: '/features/feature-flags',
  },
  {
    icon: Zap,
    title: 'Production-grade billing',
    description:
      'Redis-locked Stripe mutations prevent race conditions. 4 plan tiers, team seats, dunning emails, and incomplete payment recovery.',
    link: '/features/billing',
  },
];

const personas = [
  {
    icon: Rocket,
    title: 'Solo founders',
    description:
      'Skip 2-3 months of boilerplate. Auth, billing, admin panel, and email sequences ready on day one.',
  },
  {
    icon: Users,
    title: 'Small teams',
    description:
      'Onboard your team with TypeScript, Pest tests, and CI/CD already configured. Focus on your product, not infrastructure.',
  },
  {
    icon: Code2,
    title: 'Agencies',
    description:
      'Start every client project from a tested, documented base. Feature flags let you customize scope per engagement.',
  },
];

const techStack = [
  'Laravel 12',
  'React 18',
  'TypeScript',
  'Tailwind CSS v4',
  'Inertia.js',
];

type WelcomeComponent = ((props: WelcomeProps) => JSX.Element) & {
  disableGlobalUi?: boolean;
};

const Welcome: WelcomeComponent = ({
  canLogin,
  canRegister,
  faqs = [],
  featureCount = 11,
  testCount = 90,
  planCount = 4,
}) => {
  const appName = import.meta.env.VITE_APP_NAME || 'Laravel React Starter';
  const { track } = useAnalytics();

  const faqSchema = JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqs.map((faq) => ({
      '@type': 'Question',
      name: faq.question,
      acceptedAnswer: {
        '@type': 'Answer',
        text: faq.answer,
      },
    })),
  });

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'welcome' });
  }, [track]);

  return (
    <>
      <Head title={`${appName} — Laravel React SaaS Starter Kit`}>
        <meta
          name="description"
          content={`A production-ready Laravel + React starter with ${featureCount} feature flags, ${planCount} billing tiers, and ${testCount}+ tests. Auth, admin panel, Stripe billing, and email sequences — ready to ship.`}
        />
        <meta
          property="og:title"
          content={`${appName} — Ship your SaaS in days, not months`}
        />
        <meta
          property="og:description"
          content={`${featureCount} toggleable features, Redis-locked billing, production admin panel. Laravel 12 + React 18 + TypeScript.`}
        />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta
          name="twitter:title"
          content={`${appName} — Ship your SaaS in days, not months`}
        />
        <meta
          name="twitter:description"
          content={`${featureCount} toggleable features, Redis-locked billing, production admin panel. Laravel 12 + React 18 + TypeScript.`}
        />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(faqSchema) }}
        />
      </Head>

      <a
        href="#main-content"
        className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-background focus:text-foreground focus:border focus:border-border focus:rounded-md"
      >
        Skip to content
      </a>

      <div className="relative min-h-screen overflow-hidden bg-gradient-to-b from-background via-background to-muted/30">
        <div
          aria-hidden="true"
          className="absolute inset-x-0 top-0 h-[32rem] bg-[radial-gradient(circle_at_top_right,_hsl(var(--primary)/0.18),_transparent_34%),radial-gradient(circle_at_top_left,_hsl(var(--accent)/0.12),_transparent_28%)]"
        />
        <div
          aria-hidden="true"
          className="absolute inset-x-6 top-28 mx-auto hidden h-64 max-w-5xl rounded-[2.5rem] border border-border/60 bg-card/50 blur-3xl lg:block"
        />

        {/* Navigation */}
        <nav className="container relative z-10 flex items-center justify-between py-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo className="h-8 w-8" />
            <TextLogo className="text-xl font-bold" />
          </Link>

          <div className="flex items-center gap-4">
            <Link
              href="/features/billing"
              className="hidden text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline"
            >
              Billing
            </Link>
            <Link
              href="/features/feature-flags"
              className="hidden text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline"
            >
              Feature Flags
            </Link>
            <Link
              href="/features/admin-panel"
              className="hidden text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline"
            >
              Admin Panel
            </Link>
            {canLogin && (
              <Button variant="ghost" asChild>
                <Link href={route('login')}>Log in</Link>
              </Button>
            )}
            {canRegister && (
              <Button asChild>
                <Link href={route('register')}>
                  Get Started
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Link>
              </Button>
            )}
          </div>
        </nav>

        <main id="main-content">
          {/* Hero Section */}
          <section className="container relative z-10 py-24">
            <div className="mx-auto max-w-5xl">
              <div className="mx-auto max-w-3xl text-center">
                <div className="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
                  <Sparkles className="h-4 w-4" />
                  {featureCount} features, {testCount}+ tests, ready to ship
                </div>
                <h1 className="mt-6 text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl">
                  Ship your SaaS
                  <br />
                  <span className="text-primary">in days, not months</span>
                </h1>
                <p className="mt-6 text-lg text-muted-foreground md:text-xl">
                  A production-ready Laravel + React starter with
                  authentication, {featureCount} toggleable feature flags,
                  Redis-locked billing, and a full admin panel. Stop rebuilding
                  the same infrastructure.
                </p>
              </div>

              <div className="flex flex-wrap items-center justify-center gap-4 pt-4">
                {canRegister && (
                  <Button size="lg" asChild>
                    <Link href={route('register')}>
                      Create Your First Account
                      <ArrowRight className="ml-2 h-4 w-4" />
                    </Link>
                  </Button>
                )}
                <Button variant="outline" size="lg" asChild>
                  <a
                    href="https://github.com/your-org/laravel-react-starter#readme"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    Documentation
                  </a>
                </Button>
              </div>

              {/* Key stats */}
              <div className="mt-12 grid gap-4 md:grid-cols-3">
                {[
                  `${featureCount} toggleable feature flags`,
                  `${planCount} billing tiers with Redis-locked mutations`,
                  `${testCount}+ tests across Pest, Vitest & Playwright`,
                ].map((highlight) => (
                  <div
                    key={highlight}
                    className="rounded-2xl border border-border/70 bg-card/80 px-5 py-4 text-sm font-medium text-foreground shadow-sm backdrop-blur"
                  >
                    {highlight}
                  </div>
                ))}
              </div>
            </div>
          </section>

          {/* Features Section */}
          <section className="container py-24">
            <div className="mx-auto max-w-5xl">
              <h2 className="mb-12 text-center text-3xl font-bold">
                Everything you need to launch
              </h2>
              <div className="grid gap-8 md:grid-cols-3">
                {features.map((feature) => {
                  const card = (
                    <>
                      <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                        <feature.icon
                          className="h-5 w-5 text-primary"
                          aria-hidden="true"
                        />
                      </div>
                      <h3 className="mb-2 text-lg font-semibold">
                        {feature.title}
                      </h3>
                      <p className="text-sm text-muted-foreground">
                        {feature.description}
                      </p>
                      {feature.link && (
                        <span className="mt-3 inline-flex items-center text-sm font-medium text-primary">
                          Learn more
                          <ArrowRight className="ml-1 h-3 w-3" />
                        </span>
                      )}
                    </>
                  );
                  return feature.link ? (
                    <Link
                      key={feature.title}
                      href={feature.link}
                      className="rounded-2xl border border-border/70 bg-card p-6 text-card-foreground shadow-sm transition-colors hover:border-primary/30"
                    >
                      {card}
                    </Link>
                  ) : (
                    <div
                      key={feature.title}
                      className="rounded-2xl border border-border/70 bg-card p-6 text-card-foreground shadow-sm"
                    >
                      {card}
                    </div>
                  );
                })}
              </div>
            </div>
          </section>

          {/* Before vs After Section */}
          <section className="container py-24">
            <div className="mx-auto max-w-4xl">
              <h2 className="mb-12 text-center text-3xl font-bold">
                Skip the boilerplate
              </h2>
              <div className="grid gap-6 md:grid-cols-2">
                <div className="rounded-2xl border border-destructive/30 bg-destructive/5 p-8">
                  <div className="mb-4 flex items-center gap-2 text-lg font-semibold text-destructive">
                    <Clock className="h-5 w-5" />
                    Without this starter
                  </div>
                  <ul className="space-y-3 text-sm text-muted-foreground">
                    <li>2-3 months building auth, billing, admin</li>
                    <li>Rolling your own rate limiting and security headers</li>
                    <li>No tests until &ldquo;later&rdquo; (never)</li>
                    <li>Race conditions in billing on day one of launch</li>
                    <li>
                      Rebuilding the same infrastructure for every project
                    </li>
                  </ul>
                </div>
                <div className="rounded-2xl border border-success/30 bg-success/5 p-8">
                  <div className="mb-4 flex items-center gap-2 text-lg font-semibold text-success">
                    <Rocket className="h-5 w-5" />
                    With this starter
                  </div>
                  <ul className="space-y-3 text-sm text-muted-foreground">
                    <li>
                      2-3 days to your first deploy with real features enabled
                    </li>
                    <li>
                      12 rate limits and security headers already configured
                    </li>
                    <li>
                      {testCount}+ tests from day one — Pest, Vitest, Playwright
                    </li>
                    <li>Redis-locked billing prevents double charges</li>
                    <li>Toggle features off with one env var</li>
                  </ul>
                </div>
              </div>
            </div>
          </section>

          {/* Who is this for? */}
          <section className="container py-24">
            <div className="mx-auto max-w-5xl">
              <h2 className="mb-12 text-center text-3xl font-bold">
                Built for builders
              </h2>
              <div className="grid gap-8 md:grid-cols-3">
                {personas.map((persona) => (
                  <div
                    key={persona.title}
                    className="rounded-2xl border border-border/70 bg-card p-6 text-card-foreground shadow-sm"
                  >
                    <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                      <persona.icon className="h-5 w-5 text-primary" />
                    </div>
                    <h3 className="mb-2 text-lg font-semibold">
                      {persona.title}
                    </h3>
                    <p className="text-sm text-muted-foreground">
                      {persona.description}
                    </p>
                  </div>
                ))}
              </div>
            </div>
          </section>

          {/* Tech Stack Section */}
          <section className="container border-t py-24">
            <div className="mx-auto max-w-4xl text-center">
              <h2 className="mb-8 text-2xl font-bold">
                Modern stack, ready to customize
              </h2>
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                {techStack.map((item) => (
                  <div
                    key={item}
                    className="flex items-center justify-center gap-2 rounded-2xl border border-border/70 bg-card/70 px-4 py-3 text-sm font-medium text-foreground"
                  >
                    <CheckCircle2 className="h-4 w-4 text-success" />
                    <span>{item}</span>
                  </div>
                ))}
              </div>
            </div>
          </section>
        </main>

        {/* Footer */}
        <footer className="border-t py-8">
          <div className="container">
            <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
              <p className="text-sm text-muted-foreground">
                &copy; {new Date().getFullYear()} {appName}. All rights
                reserved.
              </p>
              <nav className="flex items-center gap-4 text-sm text-muted-foreground">
                <Link
                  href="/contact"
                  className="hover:text-foreground transition-colors"
                >
                  Contact
                </Link>
                <Link
                  href="/changelog"
                  className="hover:text-foreground transition-colors"
                >
                  Changelog
                </Link>
                <Link
                  href="/roadmap"
                  className="hover:text-foreground transition-colors"
                >
                  Roadmap
                </Link>
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
};

Welcome.disableGlobalUi = true;

export default Welcome;
