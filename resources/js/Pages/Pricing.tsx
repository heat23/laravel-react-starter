import { CheckCircle2, Lock, RefreshCcw, ShieldCheck, Sparkles } from 'lucide-react';

import { useEffect, useMemo, useState } from 'react';

import { Head, Link, router, usePage } from '@inertiajs/react';

import { AnnouncementBanner, type AnnouncementBannerProps } from '@/Components/layout/AnnouncementBanner';
import { MarketingNav } from '@/Components/layout/MarketingNav';
import PageHeader from '@/Components/layout/PageHeader';
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
  announcementBanner?: AnnouncementBannerProps | null;
}

const DEFAULT_FAQS: FaqItem[] = [
  {
    question: 'Can I change plans?',
    answer:
      'Yes. You can upgrade or downgrade your plan at any time. Changes take effect immediately and billing is prorated automatically.',
  },
  {
    question: 'What happens when I cancel?',
    answer:
      'Your subscription remains active until the end of the billing period. After that, your account reverts to the Free plan — your data is never deleted.',
  },
  {
    question: 'Is billing automatic?',
    answer:
      'Yes. Subscriptions renew automatically each billing cycle. You\'ll receive an email receipt before each renewal. Cancel anytime from your billing dashboard.',
  },
  {
    question: 'Do you offer refunds?',
    answer:
      'We offer a 30-day money-back guarantee. If you\'re not satisfied, contact us within 30 days of your first payment for a full refund.',
  },
  {
    question: 'How do team seats work?',
    answer:
      'The Team plan includes a minimum of 3 seats and supports up to 50. You can adjust the seat count at any time and billing is prorated accordingly.',
  },
];

export default function Pricing() {
  const { tiers, currentPlan, trial, trialEnabled, trialDays, auth, faqs, announcementBanner } =
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
    if (tier.price === null)
      return { label: 'Custom', sublabel: null, savings: null };

    if (billingPeriod === 'annual' && tier.price_annual) {
      const monthlyEquivalent = (tier.price_annual / 12).toFixed(2);
      const yearlySavings = tier.price * 12 - tier.price_annual;
      return {
        label: `$${tier.price_annual}/year`,
        sublabel: `$${monthlyEquivalent}/mo`,
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

  const content = (
    <>
      <Head title="Pricing">
        <meta
          name="description"
          content="One-time purchase. Free, Pro, Team, and Enterprise plans — no recurring subscription fees. Full source code included."
        />
      </Head>
      <PageHeader
        title="Pricing"
        subtitle="One-time purchase. Full source code. No subscriptions."
      />
      <div className="container py-12">
        <div className="max-w-5xl mx-auto space-y-10">
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
              const isEnterprise = tier.price === null;
              const pricing = getPrice(tier);

              return (
                <Card
                  key={key}
                  className={
                    isCurrent
                      ? 'border-primary shadow-md'
                      : tier.popular
                        ? 'ring-2 ring-primary shadow-md'
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
                    <div className="flex items-center justify-between">
                      <CardTitle className="text-lg">{tier.name}</CardTitle>
                      <div className="flex items-center gap-2">
                        {tier.coming_soon && (
                          <Badge variant="outline">Coming Soon</Badge>
                        )}
                        {tier.popular && !isCurrent && !tier.coming_soon && (
                          <Badge variant="default">Most Popular</Badge>
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
                          ({pricing.sublabel} billed annually)
                        </CardDescription>
                      )}
                      {tier.per_seat && tier.min_seats && (
                        <CardDescription className="text-xs text-muted-foreground">
                          per seat, min {tier.min_seats} seats
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
                        !isEnterprise &&
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
                        !isEnterprise &&
                        !isCurrent &&
                        key === 'free' &&
                        currentPlan !== 'free' && (
                          <Button asChild className="w-full" variant="outline">
                            <Link href="/dashboard">Go to Dashboard</Link>
                          </Button>
                        )}

                      {auth.user && isEnterprise && !isCurrent && (
                        <Button asChild className="w-full" variant="outline">
                          <Link href="/contact">Contact Sales</Link>
                        </Button>
                      )}

                      {auth.user && isCurrent && (
                        <Button asChild className="w-full" variant="outline">
                          <Link href={route('billing.index')}>
                            Manage Billing
                          </Link>
                        </Button>
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
              30-day money-back guarantee
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
              Frequently asked questions
            </h2>
            <div className="space-y-4">
              {(faqs ?? DEFAULT_FAQS).map((faq) => (
                <div
                  key={faq.question}
                  className="rounded-2xl border border-border/70 bg-card p-5"
                >
                  <h3 className="mb-1.5 font-semibold">{faq.question}</h3>
                  <p className="text-sm text-muted-foreground">{faq.answer}</p>
                </div>
              ))}
            </div>
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
      <MarketingNav canLogin canRegister currentPath="/pricing" />
      {content}
    </div>
  );
}
