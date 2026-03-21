import { BookOpen, CheckCircle2, CreditCard, Flag, Key, LayoutDashboard, Palette, Settings, Sparkles, type LucideIcon } from "lucide-react";
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

const USE_CASE_OPTIONS = [
  { value: 'saas_founder', label: 'My first SaaS', description: 'Building a product to charge real customers' },
  { value: 'agency', label: 'Client project for an agency', description: 'Deploying this for a client engagement' },
  { value: 'internal_tool', label: 'Internal tool', description: 'For my team — not a public-facing product' },
  { value: 'evaluating', label: 'Evaluating for my team', description: 'Deciding if this is the right stack for us' },
] as const;

type UseCase = typeof USE_CASE_OPTIONS[number]['value'] | '';

export default function Onboarding({ email_verified = false }: OnboardingProps) {
  const { auth, features } = usePage<PageProps>().props;
  const { theme, setTheme } = useTheme();
  const { timezone, setTimezone } = useTimezone();
  const { track } = useAnalytics();

  const [currentStep, setCurrentStep] = useState(0);
  const [name, setName] = useState(auth.user?.name ?? "");
  const [useCase, setUseCase] = useState<UseCase>('');
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
      // Persist use_case to user_settings (non-blocking — failure doesn't gate progress)
      if (useCase) {
        router.post("/api/settings", { key: 'use_case', value: useCase }, {
          preserveState: true,
          preserveScroll: true,
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
                  <CardTitle className="text-2xl">Let's get your SaaS ready to ship</CardTitle>
                  <CardDescription>Three quick steps to make this yours. Takes about 2 minutes.</CardDescription>
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

                  {/* Use case segmentation (optional) */}
                  <div className="space-y-3">
                    <Label>What are you building? <span className="text-xs text-muted-foreground font-normal">(optional)</span></Label>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                      {USE_CASE_OPTIONS.map((option) => (
                        <button
                          key={option.value}
                          type="button"
                          onClick={() => setUseCase(useCase === option.value ? '' : option.value)}
                          aria-pressed={useCase === option.value ? "true" : "false"}
                          className={`flex flex-col items-start rounded-lg border-2 p-3 text-left transition-colors ${
                            useCase === option.value
                              ? "border-primary bg-primary/5"
                              : "border-muted hover:border-muted-foreground/30"
                          }`}
                        >
                          <span className="text-sm font-medium">{option.label}</span>
                          <span className="text-xs text-muted-foreground">{option.description}</span>
                        </button>
                      ))}
                    </div>
                  </div>
                </CardContent>
              </Card>
            </StepContent>

            {/* Step 2: Preferences */}
            <StepContent step={1} currentStep={currentStep}>
              <Card>
                <CardHeader className="text-center">
                  <CardTitle>Make it feel like yours</CardTitle>
                  <CardDescription>Your timezone and theme stay consistent across all your sessions.</CardDescription>
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

            {/* Step 3: Completion Celebration */}
            <StepContent step={2} currentStep={currentStep}>
              <Card>
                <CardHeader className="text-center">
                  <div
                    aria-hidden="true"
                    className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-success/10 ring-4 ring-success/20 motion-safe:animate-pulse"
                  >
                    <CheckCircle2 className="h-8 w-8 text-success" />
                  </div>
                  <CardTitle className="text-2xl">You're all set!</CardTitle>
                  <CardDescription>Your SaaS is ready to ship. Here's where to go first.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
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

                  <div className="flex flex-col gap-3">
                    {features?.admin && (useCase === '' || useCase === 'internal_tool' || useCase === 'evaluating') && (
                      <ActionCard
                        icon={LayoutDashboard}
                        title="Explore the admin panel"
                        description="User management, feature flags, and system health"
                        time="2 min"
                        href="/admin"
                        onNavigate={completeOnboardingAndGoTo}
                      />
                    )}
                    {features?.billing && (useCase === '' || useCase === 'saas_founder' || useCase === 'evaluating') && (
                      <ActionCard
                        icon={CreditCard}
                        title="Set up billing"
                        description="Choose a plan and connect Stripe payments"
                        time="5 min"
                        href="/pricing?ref=onboarding"
                        onNavigate={completeOnboardingAndGoTo}
                      />
                    )}
                    {(useCase === '' || useCase === 'saas_founder' || useCase === 'internal_tool') && (
                      <ActionCard
                        icon={Key}
                        title="Create an API token"
                        description="Start building integrations right away"
                        time="1 min"
                        href="/settings/tokens"
                        onNavigate={completeOnboardingAndGoTo}
                      />
                    )}
                    {(useCase === 'agency') && (
                      <ActionCard
                        icon={Flag}
                        title="Review feature flags"
                        description="Toggle features on or off per deployment for each client"
                        time="2 min"
                        href="/features/feature-flags"
                        onNavigate={completeOnboardingAndGoTo}
                      />
                    )}
                    {(useCase === 'agency') && features?.billing && (
                      <ActionCard
                        icon={CreditCard}
                        title="Understand billing setup"
                        description="Each client deployment has its own Stripe configuration"
                        time="3 min"
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

function ActionCard({
  icon: Icon,
  title,
  description,
  time,
  href,
  onNavigate,
}: {
  icon: LucideIcon;
  title: string;
  description: string;
  time: string;
  href: string;
  onNavigate: (href: string) => void;
}) {
  return (
    <button
      type="button"
      onClick={() => onNavigate(href)}
      className="flex w-full items-center gap-3 rounded-lg border p-3 text-left transition-colors hover:border-primary/40 hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
    >
      <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
        <Icon className="h-4 w-4" />
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-sm font-medium">{title}</p>
        <p className="text-xs text-muted-foreground truncate">{description}</p>
      </div>
      <span className="shrink-0 text-xs text-muted-foreground/60 tabular-nums">{time}</span>
    </button>
  );
}
