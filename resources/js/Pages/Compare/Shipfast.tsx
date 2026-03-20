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

export default function Shipfast({
  title,
  metaDescription,
  features,
  breadcrumbs,
}: ComparisonPageProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'compare-shipfast' });
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
                Laravel React Starter vs Shipfast
              </h1>
              <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                Shipfast is Next.js. This starter is Laravel. Both ship React + TypeScript
                with Stripe billing &mdash; the backend is the real decision.
              </p>
            </header>

            {/* Intro */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <p>
                Shipfast is the go-to Next.js SaaS starter for indie hackers &mdash; strong SEO,
                fast iteration, and a TypeScript-first approach. This starter is the Laravel
                equivalent: same React + TypeScript frontend philosophy, same commercial focus,
                but with Laravel 12 on the backend instead of Node.js.
              </p>
              <p>
                The backend choice is the real decision here &mdash; PHP/Laravel vs. Node.js/Next.js.
                Both starters share React on the frontend, both include Stripe billing, and both
                target solo founders who want to ship quickly. The difference is in the server-side
                ecosystem: Laravel&apos;s queues, jobs, and Eloquent ORM vs. Next.js API routes,
                serverless functions, and Prisma.
              </p>
              <p>
                This comparison helps developers who are fluent in both ecosystems &mdash; or
                choosing between them &mdash; make the right call for their specific SaaS. If
                you&apos;ve already committed to one backend, the choice is made. If you&apos;re
                evaluating both, the differences below matter.
              </p>
            </section>

            {/* Comparison Table */}
            <section className="my-16">
              <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <ComparisonTable
                  features={features}
                  usName="Laravel React Starter"
                  themName="Shipfast"
                />
              </div>
            </section>

            {/* When to choose Shipfast */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <h2>When to choose Shipfast</h2>
              <p>
                Shipfast is the better choice if you&apos;re a Node.js/Next.js developer who
                doesn&apos;t want to context-switch to PHP. If your existing infrastructure runs
                on Vercel or a serverless platform, Shipfast&apos;s Node.js stack deploys in one
                click with zero server configuration. The Next.js ecosystem has stronger
                integrations with edge platforms and CDN-native middleware.
              </p>
              <p>
                Shipfast also includes a built-in blog with MDX content, which is useful if you
                want content marketing from day one without adding a separate CMS. The indie
                hacker community around Shipfast is active, with templates, tutorials, and
                integrations contributed by other founders. If you value that ecosystem and prefer
                JavaScript end-to-end, Shipfast is a strong choice.
              </p>
            </section>

            {/* When to choose this starter */}
            <section className="prose prose-neutral dark:prose-invert max-w-none mt-8">
              <h2>When to choose Laravel React Starter</h2>
              <p>
                If you&apos;re a Laravel developer, the PHP ecosystem &mdash; Cashier, Telescope,
                Horizon, Octane, Pest &mdash; is mature, predictable, and battle-tested for SaaS.
                Laravel&apos;s queue system, job batching, and Redis integration are production-proven
                at scale. Redis-locked billing mutations, PHPStan static analysis, and Pest&apos;s
                expressive test syntax are real advantages for a solo founder who needs confidence
                in their billing layer.
              </p>
              <p>
                The Laravel job/queue model makes background processing &mdash; dunning emails,
                webhook dispatch, audit log persistence &mdash; cleaner than Next.js API routes.
                The built-in admin panel, 11 feature flags with database overrides, and HMAC-signed
                webhooks are production infrastructure that Shipfast doesn&apos;t include. If your
                SaaS needs a custom admin panel and granular feature rollouts, this starter provides
                both out of the box.
              </p>
            </section>

            {/* CTA */}
            <section className="mt-16 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
              <h2 className="text-2xl font-bold">Ship your SaaS faster</h2>
              <p className="mt-2 text-muted-foreground">
                Laravel + React + TypeScript &mdash; the PHP equivalent of Shipfast.
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
