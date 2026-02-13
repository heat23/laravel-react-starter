import axios from "axios";
import { BookOpen, Home, Key, Palette, Settings, Sparkles, User as UserIcon } from "lucide-react";
import { toast } from "sonner";

import { useState, useCallback } from "react";

import { Head, router, usePage } from "@inertiajs/react";

import { TimezoneSelector } from "@/Components/settings/TimezoneSelector";
import { useTheme } from "@/Components/theme";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { StepContent, Stepper, StepperNavigation } from "@/Components/ui/stepper";
import { useTimezone } from "@/hooks/useTimezone";
import type { PageProps } from "@/types";

const steps = [
  { id: "welcome", label: "Welcome", icon: Sparkles },
  { id: "preferences", label: "Preferences", icon: Settings },
  { id: "get-started", label: "Get Started", icon: BookOpen },
];

export default function Onboarding() {
  const { auth } = usePage<PageProps>().props;
  const { theme, setTheme } = useTheme();
  const { timezone, setTimezone } = useTimezone();

  const [currentStep, setCurrentStep] = useState(0);
  const [name, setName] = useState(auth.user?.name ?? "");
  const [saving, setSaving] = useState(false);

  const completeOnboarding = useCallback(async () => {
    setSaving(true);
    // Save onboarding_completed timestamp
    try {
      await axios.post("/api/settings", {
        key: "onboarding_completed",
        value: new Date().toISOString(),
      });
    } catch {
      toast.warning("Could not save onboarding progress, but you can continue.");
    }
    router.visit("/dashboard");
  }, []);

  const handleSkip = useCallback(() => {
    completeOnboarding();
  }, [completeOnboarding]);

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
      setCurrentStep(1);
    } else if (currentStep === 1) {
      // Timezone and theme are saved via their own hooks
      setCurrentStep(2);
    } else {
      // Final step — complete onboarding
      completeOnboarding();
    }
  }, [currentStep, name, auth.user?.name, auth.user?.email, completeOnboarding]);

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
                  <CardTitle className="text-2xl">Welcome to {import.meta.env.VITE_APP_NAME || "the app"}!</CardTitle>
                  <CardDescription>Let's get you set up. This will only take a moment.</CardDescription>
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
                  <CardTitle>You're all set!</CardTitle>
                  <CardDescription>Here's what you can do next.</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid gap-3 sm:grid-cols-2">
                    <FeatureCard
                      icon={Home}
                      title="Dashboard"
                      description="View your overview and key metrics"
                    />
                    <FeatureCard
                      icon={UserIcon}
                      title="Profile"
                      description="Complete your profile information"
                    />
                    <FeatureCard
                      icon={Key}
                      title="API Tokens"
                      description="Generate tokens for API access"
                    />
                    <FeatureCard
                      icon={Settings}
                      title="Settings"
                      description="Fine-tune your preferences"
                    />
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
              finishLabel={saving ? "Setting up..." : "Get Started"}
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
}: {
  icon: typeof Home;
  title: string;
  description: string;
}) {
  return (
    <div className="flex items-start gap-3 rounded-lg border p-3">
      <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
        <Icon className="h-4 w-4" />
      </div>
      <div>
        <p className="text-sm font-medium">{title}</p>
        <p className="text-xs text-muted-foreground">{description}</p>
      </div>
    </div>
  );
}
