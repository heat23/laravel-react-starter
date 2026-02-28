import { AlertCircle, CheckCircle2, Download, HelpCircle, Info, Receipt } from "lucide-react";

import { useEffect, useState } from "react";

import { Head, Link, router, usePage } from "@inertiajs/react";

import { CancelSubscriptionDialog } from "@/Components/billing/CancelSubscriptionDialog";
import { ResumeSubscriptionDialog } from "@/Components/billing/ResumeSubscriptionDialog";
import { StatusBadge } from "@/Components/billing/StatusBadge";
import PageHeader from "@/Components/layout/PageHeader";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { LoadingButton } from "@/Components/ui/loading-button";
import DashboardLayout from "@/Layouts/DashboardLayout";

interface SubscriptionInfo {
  name: string;
  status: string;
  priceId: string;
  trialEndsAt?: string | null;
  endsAt?: string | null;
  onGracePeriod: boolean;
  canceled: boolean;
  active: boolean;
}

interface PlatformTrial {
  endsAt: string;
  daysRemaining: number;
}

interface Invoice {
  id: string;
  date: string;
  amount: number;
  status: string;
  invoice_pdf: string | null;
}

interface IncompletePayment {
  paymentId: string;
  confirmUrl: string;
}

interface BillingPageProps {
  subscription?: SubscriptionInfo | null;
  platformTrial?: PlatformTrial | null;
  incompletePayment?: IncompletePayment | null;
  invoices?: Invoice[];
  graceDays?: number;
}

function formatSubscriptionDate(dateString: string): { formatted: string; relative: string } {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = date.getTime() - now.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  let relative = "";
  if (diffDays < 0) {
    relative = "Expired";
  } else if (diffDays === 0) {
    relative = "Today";
  } else if (diffDays === 1) {
    relative = "Tomorrow";
  } else if (diffDays <= 7) {
    relative = `${diffDays} days`;
  } else if (diffDays <= 30) {
    const weeks = Math.floor(diffDays / 7);
    relative = `${weeks} ${weeks === 1 ? "week" : "weeks"}`;
  } else {
    relative = `${Math.floor(diffDays / 30)} months`;
  }

  return {
    formatted: date.toLocaleDateString(),
    relative,
  };
}

