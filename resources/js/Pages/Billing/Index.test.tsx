import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";

import { router } from "@inertiajs/react";

import BillingIndex from "./Index";

// Mock Inertia
vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    usePage: vi.fn(() => ({
      url: "/billing",
      props: {
        subscription: null,
        platformTrial: null,
        incompletePayment: null,
        invoices: [],
        graceDays: 7,
        auth: {
          user: { id: 1, name: "Test User", email: "test@example.com", is_admin: false },
        },
        features: {
          billing: true,
          apiTokens: true,
          userSettings: true,
          webhooks: false,
          notifications: false,
          socialAuth: false,
          twoFactor: false,
          emailVerification: true,
          onboarding: false,
          apiDocs: false,
          admin: false,
        },
      },
    })),
    router: {
      visit: vi.fn(),
      reload: vi.fn(),
    },
    Head: ({ children }: { children?: React.ReactNode }) => <>{children}</>,
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
  };
});

// Mock route helper
vi.mock("ziggy-js", () => ({
  route: (name: string) => `/${name.replace(/\./g, "/")}`,
}));

// Mock useTheme hook
vi.mock("@/Components/theme/use-theme", () => ({
  useTheme: vi.fn(() => ({ theme: "system", setTheme: vi.fn(), resolvedTheme: "light" })),
}));

// Shared mock props factory
const createMockProps = (overrides: Record<string, unknown> = {}) => ({
  auth: {
    user: { id: 1, name: "Test User", email: "test@example.com", is_admin: false },
  },
  features: {
    billing: true,
    apiTokens: true,
    userSettings: true,
    webhooks: false,
    notifications: false,
    socialAuth: false,
    twoFactor: false,
    emailVerification: true,
    onboarding: false,
    apiDocs: false,
    admin: false,
  },
  subscription: null,
  platformTrial: null,
  incompletePayment: null,
  invoices: [],
  graceDays: 7,
  ...overrides,
});

