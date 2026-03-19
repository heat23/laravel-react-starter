import { ArrowRight } from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import { ComparisonTable } from '@/Components/compare/ComparisonTable';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { Button } from '@/Components/ui/button';
import type { ComparisonPageProps } from '@/types/index';

export default function Supastarter({
  title,
  metaDescription,
  features,
  breadcrumbs,
}: ComparisonPageProps) {
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
                Laravel React Starter vs Supastarter
              </h1>
              <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                Supastarter uses Supabase as a backend-as-a-service. This starter uses
                Laravel + MySQL + Redis &mdash; a backend where you own every layer.
              </p>
            </header>

            {/* Intro */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <p>
                Supastarter is a solid choice for developers who want Supabase&apos;s managed auth,
                realtime subscriptions, and storage out of the box. This comparison isn&apos;t about
                which is &ldquo;better&rdquo; &mdash; it&apos;s about which fits your team&apos;s
                database philosophy. Supastarter bets on Supabase (PostgreSQL managed service + BaaS
                SDKs). This starter bets on Laravel + MySQL + Redis &mdash; a backend where you own
                every layer.
              </p>
              <p>
                Both use React + TypeScript on the frontend, so the UI development experience is
                similar. The architectural difference is entirely on the server side: Supabase
                abstracts away the database layer behind its SDK, while Laravel gives you full
                control over your Eloquent models, migrations, and query builder. The tradeoff
                is development speed vs. operational control.
              </p>
              <p>
                For SaaS products where billing safety, audit trails, and feature flag granularity
                matter, the self-hosted model provides more control. For products where realtime
                collaboration and managed infrastructure are priorities, Supabase&apos;s native
                capabilities are hard to match with a self-hosted stack.
              </p>
            </section>

            {/* Comparison Table */}
            <section className="my-16">
              <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <ComparisonTable
                  features={features}
                  usName="Laravel React Starter"
                  themName="Supastarter"
                />
              </div>
            </section>

            {/* When to choose Supastarter */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <h2>When to choose Supastarter</h2>
              <p>
                Supastarter is the better choice if you want Supabase&apos;s managed auth to handle
                OAuth, magic links, and session management without writing the code yourself. If
                your SaaS needs realtime features &mdash; collaborative editing, live dashboards,
                presence indicators &mdash; Supabase&apos;s subscription model handles those natively
                with minimal server-side code.
              </p>
              <p>
                The Supabase SDK ecosystem has strong integrations with common Next.js patterns,
                and the managed infrastructure means you don&apos;t need to provision or maintain
                database servers, Redis instances, or queue workers. If your team prefers a BaaS
                model where database migrations are handled through the Supabase dashboard and
                you want to deploy to Vercel with minimal DevOps, Supastarter is a good fit.
              </p>
            </section>

            {/* When to choose this starter */}
            <section className="prose prose-neutral dark:prose-invert max-w-none mt-8">
              <h2>When to choose Laravel React Starter</h2>
              <p>
                If you prefer owning your infrastructure, Laravel&apos;s self-hosted stack avoids
                Supabase vendor lock-in &mdash; your database is MySQL or PostgreSQL that you
                control, your auth is Sanctum (open-source), and your queue is Redis (self-hosted).
                Migration between hosting providers is straightforward because nothing depends on
                a proprietary SDK.
              </p>
              <p>
                The Redis-locked billing layer is a concrete advantage for billing safety that
                Supabase&apos;s architecture doesn&apos;t address &mdash; concurrent subscription
                mutations are serialized with distributed locks. PHPStan on the backend and
                TypeScript strict on the frontend gives you static analysis coverage across both
                layers, a level of compile-time safety that a BaaS + SDK model doesn&apos;t provide
                for server-side logic. For teams that want full control over their data layer and
                production-grade billing infrastructure, this starter delivers both.
              </p>
            </section>

            {/* CTA */}
            <section className="mt-16 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
              <h2 className="text-2xl font-bold">Ship your SaaS faster</h2>
              <p className="mt-2 text-muted-foreground">
                Own your backend. React + TypeScript + Laravel &mdash; no vendor lock-in.
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
