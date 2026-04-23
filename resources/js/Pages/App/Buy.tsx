import { ArrowRight, CheckCircle2, Clock, Lock, RefreshCcw, ShieldCheck } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';

import { PublicFooter } from '@/Components/marketing/PublicFooter';
import { PublicNav } from '@/Components/marketing/PublicNav';
import { Button } from '@/Components/ui/button';

interface BuyProps {
  canLogin?: boolean;
  canRegister?: boolean;
  templatePrice: string;
  canonicalUrl?: string;
}

const includedFeatures = [
  'Auth, 2FA, social login (Google + GitHub)',
  'Stripe billing with double-charge prevention',
  'Custom React + TypeScript admin panel',
  '11 toggleable feature flags',
  'HMAC-signed webhooks (incoming + outgoing)',
  'Audit logging with IP and user agent',
  '90+ Pest + Vitest tests',
  'PHPStan level 8 static analysis',
  'CI/CD GitHub Actions workflow',
  'VPS deployment configs (nginx + supervisor)',
  'Full source code — no lock-in',
  'Lifetime access to updates via GitHub releases',
];

const buildYourselfRows = [
  { item: 'Auth + social login + 2FA', hours: '40–80h', cost: '$3,000–$6,000' },
  { item: 'Stripe billing + webhooks + dunning', hours: '60–120h', cost: '$4,500–$9,000' },
  { item: 'Admin panel + audit logs', hours: '40–80h', cost: '$3,000–$6,000' },
  { item: 'Feature flags + CI/CD + PHPStan', hours: '20–40h', cost: '$1,500–$3,000' },
  { item: 'Testing (90+ tests)', hours: '40–80h', cost: '$3,000–$6,000' },
];

type BuyComponent = ((props: BuyProps) => JSX.Element) & {
  disableGlobalUi?: boolean;
};

