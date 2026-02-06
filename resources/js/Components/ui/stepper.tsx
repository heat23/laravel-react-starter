import { Check } from "lucide-react";
import type { LucideIcon } from "lucide-react";

import type { ReactNode } from "react";

import { Button } from "@/Components/ui/button";
import { cn } from "@/lib/utils";

export interface StepDefinition {
  id: string;
  label: string;
  description?: string;
  icon?: LucideIcon;
  optional?: boolean;
}

interface StepperProps {
  steps: StepDefinition[];
  currentStep: number;
  onStepChange: (step: number) => void;
  children: ReactNode;
  className?: string;
}

export function Stepper({ steps, currentStep, children, className }: StepperProps) {
  return (
    <div className={cn("w-full", className)}>
      {/* Step Indicator */}
      <div className="mb-8">
        <div className="flex items-center justify-center">
          {steps.map((step, index) => {
            const isCompleted = index < currentStep;
            const isCurrent = index === currentStep;
            const StepIcon = step.icon;

            return (
              <div key={step.id} className="flex items-center">
                {/* Step Circle */}
                <div className="flex flex-col items-center">
                  <div
                    className={cn(
                      "flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-medium transition-colors",
                      isCompleted && "border-primary bg-primary text-primary-foreground",
                      isCurrent && "border-primary bg-background text-primary",
                      !isCompleted && !isCurrent && "border-muted-foreground/30 text-muted-foreground/50",
                    )}
                    aria-current={isCurrent ? "step" : undefined}
                  >
                    {isCompleted ? (
                      <Check className="h-5 w-5 animate-checkmark" />
                    ) : StepIcon ? (
                      <StepIcon className="h-5 w-5" />
                    ) : (
                      index + 1
                    )}
                  </div>
                  <span
                    className={cn(
                      "mt-2 text-xs font-medium",
                      isCurrent ? "text-foreground" : "text-muted-foreground",
                    )}
                  >
                    {step.label}
                  </span>
                </div>

                {/* Connecting Line */}
                {index < steps.length - 1 && (
                  <div
                    className={cn(
                      "mx-2 h-0.5 w-12 sm:w-20 transition-colors",
                      index < currentStep ? "bg-primary" : "bg-muted-foreground/20",
                    )}
                  />
                )}
              </div>
            );
          })}
        </div>
      </div>

      {/* Step Content */}
      <div key={currentStep} className="animate-fade-in">{children}</div>
    </div>
  );
}

interface StepContentProps {
  step: number;
  currentStep: number;
  children: ReactNode;
  className?: string;
}

export function StepContent({ step, currentStep, children, className }: StepContentProps) {
  if (step !== currentStep) return null;
  return <div key={step} className={cn("animate-fade-in", className)}>{children}</div>;
}

interface StepperNavigationProps {
  currentStep: number;
  totalSteps: number;
  onBack: () => void;
  onNext: () => void;
  onSkip?: () => void;
  nextLabel?: string;
  backLabel?: string;
  finishLabel?: string;
  isNextDisabled?: boolean;
  className?: string;
}

export function StepperNavigation({
  currentStep,
  totalSteps,
  onBack,
  onNext,
  onSkip,
  nextLabel,
  backLabel = "Back",
  finishLabel = "Finish",
  isNextDisabled = false,
  className,
}: StepperNavigationProps) {
  const isFirst = currentStep === 0;
  const isLast = currentStep === totalSteps - 1;

  return (
    <div className={cn("flex items-center justify-between pt-6", className)}>
      <div>
        {!isFirst && (
          <Button variant="outline" onClick={onBack}>
            {backLabel}
          </Button>
        )}
      </div>
      <div className="flex items-center gap-3">
        {onSkip && (
          <Button variant="ghost" onClick={onSkip} className="text-muted-foreground">
            Skip
          </Button>
        )}
        <Button onClick={onNext} disabled={isNextDisabled}>
          {isLast ? finishLabel : nextLabel || "Next"}
        </Button>
      </div>
    </div>
  );
}
