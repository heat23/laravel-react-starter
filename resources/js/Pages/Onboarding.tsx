import { BookOpen, CreditCard, Home, Key, Palette, Settings, Sparkles, User as UserIcon } from "lucide-react";
import { toast } from "sonner";

import { useState, useCallback, useEffect } from "react";

import { Head, Link, router, usePage } from "@inertiajs/react";

import { TimezoneSelector } from "@/Components/settings/TimezoneSelector";
import { useTheme } from "@/Components/theme";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { StepContent, Stepper, StepperNavigation } from "@/Components/ui/stepper";
import { useAnalytics } from "@/hooks/useAnalytics";
import { useTimezone } from "@/hooks/useTimezone";
import { AnalyticsEvents } from "@/lib/events";
import type { PageProps } from "@/types";

interface OnboardingProps {
  email_verified?: boolean;
}

const steps = [
  { id: "welcome", label: "Welcome", icon: Sparkles },
  { id: "preferences", label: "Preferences", icon: Settings },
  { id: "get-started", label: "Get Started", icon: BookOpen },
];

export default function Onboarding({ email_verified = false }: OnboardingProps) {
  const { auth, features } = usePage<PageProps>().props;
  const { theme, setTheme } = useTheme();
  const { timezone, setTimezone } = useTimezone();
  const { track } = useAnalytics();

  const [currentStep, setCurrentStep] = useState(0);
  const [name, setName] = useState(auth.user?.name ?? "");
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    track(AnalyticsEvents.ONBOARDING_STARTED);
  }, [track]);

  const completeOnboardingAndGoTo = useCallback((destination: string) => {
    setSaving(true);
    router.post(
      "/onboarding/complete",
      {},
      {
        onSuccess: () => {
          track(AnalyticsEvents.ONBOARDING_COMPLETED);
          router.visit(destination);
        },
        onError: () => {
          setSaving(false);
          toast.warning("Could not save onboarding progress, but you can continue.");
          router.visit(destination);
        },
      }
    );
  }, [track]);

  const completeOnboarding = useCallback(() => {
    completeOnboardingAndGoTo("/dashboard");
  }, [completeOnboardingAndGoTo]);

  const handleSkip = useCallback(() => {
    track(AnalyticsEvents.ONBOARDING_STEP_COMPLETED, { step: 'skipped' });
    completeOnboarding();
  }, [completeOnboarding, track]);

  const handleNext = useCallback(async () => {
    if (currentStep === 0) {
      // Save name if changed — include email to satisfy ProfileUpdateRequest validation
      if (name !== auth.user?.name && name.trim()) {
        setSaving(true);
        await new Promise<void>((resolve) => {
          router.patch("/profile", { name, email: auth.user?.email }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
              setSaving(false);
              resolve();
            },
            onError: (errors) => {
              setSaving(false);
              const firstError = Object.values(errors)[0];
              if (firstError) toast.error(firstError);
              resolve();
            },
          });
        });
      }
      track(AnalyticsEvents.ONBOARDING_STEP_COMPLETED, { step: 'welcome' });
      setCurrentStep(1);
    } else if (currentStep === 1) {
      // Timezone and theme are saved via their own hooks
      track(AnalyticsEvents.ONBOARDING_STEP_COMPLETED, { step: 'preferences' });
      setCurrentStep(2);
    } else {
      // Final step — complete onboarding
      completeOnboarding();
    }
  }, [currentStep, name, auth.user?.name, auth.user?.email, completeOnboarding, track]);

  const handleBack = useCallback(() => {
    setCurrentStep((prev) => Math.max(0, prev - 1));
  }, []);

  return (
    <>
      <Head title="Welcome" />
      <div className="flex min-h-screen items-center justify-center bg-background p-4">
        <div className="w-full max-w-2xl">
          <Stepper steps={steps} currentStep={currentStep} onStepChange={setCurrentStep}>
            {/* Step 1: Welcome */}
            <StepContent step={0} currentStep={currentStep}>
              <Card>
                <CardHeader className="text-center">
                  <CardTitle className="text-2xl">You're 3 steps from a production-ready app.</CardTitle>
                  <CardDescription>We'll set up your profile and preferences — so you can focus on building what makes your product unique.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                  <div className="space-y-2">
                    <Label htmlFor="name">Your name</Label>
                    <Input
                      id="name"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      placeholder="Enter your name"
                    />
                  </div>

                  {/* Avatar placeholder */}
                  <div className="flex items-center gap-4">
                    <div className="flex h-16 w-16 items-center justify-center rounded-full border-2 border-dashed border-muted-foreground/30 text-2xl font-bold text-muted-foreground/50">
                      {name ? name.charAt(0).toUpperCase() : "?"}
                    </div>
                    <p className="text-sm text-muted-foreground">
                      You can customize your avatar later in your profile settings.
                    </p>
                  </div>
                </CardContent>
              </Card>
            </StepContent>

            {/* Step 2: Preferences */}
            <StepContent step={1} currentStep={currentStep}>
              <Card>
                <CardHeader className="text-center">
                  <CardTitle>Set your preferences</CardTitle>
                  <CardDescription>Customize your experience. You can change these anytime.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                  <div className="space-y-2">
                    <Label>Timezone</Label>
                    <TimezoneSelector value={timezone} onChange={setTimezone} />
                  </div>

                  <div className="space-y-3">
                    <Label>Theme</Label>
                    <div className="grid grid-cols-3 gap-3">
                      {(["light", "dark", "system"] as const).map((t) => (
                        <button
                          key={t}
                          onClick={() => setTheme(t)}
                          aria-pressed={theme === t ? "true" : "false"}
                          className={`flex flex-col items-center gap-2 rounded-lg border-2 p-4 transition-colors ${
                            theme === t
                              ? "border-primary bg-primary/5"
                              : "border-muted hover:border-muted-foreground/30"
                          }`}
                        >
                          <Palette className="h-5 w-5" />
                          <span className="text-sm font-medium capitalize">{t}</span>
                        </button>
                      ))}
                    </div>
                  </div>
                </CardContent>
              </Card>
            </StepContent>

            {/* Step 3: Get Started */}
            <StepContent step={2} currentStep={currentStep}>
              <Card>
                <CardHeader className="text-center">
                  <CardTitle>You're ready to ship.</CardTitle>
                  <CardDescription>Your app is production-ready. Pick where to go first.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex flex-col items-center gap-2 pb-2">
                    <button
                      type="button"
                      onClick={() => completeOnboardingAndGoTo("/dashboard")}
                      disabled={saving}
                      className="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50"
                    >
                      Go to your dashboard →
                    </button>
                    {features?.apiDocs && (
                      <a
                        href="/docs"
                        className="text-xs text-muted-foreground underline-offset-2 hover:underline"
                      >
                        Read the docs
                      </a>
                    )}
                  </div>
                  {!email_verified && (
                    <Alert className="border-warning/30 bg-warning/10">
                      <AlertDescription className="text-sm">
                        <strong>Check your inbox</strong> — we sent a verification link to{' '}
                        <span className="font-medium">{auth.user?.email}</span>.{' '}
                        Without verification you won&apos;t be able to access all features.{' '}
                        <Link
                          href="/email/verification-notification"
                          className="underline text-primary"
                          as="button"
                          method="post"
                        >
                          Resend
                        </Link>
                      </AlertDescription>
                    </Alert>
                  )}

                  <div className="grid gap-3 sm:grid-cols-2">
                    <FeatureCard
                      icon={Home}
                      title="Dashboard"
                      description="View your overview and key metrics"
                      href="/dashboard"
                      onNavigate={completeOnboardingAndGoTo}
                    />
                    <FeatureCard
                      icon={UserIcon}
                      title="Profile"
                      description="Complete your profile information"
                      href="/settings/profile"
                      onNavigate={completeOnboardingAndGoTo}
                    />
                    <FeatureCard
                      icon={Key}
                      title="API Tokens"
                      description="Generate tokens for API access"
                      href="/settings/tokens"
                      onNavigate={completeOnboardingAndGoTo}
                      highlighted
                    />
                    <FeatureCard
                      icon={Settings}
                      title="Settings"
                      description="Fine-tune your preferences"
                      href="/settings"
                      onNavigate={completeOnboardingAndGoTo}
                    />
                    {features?.billing && (
                      <FeatureCard
                        icon={CreditCard}
                        title="Upgrade to Pro"
                        description="Unlock billing, more API tokens, and team features"
                        href="/pricing?ref=onboarding"
                        onNavigate={completeOnboardingAndGoTo}
                      />
                    )}
                  </div>
                </CardContent>
              </Card>
            </StepContent>

            <StepperNavigation
              currentStep={currentStep}
              totalSteps={steps.length}
              onBack={handleBack}
              onNext={handleNext}
              onSkip={!saving ? handleSkip : undefined}
              finishLabel={saving ? "Setting up..." : "Go to Dashboard"}
              isNextDisabled={saving}
            />
          </Stepper>
        </div>
      </div>
    </>
  );
}

function FeatureCard({
  icon: Icon,
  title,
  description,
  href,
  onNavigate,
  highlighted = false,
}: {
  icon: typeof Home;
  title: string;
  description: string;
  href: string;
  onNavigate: (href: string) => void;
  highlighted?: boolean;
}) {
  return (
    <button
      type="button"
      onClick={() => onNavigate(href)}
      className={`flex w-full items-start gap-3 rounded-lg border p-3 text-left transition-colors hover:border-primary/40 hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring ${
        highlighted ? 'border-primary/30 bg-primary/5 ring-1 ring-primary/20' : ''
      }`}
    >
      <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
        <Icon className="h-4 w-4" />
      </div>
      <div>
        <p className="text-sm font-medium">{title}</p>
        <p className="text-xs text-muted-foreground">{description}</p>
      </div>
    </button>
  );
}
