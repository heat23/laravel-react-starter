import { ArrowRight } from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import { ComparisonTable } from '@/Components/compare/ComparisonTable';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { Button } from '@/Components/ui/button';
import type { ComparisonPageProps } from '@/types/index';

export default function SaaSykit({
  title,
  metaDescription,
  features,
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
                Laravel React Starter vs SaaSykit
              </h1>
              <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                Both are Laravel SaaS boilerplates. The architectural difference
                is in the admin panel and type safety across the full stack.
              </p>
            </header>

            {/* Intro */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <p>
                SaaSykit is a solid Laravel SaaS boilerplate with a growing feature set. It
                includes billing, authentication, and an admin panel built on Filament &mdash;
                a popular Livewire-based admin framework. For teams already using Filament,
                SaaSykit provides a familiar and extensible starting point.
              </p>
              <p>
                The key architectural difference is the admin panel. SaaSykit uses Filament,
                which is Livewire-based and renders server-side. This starter&apos;s admin panel
                is built entirely in React and TypeScript &mdash; the same stack as the rest
                of the application. That means no context switch between the customer-facing
                frontend and the admin panel, and TypeScript type safety across the full stack.
              </p>
              <p>
                This matters more than it sounds. When your admin panel and your product
                frontend share components, hooks, and type definitions, changes propagate
                consistently. A renamed API field breaks at compile time in both places, not
                at runtime in the admin panel three weeks after the customer-facing fix shipped.
              </p>
            </section>

            {/* Comparison Table */}
            <section className="my-16">
              <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <ComparisonTable
                  features={features}
                  usName="Laravel React Starter"
                  themName="SaaSykit"
                />
              </div>
            </section>

            {/* When to choose SaaSykit */}
            <section className="prose prose-neutral dark:prose-invert max-w-none">
              <h2>When to choose SaaSykit</h2>
              <p>
                If your team is comfortable with Filament and prefers Livewire for admin
                tooling, SaaSykit&apos;s admin panel will feel familiar and extend easily with
                Filament plugins. The Filament ecosystem has hundreds of plugins for common
                admin patterns &mdash; charts, form builders, table filters, and more.
              </p>
              <p>
                SaaSykit also has a larger community and more third-party integrations in the
                Filament ecosystem. If you expect to heavily customize the admin panel with
                Filament-specific features like custom form fields, relation managers, or
                widgets, SaaSykit gives you that foundation.
              </p>
              <p>
                For teams where the admin panel is a significant part of the product &mdash;
                not just user management, but custom workflows and data entry &mdash;
                Filament&apos;s rapid prototyping capabilities can be an advantage over building
                every admin view as a React component.
              </p>
            </section>

            {/* When to choose this starter */}
            <section className="prose prose-neutral dark:prose-invert max-w-none mt-8">
              <h2>When to choose Laravel React Starter</h2>
              <p>
                If you want a single stack end-to-end &mdash; React and TypeScript for both
                the marketing site and the admin panel &mdash; this starter eliminates the
                Filament and Livewire context switch. Your team uses one component model, one
                state management approach, and one set of tooling across the entire application.
              </p>
              <p>
                TypeScript in the admin panel means your IDE catches prop mismatches, API
                contract violations, and null reference errors before they hit production.
                When you rename a field in your Laravel controller, TypeScript compilation
                fails in every admin component that references the old name. With Filament,
                those errors surface at runtime.
              </p>
              <p>
                The feature flag system with database overrides and per-user targeting is
                more granular than what SaaSykit offers out of the box. If you need to
                gradually roll out features to specific users or override flags without
                redeploying, this starter has that infrastructure built in and tested.
              </p>
            </section>

            {/* CTA */}
            <section className="mt-16 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
              <h2 className="text-2xl font-bold">Ship your SaaS faster</h2>
              <p className="mt-2 text-muted-foreground">
                One stack, full TypeScript, 90+ tests &mdash; from auth to admin panel.
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