export default function BillingIndex() {
  const { subscription, platformTrial, incompletePayment, invoices = [], graceDays = 7 } =
    usePage<BillingPageProps>().props;
  const [cancelDialogOpen, setCancelDialogOpen] = useState(false);
  const [resumeDialogOpen, setResumeDialogOpen] = useState(false);
  const [checkoutSuccess, setCheckoutSuccess] = useState(false);
  const [portalLoading, setPortalLoading] = useState(false);

  useEffect(() => {
    if (typeof window === "undefined") {
      return;
    }

    const params = new URLSearchParams(window.location.search);
    if (params.get("checkout") === "success") {
      setCheckoutSuccess(true);

      params.delete("checkout");
      const nextQuery = params.toString();
      const nextUrl = nextQuery ? `${window.location.pathname}?${nextQuery}` : window.location.pathname;
      window.history.replaceState({}, "", nextUrl);
    }
  }, []);

  const handlePortal = () => {
    setPortalLoading(true);
    router.visit(route("billing.portal"), {
      onFinish: () => setPortalLoading(false),
    });
  };

  const handleCancelSuccess = () => {
    router.reload();
  };

  const handleResumeSuccess = () => {
    router.reload();
  };

  return (
    <DashboardLayout>
      <Head title="Billing" />
      <PageHeader
        title="Billing"
        subtitle="Manage your subscription and payment details"
      />
      <div className="container py-8">
        <div className="max-w-2xl mx-auto space-y-6">
          {checkoutSuccess && (
            <Alert className="border-success/20 bg-success/5">
              <CheckCircle2 className="h-4 w-4 text-success" />
              <AlertTitle>
                {!subscription ? "Welcome!" : "Checkout complete"}
              </AlertTitle>
              <AlertDescription>
                {!subscription
                  ? "You're now subscribed! It may take a moment for your subscription details to appear."
                  : "Thanks for upgrading! It may take a moment for your subscription details to appear."}
              </AlertDescription>
            </Alert>
          )}

          {platformTrial && (
            <Alert className="border-primary/20 bg-primary/5">
              <Info className="h-4 w-4 text-primary" />
              <AlertTitle>Pro Trial Active</AlertTitle>
              <AlertDescription>
                You have <strong>{platformTrial.daysRemaining} days</strong> remaining to explore all
                Pro features. Trial expires on{" "}
                <strong>{new Date(platformTrial.endsAt).toLocaleDateString()}</strong>.{" "}
                <Link
                  href="/pricing"
                  className="underline font-medium rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  Upgrade now
                </Link>{" "}
                to keep access after your trial ends.
              </AlertDescription>
            </Alert>
          )}

          {incompletePayment && (
            <Alert variant="destructive">
              <AlertCircle className="h-4 w-4" />
              <AlertTitle>Payment Confirmation Required</AlertTitle>
              <AlertDescription>
                Your subscription requires payment confirmation.{" "}
                <a
                  href={incompletePayment.confirmUrl}
                  className="underline font-medium rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  Complete payment now
                </a>
              </AlertDescription>
            </Alert>
          )}

          {subscription?.status === "incomplete" && !incompletePayment && (
            <Alert variant="destructive">
              <Info className="h-4 w-4" />
              <AlertTitle>Payment Processing</AlertTitle>
              <AlertDescription>
                Your subscription is being set up. This usually takes a few moments. If this
                persists, please contact support or visit the{" "}
                <LoadingButton
                  variant="link"
                  onClick={handlePortal}
                  loading={portalLoading}
                  loadingText="Opening..."
                  className="underline font-medium rounded-sm p-0 h-auto focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  billing portal
                </LoadingButton>
                .
              </AlertDescription>
            </Alert>
          )}

          {subscription?.status === "past_due" && (
            <Alert variant="destructive">
              <AlertCircle className="h-4 w-4" />
              <AlertTitle>Payment Failed - Automatic Retry in Progress</AlertTitle>
              <AlertDescription>
                Your last payment failed. Stripe will automatically retry charging your card. To
                avoid service interruption,{" "}
                <button
                  type="button"
                  onClick={handlePortal}
                  className="underline font-medium rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                  aria-label="Open Stripe billing portal to update payment method"
                >
                  update your payment method now
                </button>
                .
                <p className="mt-2 text-sm text-muted-foreground">
                  <strong>Note:</strong> If payment fails for {graceDays} days, access to paid
                  features will be suspended until payment is resolved.
                </p>
              </AlertDescription>
            </Alert>
          )}

          {subscription?.status === "incomplete_expired" && (
            <Alert variant="destructive">
              <AlertCircle className="h-4 w-4" />
              <AlertTitle>Subscription Expired</AlertTitle>
              <AlertDescription>
                Your subscription could not be completed and has expired. Please start a new
                subscription to access paid features.{" "}
                <Link
                  href="/pricing"
                  className="underline font-medium rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  View plans
                </Link>
              </AlertDescription>
            </Alert>
          )}

          {subscription?.onGracePeriod && subscription.endsAt && (
            <Alert variant="destructive">
              <AlertCircle className="h-4 w-4" />
              <AlertTitle>Subscription Ending</AlertTitle>
              <AlertDescription>
                Your subscription has been canceled and will end on{" "}
                <strong>{new Date(subscription.endsAt).toLocaleDateString()}</strong>. You can
                resume your subscription at any time to continue enjoying all features.
              </AlertDescription>
            </Alert>
          )}

          {subscription?.canceled && !subscription.onGracePeriod && (
            <Alert>
              <Info className="h-4 w-4" />
              <AlertTitle>Subscription Ended</AlertTitle>
              <AlertDescription>
                Your subscription has ended. Upgrade to regain access to premium features.
              </AlertDescription>
            </Alert>
          )}

          <Card>
            <CardHeader>
              <CardTitle>Subscription Status</CardTitle>
              <CardDescription>
                {subscription
                  ? "Your current plan details."
                  : platformTrial
                    ? "You're on a Pro trial. Subscribe to keep access after your trial ends."
                    : "You are not subscribed to a paid plan yet."}
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              {subscription ? (
                <div className="space-y-4">
                  <div className="flex items-center justify-between pb-4 border-b border-border">
                    <div>
                      <p className="text-xs uppercase tracking-wide text-muted-foreground mb-1">
                        Current Plan
                      </p>
                      <p className="text-2xl font-semibold text-foreground">{subscription.name}</p>
                    </div>
                    <div className="scale-125">
                      <StatusBadge status={subscription.status} />
                    </div>
                  </div>

                  <div className="space-y-3">
                    {subscription.trialEndsAt && (
                      <div className="flex items-center justify-between py-2 border-b border-border/50">
                        <span className="text-sm font-medium text-muted-foreground">
                          Trial ends
                        </span>
                        <span className="text-sm font-semibold text-foreground tabular-nums">
                          {new Date(subscription.trialEndsAt).toLocaleDateString()}
                        </span>
                      </div>
                    )}
                    {subscription.endsAt && subscription.onGracePeriod && (
                      <div className="flex items-center justify-between py-2 border-b border-border/50">
                        <span className="text-sm font-medium text-muted-foreground">
                          Access until
                        </span>
                        <div className="text-right">
                          <p className="text-sm font-semibold text-destructive tabular-nums">
                            {formatSubscriptionDate(subscription.endsAt).formatted}
                          </p>
                          <p className="text-xs text-muted-foreground">
                            {formatSubscriptionDate(subscription.endsAt).relative}
                          </p>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              ) : platformTrial ? (
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-medium text-muted-foreground">Plan</span>
                    <span className="text-sm text-foreground">Pro Trial</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-medium text-muted-foreground">Status</span>
                    <StatusBadge status="trialing" />
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-medium text-muted-foreground">Trial expires</span>
                    <span className="text-sm text-foreground">
                      {new Date(platformTrial.endsAt).toLocaleDateString()}
                    </span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-medium text-muted-foreground">
                      Days remaining
                    </span>
                    <span className="text-sm text-foreground">{platformTrial.daysRemaining}</span>
                  </div>
                </div>
              ) : (
                <div className="py-8 text-center space-y-4">
                  <div className="mx-auto w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg
                      className="w-8 h-8 text-primary"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M13 10V3L4 14h7v7l9-11h-7z"
                      />
                    </svg>
                  </div>
                  <div>
                    <p className="text-sm font-medium text-foreground mb-1">
                      Ready to unlock premium features?
                    </p>
                    <p className="text-sm text-muted-foreground">
                      Get unlimited access with a paid subscription
                    </p>
                  </div>
                </div>
              )}

              <div className="flex flex-col gap-3 pt-4 border-t border-border">
                {subscription ? (
                  <>
                    {subscription.onGracePeriod ? (
                      <Button onClick={() => setResumeDialogOpen(true)} className="w-full sm:w-auto">
                        Resume Subscription
                      </Button>
                    ) : (
                      <div className="flex flex-col sm:flex-row gap-3">
                        <LoadingButton
                          onClick={handlePortal}
                          loading={portalLoading}
                          loadingText="Opening portal..."
                          className="flex-1 sm:flex-none"
                        >
                          Manage Billing
                        </LoadingButton>
                        {subscription.active && (
                          <Button
                            variant="outline"
                            onClick={() => setCancelDialogOpen(true)}
                            className="flex-1 sm:flex-none"
                          >
                            Cancel Subscription
                          </Button>
                        )}
                      </div>
                    )}
                  </>
                ) : (
                  <Button asChild className="w-full sm:w-auto">
                    <Link href="/pricing">
                      {platformTrial ? "Upgrade Now" : "View Plans"}
                    </Link>
                  </Button>
                )}
                <Button asChild variant="outline" className="w-full sm:w-auto group">
                  <Link href="/contact" className="gap-2">
                    <HelpCircle className="h-4 w-4 transition-transform group-hover:scale-110" />
                    Need help?
                  </Link>
                </Button>
              </div>
            </CardContent>
          </Card>

          {subscription && (
            <Card>
              <CardHeader>
                <CardTitle>Billing History</CardTitle>
                <CardDescription>Your past invoices and payments</CardDescription>
              </CardHeader>
              <CardContent>
                {invoices.length === 0 ? (
                  <div className="flex flex-col items-center justify-center py-8 text-center">
                    <Receipt className="h-8 w-8 text-muted-foreground/50 mb-3" />
                    <p className="font-medium">No invoices yet</p>
                    <p className="text-sm text-muted-foreground mt-1">
                      Your invoices will appear here after your first billing cycle.
                    </p>
                  </div>
                ) : (
                  <div className="space-y-2">
                    {invoices.slice(0, 5).map((invoice) => (
                      <div
                        key={invoice.id}
                        className="flex items-center justify-between py-3 border-b border-border/50 last:border-0"
                      >
                        <div>
                          <p className="text-sm font-medium text-foreground">
                            {new Date(invoice.date).toLocaleDateString()}
                          </p>
                          <p className="text-xs text-muted-foreground">
                            {invoice.status === "paid" ? "Paid" : "Pending"}
                          </p>
                        </div>
                        <div className="flex items-center gap-3">
                          <span className="text-sm font-semibold tabular-nums">
                            ${(invoice.amount / 100).toFixed(2)}
                          </span>
                          {invoice.invoice_pdf && (
                            <Button variant="ghost" size="sm" asChild>
                              <a
                                href={invoice.invoice_pdf}
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label={`Download invoice from ${new Date(invoice.date).toLocaleDateString()}`}
                              >
                                <Download className="h-4 w-4" />
                              </a>
                            </Button>
                          )}
                        </div>
                      </div>
                    ))}
                    {invoices.length > 5 && (
                      <LoadingButton
                        variant="ghost"
                        className="w-full mt-2"
                        onClick={handlePortal}
                        loading={portalLoading}
                        loadingText="Opening portal..."
                      >
                        View all {invoices.length} invoices
                      </LoadingButton>
                    )}
                  </div>
                )}
              </CardContent>
            </Card>
          )}
        </div>
      </div>

      <CancelSubscriptionDialog
        open={cancelDialogOpen}
        onOpenChange={setCancelDialogOpen}
        onSuccess={handleCancelSuccess}
      />

      <ResumeSubscriptionDialog
        open={resumeDialogOpen}
        onOpenChange={setResumeDialogOpen}
        onSuccess={handleResumeSuccess}
      />
    </DashboardLayout>
  );
}
