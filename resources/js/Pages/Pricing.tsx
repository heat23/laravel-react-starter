import { CheckCircle2, Lock, RefreshCcw, ShieldCheck, Sparkles } from 'lucide-react';

import { useEffect, useMemo, useState } from 'react';

import { Head, Link, router, usePage } from '@inertiajs/react';

import { AnnouncementBanner, type AnnouncementBannerProps } from '@/Components/layout/AnnouncementBanner';
import PageHeader from '@/Components/layout/PageHeader';
import { FaqAccordion } from '@/Components/marketing/FaqAccordion';
import { PublicFooter } from '@/Components/marketing/PublicFooter';
import { PublicNav } from '@/Components/marketing/PublicNav';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { LoadingButton } from '@/Components/ui/loading-button';
import { ToggleGroup, ToggleGroupItem } from '@/Components/ui/toggle-group';
import { useAnalytics } from '@/hooks/useAnalytics';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { AnalyticsEvents, type BillingPeriod, type PlanKey } from '@/lib/events';
import type { PageProps } from '@/types';

interface TierConfig {
  name: string;
  description: string;
  price: number | null;
  price_annual?: number | null;
  stripe_price_id?: string | null;
  stripe_price_id_annual?: string | null;
  per_seat?: boolean;
  min_seats?: number | null;
  coming_soon?: boolean;
  popular?: boolean;
  limits?: Record<string, number | null>;
  features?: string[];
}

interface FaqItem {
  question: string;
  answer: string;
}

interface PricingPageProps extends PageProps {
  tiers: Record<string, TierConfig>;
  currentPlan?: string | null;
  trial?: {
    active: boolean;
    daysRemaining: number;
    endsAt: string;
  } | null;
  trialEnabled?: boolean;
  trialDays?: number;
  faqs?: FaqItem[];
  contactEmail?: string;
  announcementBanner?: AnnouncementBannerProps | null;
}

const DEFAULT_FAQS: FaqItem[] = [
  {
    question: 'Can I switch plans anytime?',
    answer:
      'Yes. Upgrade or downgrade at any time — changes take effect immediately. Downgrades are prorated, so you only pay for what you use.',
  },
  {
    question: 'Is there a free trial?',
    answer:
      'The Free tier is free forever with no credit card required. Paid tiers offer a 14-day trial so you can test all features before committing.',
  },
  {
    question: 'What happens when I exceed my plan limits?',
    answer:
      "You'll get a notification and can upgrade instantly. We never shut down your app or block your users — you just can't create new resources beyond the limit until you upgrade.",
  },
  {
    question: 'How does team seat billing work?',
    answer:
      'Team plans are billed per seat per month. The Team tier requires a minimum of 2 seats ($98/mo). Add or remove seats anytime — billing adjusts automatically.',
  },
  {
    question: 'What payment methods do you accept?',
    answer:
      'We accept all major credit cards via Stripe. For Enterprise plans, we can arrange invoicing.',
  },
  {
    question: 'Can I get a refund?',
    answer:
      "Yes. If you're not satisfied within the first 14 days, contact us for a full refund. No questions asked.",
  },
  {
    question: 'Do you offer annual pricing?',
    answer:
      'Yes — switch to Annual in the billing toggle and save 20% (2 months free). You can switch between monthly and annual at any time from your billing settings.',
  },
];

