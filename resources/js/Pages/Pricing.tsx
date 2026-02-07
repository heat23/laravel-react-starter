import { CheckCircle2, Sparkles } from "lucide-react";

import { useMemo, useState } from "react";

import { Head, Link, router, usePage } from "@inertiajs/react";

import PageHeader from "@/Components/layout/PageHeader";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { LoadingButton } from "@/Components/ui/loading-button";
import { ToggleGroup, ToggleGroupItem } from "@/Components/ui/toggle-group";
import DashboardLayout from "@/Layouts/DashboardLayout";
import type { PageProps } from "@/types";

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
  limits?: Record<string, number | null>;
  features?: string[];
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
}

export default function Pricing() {
  const { tiers, currentPlan, trial, trialEnabled, trialDays, auth } =
    usePage<PricingPageProps>().props;
  const tierEntries = useMemo(() => Object.entries(tiers), [tiers]);
  const [billingPeriod, setBillingPeriod] = useState<"monthly" | "annual">("monthly");
  const [checkoutLoading, setCheckoutLoading] = useState<string | null>(null);

  const hasAnnualPricing = useMemo(() => {
    return tierEntries.some(([, tier]) => tier.price_annual && tier.price_annual > 0);
  }, [tierEntries]);

  const annualSavingsPercent = useMemo(() => {
    const proTier = tiers.pro;
    if (proTier?.price == null || proTier?.price_annual == null || proTier.price <= 0) return 0;
    const monthlyTotal = proTier.price * 12;
    const savings = monthlyTotal - proTier.price_annual;
    return Math.round((savings / monthlyTotal) * 100);
  }, [tiers.pro]);

  const getPrice = (tier: TierConfig) => {
    if (tier.price === null) return { label: "Custom", sublabel: null, savings: null };

    if (billingPeriod === "annual" && tier.price_annual) {
      const monthlyEquivalent = (tier.price_annual / 12).toFixed(2);
      const yearlySavings = tier.price * 12 - tier.price_annual;
      return {
        label: `$${tier.price_annual}/year`,
        sublabel: `$${monthlyEquivalent}/mo`,
        savings: yearlySavings > 0 ? yearlySavings : null,
      };
    }

    return { label: tier.price === 0 ? "Free" : `$${tier.price}/mo`, sublabel: null, savings: null };
  };

  const handleCheckout = (planKey: string) => {
    const tier = tiers[planKey];
    const priceId =
      billingPeriod === "annual" && tier.stripe_price_id_annual
        ? tier.stripe_price_id_annual
        : tier.stripe_price_id;

    setCheckoutLoading(planKey);
    router.post(
      route("billing.subscribe"),
      {
        price_id: priceId,
        quantity: tier.per_seat && tier.min_seats ? tier.min_seats : 1,
      },
      {
        onFinish: () => setCheckoutLoading(null),
      },
    );
  };

  const content = (
    <>
      <Head title="Pricing" />
      <PageHeader
        title="Pricing"
        subtitle="Simple, transparent pricing that grows with you"
      />
      <div className="container py-12">
        <div className="max-w-5xl mx-auto space-y-10">
          {hasAnnualPricing && (
            <div className="flex justify-center">
              <div className="inline-flex items-center gap-4 p-1 bg-muted rounded-lg">
                <ToggleGroup
                  type="single"
                  value={billingPeriod}
                  onValueChange={(value) =>
                    value && setBillingPeriod(value as "monthly" | "annual")
                  }
                >
                  <ToggleGroupItem value="monthly" className="px-4">
                    Monthly
                  </ToggleGroupItem>
                  <ToggleGroupItem value="annual" className="px-4">
                    Annual
                    {annualSavingsPercent > 0 && (
                      <Badge
                        variant="success"
                        className="ml-2"
                      >
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
                <strong className="text-primary">Pro Trial Active</strong> - You have{" "}
                <strong>{trial.daysRemaining}</strong> day
                {trial.daysRemaining !== 1 ? "s" : ""} remaining. Upgrade now to keep your Pro
                features!
              </AlertDescription>
            </Alert>
          )}

          {!auth.user && trialEnabled && (
            <Alert className="border-success/30 bg-success/10">
              <Sparkles className="h-4 w-4 text-success" />
              <AlertDescription className="text-center">
                <strong className="text-success">
                  Start with a {trialDays}-day free Pro trial!
                </strong>{" "}
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
                <Card key={key} className={isCurrent ? "border-primary shadow-md" : ""}>
                  <CardHeader>
                    <div className="flex items-center justify-between">
                      <CardTitle className="text-lg">{tier.name}</CardTitle>
                      <div className="flex items-center gap-2">
                        {tier.coming_soon && (
                          <Badge variant="outline">Coming Soon</Badge>
                        )}
                        {pricing.savings && billingPeriod === "annual" && !tier.coming_soon && (
                          <Badge variant="success">Save ${pricing.savings}</Badge>
                        )}
                        {isCurrent && <Badge variant="secondary">Current</Badge>}
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
                      <p className="text-sm text-muted-foreground">{tier.description}</p>
                    )}

                    <ul className="space-y-2 text-sm">
                      {(tier.features ?? []).map((feature) => (
                        <li key={feature} className="flex items-center gap-2 text-muted-foreground">
                          <CheckCircle2 className="h-4 w-4 text-success shrink-0" />
                          {feature}
                        </li>
                      ))}
                    </ul>

                    <div className="pt-2">
                      {!auth.user && (
                        <Button asChild className="w-full">
                          <Link href="/register">
                            {key === "pro" && trialEnabled
                              ? `Start ${trialDays}-Day Free Trial`
                              : "Get Started"}
                          </Link>
                        </Button>
                      )}

                      {auth.user && !isEnterprise && !isCurrent && key !== "free" && (
                        <>
                          {tier.coming_soon ? (
                            <Button className="w-full" variant="secondary" disabled>
                              Coming Soon
                            </Button>
                          ) : (
                            <LoadingButton
                              className="w-full"
                              onClick={() => handleCheckout(key)}
                              loading={checkoutLoading === key}
                              loadingText="Processing..."
                            >
                              Upgrade to {tier.name}
                              {billingPeriod === "annual" && " (Annual)"}
                            </LoadingButton>
                          )}
                        </>
                      )}

                      {auth.user && !isEnterprise && !isCurrent && key === "free" && (
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
                          <Link href={route("billing.index")}>Manage Billing</Link>
                        </Button>
                      )}
                    </div>
                  </CardContent>
                </Card>
              );
            })}
          </div>
        </div>
      </div>
    </>
  );

  // Use DashboardLayout for authenticated users, plain layout for guests
  if (auth.user) {
    return <DashboardLayout>{content}</DashboardLayout>;
  }

  return <div className="min-h-screen bg-background">{content}</div>;
}