const Buy: BuyComponent = ({
  canLogin = true,
  canRegister = true,
  templatePrice,
  canonicalUrl,
}) => {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'buy' });
  }, [track]); // mount-only in practice: track is stable (see useAnalytics)

  return (
    <>
      <Head title="Buy — Laravel React Starter">
        <meta
          name="description"
          content={`Get the Laravel React Starter for ${templatePrice} — a one-time purchase. Auth, billing, admin panel, feature flags, and 90+ tests ready to ship.`}
        />
        {canonicalUrl && <link rel="canonical" href={canonicalUrl} />}
      </Head>

      <div className="min-h-screen bg-background">
        <PublicNav canLogin={canLogin} canRegister={canRegister} currentPath="/buy" />

        <main className="container py-16">
          <div className="mx-auto max-w-4xl">
            {/* Hero */}
            <header className="text-center">
              <div className="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
                One-time purchase · Full source code · No subscription
              </div>
              <h1 className="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">
                Laravel React Starter
              </h1>
              <p className="mt-4 text-lg text-muted-foreground">
                Auth, billing, admin panel, and 90+ tests — ready to ship your SaaS today.
              </p>

              {/* Price anchor */}
              <div className="mt-8 inline-block rounded-2xl border border-border bg-card px-10 py-8 shadow-md">
                <p className="text-5xl font-bold text-foreground">{templatePrice}</p>
                <p className="mt-2 text-sm font-medium text-muted-foreground">
                  One-time · No recurring fees · Deploy to unlimited projects
                </p>
                <Button size="lg" className="mt-6 w-full" asChild>
                  {/* Replace this href with your actual payment link (Gumroad, Lemon Squeezy, etc.) */}
                  <a href="#purchase" onClick={(e) => { e.preventDefault(); track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, { source: 'buy_page', label: 'get_instant_access', page: 'buy' }); }}>
                    Get Instant Access
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </a>
                </Button>
                <p className="mt-3 text-xs text-muted-foreground">
                  Delivered as a GitHub repo invite · Immediate access after purchase
                </p>
              </div>
            </header>

            {/* Trust strip */}
            <div className="mt-8 flex flex-wrap items-center justify-center gap-6 text-sm text-muted-foreground">
              <span className="flex items-center gap-1.5">
                <ShieldCheck className="h-4 w-4 text-success" />
                Secure checkout
              </span>
              <span className="flex items-center gap-1.5">
                <RefreshCcw className="h-4 w-4 text-success" />
                14-day money-back guarantee
              </span>
              <span className="flex items-center gap-1.5">
                <Lock className="h-4 w-4 text-success" />
                Full source code ownership
              </span>
            </div>

            {/* What's included */}
            <section className="mt-16">
              <h2 className="text-2xl font-bold text-center">What's included</h2>
              <div className="mt-8 grid gap-3 sm:grid-cols-2">
                {includedFeatures.map((feature) => (
                  <div key={feature} className="flex items-start gap-3">
                    <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-success" />
                    <span className="text-sm text-muted-foreground">{feature}</span>
                  </div>
                ))}
              </div>
            </section>

            {/* Build-yourself cost comparison */}
            <section className="mt-16">
              <h2 className="text-2xl font-bold text-center">What it costs to build from scratch</h2>
              <p className="mt-2 text-center text-sm text-muted-foreground">
                At $75/hr contractor rate — or weeks of your own time
              </p>
              <div className="mt-8 overflow-x-auto rounded-2xl border border-border shadow-sm">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="border-b border-border bg-muted/40">
                      <th scope="col" className="py-3 pl-5 pr-6 text-left font-semibold">Feature area</th>
                      <th scope="col" className="px-4 py-3 text-center font-semibold">Estimated hours</th>
                      <th scope="col" className="px-4 py-3 text-center font-semibold">Cost at $75/hr</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-border bg-card">
                    {buildYourselfRows.map((row) => (
                      <tr key={row.item}>
                        <td className="py-3 pl-5 pr-6 text-muted-foreground">{row.item}</td>
                        <td className="px-4 py-3 text-center text-muted-foreground">{row.hours}</td>
                        <td className="px-4 py-3 text-center text-muted-foreground">{row.cost}</td>
                      </tr>
                    ))}
                  </tbody>
                  <tfoot>
                    <tr className="border-t border-border bg-muted/40 font-semibold">
                      <td className="py-3 pl-5 pr-6">Total build cost</td>
                      <td className="px-4 py-3 text-center">200–400h</td>
                      <td className="px-4 py-3 text-center text-destructive">$15,000–$30,000</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <div className="mt-4 flex items-center gap-2 justify-center">
                <Clock className="h-4 w-4 text-muted-foreground" />
                <p className="text-sm text-muted-foreground">
                  Or 5–10 weeks of your own full-time development work.
                </p>
              </div>
            </section>

            {/* Not for you section */}
            <section className="mt-16 rounded-2xl border border-border/70 bg-muted/30 p-8">
              <h2 className="text-xl font-bold">Is this right for you?</h2>
              <div className="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                  <p className="mb-3 text-sm font-semibold text-success">Good fit if you:</p>
                  <ul className="space-y-2 text-sm text-muted-foreground">
                    <li className="flex items-start gap-2">
                      <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-success" />
                      Know Laravel and want to skip infrastructure boilerplate
                    </li>
                    <li className="flex items-start gap-2">
                      <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-success" />
                      Building a SaaS and need billing + auth + admin on day one
                    </li>
                    <li className="flex items-start gap-2">
                      <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-success" />
                      Want a tested, documented foundation for a commercial product
                    </li>
                  </ul>
                </div>
                <div>
                  <p className="mb-3 text-sm font-semibold text-destructive">Not for you if you:</p>
                  <ul className="space-y-2 text-sm text-muted-foreground">
                    <li className="flex items-start gap-2">
                      <span className="mt-0.5 h-4 w-4 shrink-0 text-center text-destructive font-bold">✕</span>
                      Prefer Livewire or Vue over React
                    </li>
                    <li className="flex items-start gap-2">
                      <span className="mt-0.5 h-4 w-4 shrink-0 text-center text-destructive font-bold">✕</span>
                      Deploying to serverless (Vercel/Lambda) — requires a VPS or cloud VM
                    </li>
                    <li className="flex items-start gap-2">
                      <span className="mt-0.5 h-4 w-4 shrink-0 text-center text-destructive font-bold">✕</span>
                      Need a no-code builder — this is source code for developers
                    </li>
                  </ul>
                </div>
              </div>
            </section>

            {/* Final CTA */}
            <section className="mt-16 text-center">
              <h2 className="text-2xl font-bold">Ready to ship your SaaS?</h2>
              <p className="mt-2 text-muted-foreground">
                {templatePrice} one-time. Everything wired up. Yours forever.
              </p>
              <div className="mt-6 flex flex-wrap items-center justify-center gap-4">
                <Button size="lg" asChild>
                  {/* Replace this href with your actual payment link */}
                  <a href="#purchase" onClick={(e) => { e.preventDefault(); track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, { source: 'buy_page', label: 'get_instant_access_bottom', page: 'buy' }); }}>
                    Get Instant Access — {templatePrice}
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </a>
                </Button>
                <Button variant="outline" size="lg" asChild>
                  <Link href="/compare">Compare alternatives</Link>
                </Button>
              </div>
              <p className="mt-4 text-xs text-muted-foreground">
                14-day money-back guarantee · No recurring fees · Full source code
              </p>
            </section>
          </div>
        </main>

        <PublicFooter />
      </div>
    </>
  );
};

Buy.disableGlobalUi = true;

export default Buy;