export default function Pricing() {
  const { tiers, currentPlan, trial, trialEnabled, trialDays, auth, faqs, contactEmail, announcementBanner } =
    usePage<PricingPageProps>().props;
  const { track } = useAnalytics();
  const tierEntries = useMemo(() => Object.entries(tiers), [tiers]);

  useEffect(() => {
    track(AnalyticsEvents.BILLING_PRICING_VIEWED, {
      user_type: auth.user ? 'authenticated' : 'anonymous',
    });
  }, [track]); // eslint-disable-line react-hooks/exhaustive-deps
  const hasAnnualPricing = useMemo(() => {
    return tierEntries.some(
      ([, tier]) => tier.price_annual && tier.price_annual > 0
    );
  }, [tierEntries]);

  const [billingPeriod, setBillingPeriod] = useState<'monthly' | 'annual'>(
    () =>
      Object.values(tiers).some((t) => t.price_annual && t.price_annual > 0)
        ? 'annual'
        : 'monthly'
  );
  const [checkoutLoading, setCheckoutLoading] = useState<string | null>(null);

  const annualSavingsPercent = useMemo(() => {
    const proTier = tiers.pro;
    if (
      proTier?.price == null ||
      proTier?.price_annual == null ||
      proTier.price <= 0
    )
      return 0;
    const monthlyTotal = proTier.price * 12;
    const savings = monthlyTotal - proTier.price_annual;
    return Math.round((savings / monthlyTotal) * 100);
  }, [tiers.pro]);

  const getPrice = (tier: TierConfig) => {
    if (tier.price === null || tier.price === undefined)
      return { label: 'Custom pricing', sublabel: null, savings: null };

    if (billingPeriod === 'annual' && tier.price_annual) {
      const monthlyEquivalent = (tier.price_annual / 12).toFixed(2);
      const yearlySavings = tier.price * 12 - tier.price_annual;
      return {
        label: `$${monthlyEquivalent}/mo`,
        sublabel: `billed annually ($${tier.price_annual}/yr)`,
        savings: yearlySavings > 0 ? yearlySavings : null,
      };
    }

    return {
      label: tier.price === 0 ? 'Free' : `$${tier.price}/mo`,
      sublabel: null,
      savings: null,
    };
  };

  const isSubscribed = !!currentPlan && currentPlan !== 'free';

  const handleCheckout = (planKey: string) => {
    const tier = tiers[planKey];
    const priceId =
      billingPeriod === 'annual' && tier.stripe_price_id_annual
        ? tier.stripe_price_id_annual
        : tier.stripe_price_id;

    track(AnalyticsEvents.BILLING_CHECKOUT_STARTED, {
      plan: planKey as PlanKey,
      price_id: priceId ?? undefined,
      billing_period: billingPeriod as BillingPeriod,
    });

    setCheckoutLoading(planKey);

    if (isSubscribed) {
      router.post(
        route('billing.swap'),
        { price_id: priceId },
        { onFinish: () => setCheckoutLoading(null) }
      );
    } else {
      // New subscribers go through Stripe Checkout hosted page for card collection.
      // Server creates a Checkout session and redirects via Inertia::location().
      router.post(
        route('billing.checkout'),
        {
          price_id: priceId,
          quantity: tier.per_seat && tier.min_seats ? tier.min_seats : 1,
        },
        { onFinish: () => setCheckoutLoading(null) }
      );
    }
  };

  const salesEmail = contactEmail ?? 'hello@example.com';

  const content = (
    <>
      <Head title="Pricing">
        <meta
          name="description"
          content="Start free, upgrade when you're ready. Simple per-seat pricing for Laravel React Starter — no credit card required to begin."
        />
      </Head>
      <PageHeader
        title="Pricing"
        subtitle="Start free. Upgrade when you have paying customers."
      />
      <div className="container py-12">
        <div className="max-w-5xl mx-auto space-y-10">
          {/* Social proof */}
          <p className="text-center text-sm text-muted-foreground">
            Join <strong className="text-foreground">hundreds of developers</strong> already building with Laravel React Starter.
          </p>

          {hasAnnualPricing && (
            <div className="flex justify-center">
              <div className="inline-flex items-center gap-4 p-1 bg-muted rounded-lg">
                <ToggleGroup
                  type="single"
                  value={billingPeriod}
                  onValueChange={(value) => {
                    if (!value) return;
                    const next = value as 'monthly' | 'annual';
                    track(AnalyticsEvents.BILLING_PERIOD_TOGGLED, {
                      from: billingPeriod as BillingPeriod,
                      to: next as BillingPeriod,
                    });
                    setBillingPeriod(next);
                  }}
                >
                  <ToggleGroupItem value="monthly" className="px-4">
                    Monthly
                  </ToggleGroupItem>
                  <ToggleGroupItem value="annual" className="px-4">
                    Annual
                    {annualSavingsPercent > 0 && (
                      <Badge variant="success" className="ml-2">
                        Save {annualSavingsPercent}%
                      </Badge>
                    )}
                  </ToggleGroupItem>
                </ToggleGroup>
              </div>
            </div>
          )}

          {!hasAnnualPricing && (
            <p className="text-center text-sm text-muted-foreground">
              Switch to annual billing and save 20%.{' '}
              <a href={`mailto:${salesEmail}`} className="underline hover:text-foreground transition-colors">
                Email us to switch.
              </a>
            </p>
          )}

          {trial?.active && (
            <Alert className="border-primary/30 bg-primary/10">
              <Sparkles className="h-4 w-4 text-primary" />
              <AlertDescription className="text-center">
                <strong className="text-primary">Pro Trial Active</strong> - You
                have <strong>{trial.daysRemaining}</strong> day
                {trial.daysRemaining !== 1 ? 's' : ''} remaining. Upgrade now to
                keep your Pro features!
              </AlertDescription>
            </Alert>
          )}

          {!auth.user && trialEnabled && (
            <Alert className="border-success/30 bg-success/10">
              <Sparkles className="h-4 w-4 text-success" />
              <AlertDescription className="text-center">
                <strong className="text-success">
                  Start with a {trialDays}-day free Pro trial!
                </strong>{' '}
                Sign up today and experience all Pro features free.
              </AlertDescription>
            </Alert>
          )}

          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            {tierEntries.map(([key, tier]) => {
              const isCurrent = currentPlan === key;
              const isEnterprise = tier.price === null || tier.price === undefined;
              const pricing = getPrice(tier);

              return (
                <Card
                  key={key}
                  className={
                    isCurrent
                      ? 'border-primary shadow-md'
                      : tier.popular
                        ? 'ring-2 ring-primary shadow-lg md:scale-[1.02] md:z-10'
                        : ''
                  }
                  onMouseEnter={() => {
                    if (!isCurrent && !isEnterprise && key !== 'free') {
                      track(AnalyticsEvents.BILLING_PLAN_SELECTED, {
                        plan: key as PlanKey,
                        billing_period: billingPeriod as BillingPeriod,
                      });
                    }
                  }}
                >
                  <CardHeader>
                    {tier.popular && !isCurrent && !tier.coming_soon && (
                      <div className="mb-2">
                        <Badge variant="default" className="bg-primary text-primary-foreground">
                          Most Popular
                        </Badge>
                      </div>
                    )}
                    <div className="flex items-center justify-between">
                      <CardTitle className="text-lg">{tier.name}</CardTitle>
                      <div className="flex items-center gap-2">
                        {tier.coming_soon && (
                          <Badge variant="outline">Coming Soon</Badge>
                        )}
                        {pricing.savings &&
                          billingPeriod === 'annual' &&
                          !tier.coming_soon && (
                            <Badge variant="success">
                              Save ${pricing.savings}
                            </Badge>
                          )}
                        {isCurrent && (
                          <Badge variant="secondary">Current</Badge>
                        )}
                      </div>
                    </div>
                    <div className="space-y-1">
                      <CardDescription className="text-2xl font-semibold text-foreground">
                        {pricing.label}
                      </CardDescription>
                      {pricing.sublabel && (
                        <CardDescription className="text-sm text-muted-foreground">
                          {pricing.sublabel}
                        </CardDescription>
                      )}
                      {tier.per_seat && tier.price != null && tier.price > 0 && tier.min_seats && (
                        <CardDescription className="text-xs text-muted-foreground">
                          {billingPeriod === 'annual' && tier.price_annual
                            ? `$${(tier.price_annual / 12).toFixed(2)}/seat/mo — min ${tier.min_seats} seats`
                            : `$${tier.price}/seat/mo — starts at $${tier.price * tier.min_seats}/mo for ${tier.min_seats} seats`}
                        </CardDescription>
                      )}
                      {!tier.per_seat && key === 'pro' && (
                        <CardDescription className="text-xs text-muted-foreground">
                          For one developer. Upgrade to Team to add collaborators.
                        </CardDescription>
                      )}
                    </div>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    {tier.description && (
                      <p className="text-sm text-muted-foreground">
                        {tier.description}
                      </p>
                    )}

                    <ul className="space-y-2 text-sm">
                      {(tier.features ?? []).map((feature) => (
                        <li
                          key={feature}
                          className="flex items-center gap-2 text-muted-foreground"
                        >
                          <CheckCircle2 className="h-4 w-4 text-success shrink-0" />
                          {feature}
                        </li>
                      ))}
                    </ul>

                    <div className="pt-2">
                      {isEnterprise ? (
                        <Button asChild className="w-full" variant="outline">
                          <a href={`mailto:${salesEmail}`}>Contact Sales</a>
                        </Button>
                      ) : (
                        <>
                          {!auth.user && (
                            <Button asChild className="w-full">
                              <Link href="/register">
                                {key === 'pro' && trialEnabled
                                  ? `Start ${trialDays}-Day Free Trial`
                                  : 'Get Started'}
                              </Link>
                            </Button>
                          )}

                          {auth.user &&
                            !isCurrent &&
                            key !== 'free' && (
                              <>
                                {tier.coming_soon ? (
                                  <Button
                                    className="w-full"
                                    variant="secondary"
                                    disabled
                                  >
                                    Coming Soon
                                  </Button>
                                ) : (
                                  <LoadingButton
                                    className="w-full"
                                    onClick={() => handleCheckout(key)}
                                    loading={checkoutLoading === key}
                                    loadingText="Processing..."
                                  >
                                    {isSubscribed ? 'Switch to' : 'Upgrade to'}{' '}
                                    {tier.name}
                                    {billingPeriod === 'annual' && ' (Annual)'}
                                  </LoadingButton>
                                )}
                              </>
                            )}

                          {auth.user &&
                            !isCurrent &&
                            key === 'free' &&
                            currentPlan !== 'free' && (
                              <Button asChild className="w-full" variant="outline">
                                <Link href="/dashboard">Go to Dashboard</Link>
                              </Button>
                            )}

                          {auth.user && isCurrent && (
                            <Button asChild className="w-full" variant="outline">
                              <Link href={route('billing.index')}>
                                Manage Billing
                              </Link>
                            </Button>
                          )}
                        </>
                      )}
                    </div>
                  </CardContent>
                </Card>
              );
            })}
          </div>

          {/* Trust strip */}
          <div className="flex flex-wrap items-center justify-center gap-6 rounded-2xl border border-border/60 bg-muted/40 px-6 py-4 text-sm text-muted-foreground">
            <span className="flex items-center gap-1.5">
              <RefreshCcw className="h-4 w-4 text-success" />
              Cancel anytime
            </span>
            <span className="flex items-center gap-1.5">
              <ShieldCheck className="h-4 w-4 text-success" />
              Secure payments via Stripe
            </span>
            <span className="flex items-center gap-1.5">
              <Lock className="h-4 w-4 text-success" />
              14-day money-back guarantee. No lock-in.
            </span>
          </div>

          {/* Compare links */}
          <p className="mt-2 text-center text-sm text-muted-foreground">
            Evaluating alternatives?{' '}
            <Link href="/compare/laravel-spark" className="underline hover:text-foreground transition-colors">
              Laravel Spark
            </Link>
            {', '}
            <Link href="/compare/laravel-jetstream" className="underline hover:text-foreground transition-colors">
              Jetstream
            </Link>
            {', '}
            <Link href="/compare/saasykit" className="underline hover:text-foreground transition-colors">
              SaaSykit
            </Link>
            {', and '}
            <Link href="/compare" className="underline hover:text-foreground transition-colors">
              more comparisons →
            </Link>
          </p>

          {/* FAQ */}
          <div className="pt-6">
            <h2 className="mb-6 text-center text-2xl font-bold">
              Common questions
            </h2>
            <FaqAccordion faqs={faqs ?? DEFAULT_FAQS} />
          </div>
        </div>
      </div>
    </>
  );

  // Use DashboardLayout for authenticated users, plain layout for guests
  if (auth.user) {
    return (
      <DashboardLayout>
        {announcementBanner && <AnnouncementBanner {...announcementBanner} />}
        {content}
      </DashboardLayout>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      {announcementBanner && <AnnouncementBanner {...announcementBanner} />}
      <PublicNav canLogin canRegister currentPath="/pricing" />
      {content}
      <PublicFooter />
    </div>
  );
}
