import { render, screen, fireEvent } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";

import { StepContent, Stepper, StepperNavigation } from "./stepper";

const steps = [
  { id: "step-1", label: "Step One" },
  { id: "step-2", label: "Step Two" },
  { id: "step-3", label: "Step Three" },
];

describe("Stepper", () => {
  it("renders correct number of step labels", () => {
    render(
      <Stepper steps={steps} currentStep={0} onStepChange={vi.fn()}>
        <div>Content</div>
      </Stepper>,
    );
    expect(screen.getByText("Step One")).toBeInTheDocument();
    expect(screen.getByText("Step Two")).toBeInTheDocument();
    expect(screen.getByText("Step Three")).toBeInTheDocument();
  });

  it("marks current step with aria-current", () => {
    const { container } = render(
      <Stepper steps={steps} currentStep={1} onStepChange={vi.fn()}>
        <div>Content</div>
      </Stepper>,
    );
    const currentStep = container.querySelector('[aria-current="step"]');
    expect(currentStep).toBeInTheDocument();
    expect(currentStep?.textContent).toBe("2");
  });

  it("shows checkmark for completed steps", () => {
    const { container } = render(
      <Stepper steps={steps} currentStep={2} onStepChange={vi.fn()}>
        <div>Content</div>
      </Stepper>,
    );
    // Steps 0 and 1 are completed — should show check icons
    const checkmarks = container.querySelectorAll(".animate-checkmark");
    expect(checkmarks.length).toBe(2);
  });

  it("renders children", () => {
    render(
      <Stepper steps={steps} currentStep={0} onStepChange={vi.fn()}>
        <div data-testid="child">Child Content</div>
      </Stepper>,
    );
    expect(screen.getByTestId("child")).toBeInTheDocument();
  });
});

describe("StepContent", () => {
  it("renders content only when step matches currentStep", () => {
    const { rerender } = render(
      <StepContent step={0} currentStep={0}>
        <div data-testid="content">Step 0 Content</div>
      </StepContent>,
    );
    expect(screen.getByTestId("content")).toBeInTheDocument();

    rerender(
      <StepContent step={0} currentStep={1}>
        <div data-testid="content">Step 0 Content</div>
      </StepContent>,
    );
    expect(screen.queryByTestId("content")).not.toBeInTheDocument();
  });
});

describe("StepperNavigation", () => {
  it("disables back button on first step", () => {
    render(
      <StepperNavigation
        currentStep={0}
        totalSteps={3}
        onBack={vi.fn()}
        onNext={vi.fn()}
      />,
    );
    // Back button should not be present on first step
    expect(screen.queryByText("Back")).not.toBeInTheDocument();
  });

  it("shows back button on non-first steps", () => {
    render(
      <StepperNavigation
        currentStep={1}
        totalSteps={3}
        onBack={vi.fn()}
        onNext={vi.fn()}
      />,
    );
    expect(screen.getByText("Back")).toBeInTheDocument();
  });

  it("shows Finish label on last step", () => {
    render(
      <StepperNavigation
        currentStep={2}
        totalSteps={3}
        onBack={vi.fn()}
        onNext={vi.fn()}
      />,
    );
    expect(screen.getByText("Finish")).toBeInTheDocument();
  });

  it("shows Next label on non-last steps", () => {
    render(
      <StepperNavigation
        currentStep={0}
        totalSteps={3}
        onBack={vi.fn()}
        onNext={vi.fn()}
      />,
    );
    expect(screen.getByText("Next")).toBeInTheDocument();
  });

  it("calls onNext when Next is clicked", () => {
    const onNext = vi.fn();
    render(
      <StepperNavigation
        currentStep={0}
        totalSteps={3}
        onBack={vi.fn()}
        onNext={onNext}
      />,
    );
    fireEvent.click(screen.getByText("Next"));
    expect(onNext).toHaveBeenCalledTimes(1);
  });

  it("calls onBack when Back is clicked", () => {
    const onBack = vi.fn();
    render(
      <StepperNavigation
        currentStep={1}
        totalSteps={3}
        onBack={onBack}
        onNext={vi.fn()}
      />,
    );
    fireEvent.click(screen.getByText("Back"));
    expect(onBack).toHaveBeenCalledTimes(1);
  });

  it("shows Skip button when onSkip is provided", () => {
    const onSkip = vi.fn();
    render(
      <StepperNavigation
        currentStep={0}
        totalSteps={3}
        onBack={vi.fn()}
        onNext={vi.fn()}
        onSkip={onSkip}
      />,
    );
    expect(screen.getByText("Skip")).toBeInTheDocument();
    fireEvent.click(screen.getByText("Skip"));
    expect(onSkip).toHaveBeenCalledTimes(1);
  });

  it("uses custom finish label", () => {
    render(
      <StepperNavigation
        currentStep={2}
        totalSteps={3}
        onBack={vi.fn()}
        onNext={vi.fn()}
        finishLabel="Get Started"
      />,
    );
    expect(screen.getByText("Get Started")).toBeInTheDocument();
  });
});
