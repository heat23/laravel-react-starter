import { render, screen, fireEvent } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";

import Onboarding from "./Onboarding";

vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    usePage: vi.fn(() => ({
      url: "/onboarding",
      props: {
        auth: {
          user: {
            name: "Test User",
            email: "test@example.com",
          },
        },
        features: {
          billing: false,
          socialAuth: false,
          emailVerification: true,
          apiTokens: true,
          userSettings: true,
          notifications: false,
          onboarding: true,
        },
      },
    })),
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    router: {
      visit: vi.fn(),
      patch: vi.fn(),
      post: vi.fn(),
    },
    Link: ({
      children,
      href,
    }: {
      children: React.ReactNode;
      href: string;
    }) => <a href={href}>{children}</a>,
  };
});

vi.mock("@/Components/theme/use-theme", () => ({
  useTheme: vi.fn(() => ({
    theme: "system",
    setTheme: vi.fn(),
    resolvedTheme: "light",
  })),
}));

vi.mock("@/hooks/useTimezone", () => ({
  useTimezone: vi.fn(() => ({
    timezone: "UTC",
    setTimezone: vi.fn(),
  })),
}));

describe("Onboarding", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("renders step 1 - Welcome", () => {
    render(<Onboarding />);
    // "Welcome" appears in step indicator and card heading
    expect(screen.getAllByText(/welcome/i).length).toBeGreaterThanOrEqual(1);
    expect(screen.getByLabelText(/your name/i)).toBeInTheDocument();
  });

  it("pre-fills user name", () => {
    render(<Onboarding />);
    const nameInput = screen.getByLabelText(/your name/i) as HTMLInputElement;
    expect(nameInput.value).toBe("Test User");
  });

  it("shows avatar placeholder with user initial", () => {
    render(<Onboarding />);
    expect(screen.getByText("T")).toBeInTheDocument();
  });

  it("navigates to step 2 on Next", () => {
    render(<Onboarding />);
    fireEvent.click(screen.getByText("Next"));
    expect(screen.getByText(/set your preferences/i)).toBeInTheDocument();
  });

  it("shows timezone selector in step 2", () => {
    render(<Onboarding />);
    fireEvent.click(screen.getByText("Next"));
    expect(screen.getByText(/timezone/i)).toBeInTheDocument();
  });

  it("shows theme options in step 2", () => {
    render(<Onboarding />);
    fireEvent.click(screen.getByText("Next"));
    expect(screen.getByText(/theme/i)).toBeInTheDocument();
    expect(screen.getByText("light")).toBeInTheDocument();
    expect(screen.getByText("dark")).toBeInTheDocument();
    expect(screen.getByText("system")).toBeInTheDocument();
  });

  it("navigates to step 3 from step 2", () => {
    render(<Onboarding />);
    fireEvent.click(screen.getByText("Next")); // Step 1 -> 2
    fireEvent.click(screen.getByText("Next")); // Step 2 -> 3
    expect(screen.getByText(/you're all set/i)).toBeInTheDocument();
  });

  it("shows feature cards in step 3", () => {
    render(<Onboarding />);
    fireEvent.click(screen.getByText("Next"));
    fireEvent.click(screen.getByText("Next"));
    expect(screen.getByText("Dashboard")).toBeInTheDocument();
    expect(screen.getByText("Profile")).toBeInTheDocument();
    expect(screen.getByText("API Tokens")).toBeInTheDocument();
  });

  it("shows Get Started button on last step", () => {
    render(<Onboarding />);
    fireEvent.click(screen.getByText("Next"));
    fireEvent.click(screen.getByText("Next"));
    // "Get Started" appears in both step label and finish button
    expect(screen.getByRole("button", { name: "Get Started" })).toBeInTheDocument();
  });

  it("has Skip button on all steps", () => {
    render(<Onboarding />);
    expect(screen.getByText("Skip")).toBeInTheDocument();

    fireEvent.click(screen.getByText("Next"));
    expect(screen.getByText("Skip")).toBeInTheDocument();

    fireEvent.click(screen.getByText("Next"));
    expect(screen.getByText("Skip")).toBeInTheDocument();
  });

  it("can navigate back from step 2", () => {
    render(<Onboarding />);
    fireEvent.click(screen.getByText("Next")); // Go to step 2
    expect(screen.getByText(/set your preferences/i)).toBeInTheDocument();

    fireEvent.click(screen.getByText("Back")); // Go back to step 1
    expect(screen.getByLabelText(/your name/i)).toBeInTheDocument();
  });
});
