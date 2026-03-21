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
import { useEffect, useRef, useState } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { AnnouncementBanner, type AnnouncementBannerProps } from '@/Components/layout/AnnouncementBanner';
import { Button } from '@/Components/ui/button';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';

interface FaqItem {
  question: string;
  answer: string;
}

interface Testimonial {
  quote: string;
  name: string;
  role: string;
}

const DEFAULT_TESTIMONIALS: Testimonial[] = [
  {
    quote:
      "Saved me 2 months of boilerplate. The Redis-locked billing alone is worth the price.",
    name: 'Alex M.',
    role: 'Senior Developer, Solo SaaS Founder',
  },
  {
    quote:
      'Finally, a Laravel starter that actually includes tests. 90+ tests meant I could refactor confidently from day one.',
    name: 'Sarah K.',
    role: 'Solo Founder',
  },
  {
    quote:
      'We use this as our agency base for every client project. Feature flags let us customize scope per engagement.',
    name: 'James T.',
    role: 'Agency Lead',
  },
];

interface WelcomeProps {
  canLogin: boolean;
  canRegister: boolean;
  faqs?: FaqItem[];
  testimonials?: Testimonial[];
  featureCount?: number;
  testCount?: number;
  planCount?: number;
  githubStars?: number;
  userCount?: number;
  appUrl?: string;
  announcementBanner?: AnnouncementBannerProps | null;
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
  testimonials = DEFAULT_TESTIMONIALS,
  featureCount = 11,
  testCount = 90,
  planCount = 4,
  githubStars,
  userCount = 100,
  appUrl = '',
  announcementBanner = null,
}) => {
  const appName = import.meta.env.VITE_APP_NAME || 'Laravel React Starter';
  const { track } = useAnalytics();
  const heroCTARef = useRef<HTMLDivElement | null>(null);
  const [showStickyMobileCTA, setShowStickyMobileCTA] = useState(false);

  const ogImageUrl = appUrl
    ? `${appUrl}/og-image.png`
    : '/og-image.png';

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

  // Sticky mobile CTA: show when hero CTA scrolls out of view
  useEffect(() => {
    if (!canRegister) return;
    const el = heroCTARef.current;
    if (!el) return;
    const observer = new IntersectionObserver(
      ([entry]) => setShowStickyMobileCTA(!entry.isIntersecting),
      { threshold: 0 }
    );
    observer.observe(el);
    return () => observer.disconnect();
  }, [canRegister]);

  return (
    <>
      <Head title={`${appName} — Laravel React SaaS Starter Kit`}>
        <meta
          name="description"
          content={`A production-ready Laravel + React starter with ${featureCount} feature flags, ${planCount} billing tiers, and ${testCount}+ tests. Auth, admin panel, Stripe billing, and email sequences — ready to ship.`}
        />
        <meta
          property="og:title"
          content={`${appName} — Ship a production-ready Laravel + React SaaS in hours, not weeks`}
        />
        <meta
          property="og:description"
          content={`${featureCount} toggleable features, Redis-locked billing, production admin panel. Laravel 12 + React 18 + TypeScript. Built for indie developers and small teams.`}
        />
        <meta property="og:type" content="website" />
        <meta property="og:image" content={ogImageUrl} />
        <meta name="twitter:card" content="summary_large_image" />
        <meta
          name="twitter:title"
          content={`${appName} — Ship a production-ready Laravel + React SaaS in hours, not weeks`}
        />
        <meta
          name="twitter:description"
          content={`${featureCount} toggleable features, Redis-locked billing, production admin panel. Laravel 12 + React 18 + TypeScript. Built for indie developers and small teams.`}
        />
        <meta name="twitter:image" content={ogImageUrl} />
        {faqs.length > 0 && (
          <script
            type="application/ld+json"
            dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(faqSchema) }}
          />
        )}
      </Head>

      <a
        href="#main-content"
        className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-background focus:text-foreground focus:border focus:border-border focus:rounded-md"
      >
        Skip to content
      </a>

      {announcementBanner && <AnnouncementBanner {...announcementBanner} />}

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
            <Link
              href="/pricing"
              className="hidden items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline-flex"
            >
              Pricing
              <span
                className="rounded-full bg-primary/10 px-1.5 py-0.5 text-xs font-medium text-primary"
                aria-label="Save 20% with annual billing"
              >
                Save 20%
              </span>
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
                  Ship a production-ready
                  <br />
                  <span className="text-primary">Laravel + React SaaS</span>
                  <br />
                  in hours, not weeks
                </h1>
                <p className="mt-6 text-lg text-muted-foreground md:text-xl">
                  A production-ready Laravel + React starter with
                  authentication, {featureCount} toggleable feature flags,
                  Redis-locked billing, and a full admin panel. Built for indie
                  developers and small teams who need to ship without the
                  scaffolding tax.
                </p>
              </div>

              <div ref={heroCTARef} className="flex flex-wrap items-center justify-center gap-4 pt-4">
                {canRegister && (
                  <Button
                    size="lg"
                    asChild
                    onClick={() =>
                      track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, {
                        source: 'hero_primary',
                        label: 'Get the Starter Kit',
                      })
                    }
                  >
                    <Link href={route('register')}>
                      Get Started Free
                      <ArrowRight className="ml-2 h-4 w-4" />
                    </Link>
                  </Button>
                )}
                <Button variant="outline" size="lg" asChild>
                  <Link href="/features/billing">
                    See All Features
                  </Link>
                </Button>
              </div>
              <p className="mt-3 text-center text-xs text-muted-foreground">
                No credit card required · Deploy in minutes
              </p>

              {/* Price anchor — sets expectation before /pricing click */}
              <p className="mt-4 text-center text-sm text-muted-foreground">
                <Link
                  href="/pricing"
                  className="hover:text-foreground transition-colors"
                  onClick={() =>
                    track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, {
                      source: 'hero_price_anchor',
                    })
                  }
                >
                  One-time purchase · No subscription · Full source code
                </Link>
              </p>

              {/* Key stats */}
              <div className="mt-12 grid gap-4 md:grid-cols-3">
                {[
                  `${featureCount} toggleable feature flags`,
                  `From clone to first deploy in 2–3 days`,
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

          {/* Social Proof Section */}
          <section aria-label="Social proof" className="container border-t py-24">
            <div className="mx-auto max-w-5xl">
              {/* Metrics bar */}
              <div className="mb-12 flex flex-wrap items-center justify-center gap-8 text-center">
                {githubStars !== undefined && (
                  <>
                    <div className="flex flex-col items-center gap-1">
                      <span className="text-3xl font-bold text-primary">
                        ★ {githubStars.toLocaleString()}
                      </span>
                      <span className="text-sm text-muted-foreground">GitHub stars</span>
                    </div>
                    <div className="hidden h-10 w-px bg-border sm:block" />
                  </>
                )}
                <div className="flex flex-col items-center gap-1">
                  <span className="text-3xl font-bold">{userCount}+</span>
                  <span className="text-sm text-muted-foreground">Developers using it</span>
                </div>
                <div className="hidden h-10 w-px bg-border sm:block" />
                <div className="flex flex-col items-center gap-1">
                  <span className="text-3xl font-bold">{testCount}+</span>
                  <span className="text-sm text-muted-foreground">Automated tests included</span>
                </div>
                <div className="hidden h-10 w-px bg-border sm:block" />
                <div className="flex flex-col items-center gap-1">
                  <span className="text-3xl font-bold">2–3 days</span>
                  <span className="text-sm text-muted-foreground">To your first production deploy</span>
                </div>
              </div>

              {testimonials.length > 0 && (
                <div className="grid gap-6 md:grid-cols-3">
                  {testimonials.map((testimonial) => (
                    <figure
                      key={testimonial.name}
                      className="rounded-2xl border border-border/70 bg-card p-6 shadow-sm"
                    >
                      <blockquote className="mb-4 text-sm italic text-muted-foreground">
                        &ldquo;{testimonial.quote}&rdquo;
                      </blockquote>
                      <figcaption>
                        <p className="text-sm font-semibold">{testimonial.name}</p>
                        <p className="text-xs text-muted-foreground">{testimonial.role}</p>
                      </figcaption>
                    </figure>
                  ))}
                </div>
              )}
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

          {/* Resources / Compare Section */}
          <section className="container border-t py-24">
            <div className="mx-auto max-w-4xl">
              <h2 className="mb-4 text-center text-2xl font-bold">
                Compare &amp; Learn
              </h2>
              <p className="mb-10 text-center text-muted-foreground">
                Not sure if this is the right kit? See how it compares to every alternative.
              </p>
              <div className="grid gap-4 sm:grid-cols-3">
                <Link
                  href="/compare"
                  className="group rounded-2xl border border-border/70 bg-card p-6 shadow-sm transition-colors hover:border-primary/30"
                >
                  <h3 className="mb-2 font-semibold">
                    Starter Kit Comparison Hub
                  </h3>
                  <p className="text-sm text-muted-foreground">
                    Side-by-side cards for all 8 Laravel SaaS boilerplates — Larafast, SaaSyKit,
                    Wave, Spark, Jetstream, ShipFast, SupaStarter.
                  </p>
                  <span className="mt-3 inline-flex items-center text-sm font-medium text-primary">
                    Compare all
                    <ArrowRight className="ml-1 h-3 w-3" />
                  </span>
                </Link>
                <Link
                  href="/guides/saas-starter-kit-comparison-2026"
                  className="group rounded-2xl border border-border/70 bg-card p-6 shadow-sm transition-colors hover:border-primary/30"
                >
                  <h3 className="mb-2 font-semibold">
                    2026 Buyer&apos;s Guide
                  </h3>
                  <p className="text-sm text-muted-foreground">
                    In-depth review of each kit: feature matrix, pricing table, pros/cons, and
                    our ranked recommendation for React and Livewire teams.
                  </p>
                  <span className="mt-3 inline-flex items-center text-sm font-medium text-primary">
                    Read the guide
                    <ArrowRight className="ml-1 h-3 w-3" />
                  </span>
                </Link>
                <Link
                  href="/pricing"
                  className="group rounded-2xl border border-border/70 bg-card p-6 shadow-sm transition-colors hover:border-primary/30"
                >
                  <h3 className="mb-2 font-semibold">
                    Pricing
                  </h3>
                  <p className="text-sm text-muted-foreground">
                    One-time purchase. Full source access. No recurring license fees.
                    Everything in the comparison matrix, ready to deploy.
                  </p>
                  <span className="mt-3 inline-flex items-center text-sm font-medium text-primary">
                    View pricing
                    <ArrowRight className="ml-1 h-3 w-3" />
                  </span>
                </Link>
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
          {/* Closing CTA Section */}
          {canRegister && (
            <section className="container border-t py-24">
              <div className="mx-auto max-w-2xl text-center">
                <h2 className="text-3xl font-bold">Ready to skip the boilerplate?</h2>
                <p className="mt-4 text-lg text-muted-foreground">
                  Everything you need to launch is already wired up.
                </p>
                <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
                  <Button
                    size="lg"
                    asChild
                    onClick={() =>
                      track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, {
                        source: 'closing_cta',
                      })
                    }
                  >
                    <Link href={route('register')}>
                      Start Building Free
                      <ArrowRight className="ml-2 h-4 w-4" />
                    </Link>
                  </Button>
                  <Button variant="outline" size="lg" asChild>
                    <Link href="/pricing">View pricing</Link>
                  </Button>
                </div>
              </div>
            </section>
          )}

          {/* FAQ Section */}
          {faqs.length > 0 && (
            <section className="container border-t py-24">
              <div className="mx-auto max-w-3xl">
                <h2 className="mb-8 text-center text-3xl font-bold">
                  Frequently asked questions
                </h2>
                <div className="space-y-4">
                  {faqs.map((faq) => (
                    <div
                      key={faq.question}
                      className="rounded-2xl border border-border/70 bg-card p-6"
                    >
                      <h3 className="mb-2 font-semibold">{faq.question}</h3>
                      <p className="text-sm text-muted-foreground">{faq.answer}</p>
                    </div>
                  ))}
                </div>
              </div>
            </section>
          )}
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

      {/* Sticky mobile CTA — visible only on small screens when hero is out of view */}
      {canRegister && showStickyMobileCTA && (
        <div className="fixed bottom-0 left-0 right-0 z-50 border-t bg-background px-4 pb-safe pt-3 pb-4 md:hidden">
          <Button
            className="w-full"
            size="lg"
            asChild
            onClick={() =>
              track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, {
                source: 'sticky_mobile_cta',
                label: 'Get Started Free',
              })
            }
          >
            <Link href={route('register')}>
              Get Started Free
              <ArrowRight className="ml-2 h-4 w-4" />
            </Link>
          </Button>
        </div>
      )}
    </>
  );
};

Welcome.disableGlobalUi = true;

export default Welcome;