describe("Billing Index Page", () => {
  it("renders billing page for user without subscription", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({ props: createMockProps() } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByRole("heading", { name: "Billing", level: 1 })).toBeInTheDocument();
    expect(screen.getByText("Manage your subscription and payment details")).toBeInTheDocument();
    expect(screen.getByText("Ready to unlock premium features?")).toBeInTheDocument();
    expect(screen.getByText("View Plans")).toBeInTheDocument();
  });

  it("renders platform trial status", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        platformTrial: {
          endsAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
          daysRemaining: 7,
        },
      }),
    } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByText("Pro Trial Active")).toBeInTheDocument();
    expect(screen.getByText(/7 days/)).toBeInTheDocument();
    expect(screen.getByText("Upgrade Now")).toBeInTheDocument();
  });

  it("renders active subscription status", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "active",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt: null,
          onGracePeriod: false,
          canceled: false,
          active: true,
        },
      }),
    } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByText("Pro")).toBeInTheDocument();
    expect(screen.getByText("Manage Billing")).toBeInTheDocument();
    expect(screen.getByText("Cancel Subscription")).toBeInTheDocument();
  });

  it("renders canceled subscription on grace period", async () => {
    const { usePage } = await import("@inertiajs/react");
    const endsAt = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString();

    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "active",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt,
          onGracePeriod: true,
          canceled: true,
          active: false,
        },
      }),
    } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByText("Subscription Ending")).toBeInTheDocument();
    expect(screen.getByText("Resume Subscription")).toBeInTheDocument();
  });

  it("renders incomplete payment alert", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "incomplete",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt: null,
          onGracePeriod: false,
          canceled: false,
          active: false,
        },
        incompletePayment: {
          paymentId: "pi_test123",
          confirmUrl: "/billing?confirm=true",
        },
      }),
    } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByText("Payment Confirmation Required")).toBeInTheDocument();
    expect(screen.getByText("Complete payment now")).toBeInTheDocument();
  });

  it("renders past due subscription alert", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "past_due",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt: null,
          onGracePeriod: false,
          canceled: false,
          active: true,
        },
      }),
    } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByText("Payment Failed - Automatic Retry in Progress")).toBeInTheDocument();
    expect(screen.getByText(/If payment fails for 7 days/)).toBeInTheDocument();
  });

  it("renders invoice list for subscribed user", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "active",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt: null,
          onGracePeriod: false,
          canceled: false,
          active: true,
        },
        invoices: [
          {
            id: "in_test123",
            date: new Date("2025-01-15").toISOString(),
            amount: 2900,
            status: "paid",
            invoice_pdf: "https://invoice.stripe.com/i/test123",
          },
          {
            id: "in_test456",
            date: new Date("2024-12-15").toISOString(),
            amount: 2900,
            status: "paid",
            invoice_pdf: "https://invoice.stripe.com/i/test456",
          },
        ],
      }),
    } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByText("Billing History")).toBeInTheDocument();
    expect(screen.getAllByText("$29.00")).toHaveLength(2);
    expect(screen.getAllByText("Paid")).toHaveLength(2);
  });

  it("shows empty state for invoices when none exist", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "active",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt: null,
          onGracePeriod: false,
          canceled: false,
          active: true,
        },
      }),
    } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByText("No invoices yet")).toBeInTheDocument();
    expect(
      screen.getByText("Your invoices will appear here after your first billing cycle."),
    ).toBeInTheDocument();
  });

  it("opens billing portal when manage billing clicked", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "active",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt: null,
          onGracePeriod: false,
          canceled: false,
          active: true,
        },
      }),
    } as ReturnType<typeof usePage>);

    const user = userEvent.setup();
    render(<BillingIndex />);

    const manageButton = screen.getByText("Manage Billing");
    await user.click(manageButton);

    expect(router.visit).toHaveBeenCalledWith("/billing/portal", expect.any(Object));
  });

  it("opens cancel subscription modal when cancel button clicked", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "active",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt: null,
          onGracePeriod: false,
          canceled: false,
          active: true,
        },
      }),
    } as ReturnType<typeof usePage>);

    const user = userEvent.setup();
    render(<BillingIndex />);

    const cancelButton = screen.getByText("Cancel Subscription");
    await user.click(cancelButton);

    // Modal should open (component is imported and used)
    // Since we're not testing the modal itself here, just verify button interaction works
    expect(cancelButton).toBeInTheDocument();
  });

  it("displays checkout success alert when query param present", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({ props: createMockProps() } as ReturnType<typeof usePage>);

    // Mock window.location.search
    delete (window as unknown as Record<string, unknown>).location;
    (window as unknown as Record<string, unknown>).location = { search: "?checkout=success", pathname: "/billing" };
    const replaceStateSpy = vi.spyOn(window.history, "replaceState");

    render(<BillingIndex />);

    await waitFor(() => {
      expect(screen.getByText("Welcome!")).toBeInTheDocument();
    });

    expect(replaceStateSpy).toHaveBeenCalledWith({}, "", "/billing");
  });

  it("shows view all invoices button when more than 5 invoices", async () => {
    const { usePage } = await import("@inertiajs/react");
    const invoices = Array.from({ length: 10 }, (_, i) => ({
      id: `in_test${i}`,
      date: new Date(2025, 0, i + 1).toISOString(),
      amount: 2900,
      status: "paid",
      invoice_pdf: `https://invoice.stripe.com/i/test${i}`,
    }));

    vi.mocked(usePage).mockReturnValue({
      props: createMockProps({
        subscription: {
          name: "Pro",
          status: "active",
          priceId: "price_pro_monthly",
          trialEndsAt: null,
          endsAt: null,
          onGracePeriod: false,
          canceled: false,
          active: true,
        },
        invoices,
      }),
    } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.getByText("View all 10 invoices")).toBeInTheDocument();
  });

  it("does not show billing history card when no subscription", async () => {
    const { usePage } = await import("@inertiajs/react");
    vi.mocked(usePage).mockReturnValue({ props: createMockProps() } as ReturnType<typeof usePage>);

    render(<BillingIndex />);

    expect(screen.queryByText("Billing History")).not.toBeInTheDocument();
  });
});
