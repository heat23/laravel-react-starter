import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";

import type { AdminFeatureFlagsIndexProps, FeatureFlagAdmin } from "@/types/admin";

vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    usePage: vi.fn(() => ({
      url: "/admin/feature-flags",
      props: {
        auth: { user: { name: "Admin", email: "admin@test.com" } },
        features: {
          billing: false,
          socialAuth: false,
          emailVerification: true,
          apiTokens: true,
          userSettings: true,
          notifications: false,
          onboarding: false,
          apiDocs: false,
          twoFactor: false,
          webhooks: false,
          admin: true,
        },
      },
    })),
    Link: ({ children, href, ...rest }: { children: React.ReactNode; href: string; className?: string }) => (
      <a href={href} {...rest}>{children}</a>
    ),
    router: {
      patch: vi.fn(),
      delete: vi.fn(),
      post: vi.fn(),
    },
  };
});

vi.mock("@/Components/theme/use-theme", () => ({
  useTheme: vi.fn(() => ({ theme: "system", setTheme: vi.fn(), resolvedTheme: "light" })),
}));

vi.mock("sonner", () => ({
  toast: {
    success: vi.fn(),
    error: vi.fn(),
  },
}));

import FeatureFlagsIndex from "./Index";

const createFlag = (overrides: Partial<FeatureFlagAdmin> = {}): FeatureFlagAdmin => ({
  flag: "billing",
  env_default: false,
  global_override: null,
  effective: false,
  user_override_count: 0,
  is_protected: false,
  is_route_dependent: true,
  ...overrides,
});

const defaultProps: AdminFeatureFlagsIndexProps = {
  flags: [
    createFlag({ flag: "billing", env_default: false, effective: false }),
    createFlag({ flag: "email_verification", env_default: true, effective: true }),
    createFlag({ flag: "admin", env_default: false, is_protected: true }),
    createFlag({ flag: "notifications", env_default: false, global_override: true, effective: false, is_route_dependent: true }),
  ],
};

describe("FeatureFlagsIndex", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("renders all feature flags in table", () => {
    render(<FeatureFlagsIndex {...defaultProps} />);

    expect(screen.getByText("billing")).toBeInTheDocument();
    expect(screen.getByText("email_verification")).toBeInTheDocument();
    expect(screen.getByText("admin")).toBeInTheDocument();
    expect(screen.getByText("notifications")).toBeInTheDocument();
  });

  it("shows correct effective state badges", () => {
    render(<FeatureFlagsIndex {...defaultProps} />);

    // Find the table rows and check badges
    const offBadges = screen.getAllByText("OFF");
    const onBadges = screen.getAllByText("ON");

    // We should have multiple OFF and ON badges
    expect(offBadges.length).toBeGreaterThan(0);
    expect(onBadges.length).toBeGreaterThan(0);
  });

  it("shows protected badge for admin flag", () => {
    render(<FeatureFlagsIndex {...defaultProps} />);

    expect(screen.getByText("Protected")).toBeInTheDocument();
  });

  it("disables switch for protected flags", () => {
    render(<FeatureFlagsIndex {...defaultProps} />);

    // Find all switches and check that one is disabled
    const switches = screen.getAllByRole("switch");

    // The admin flag switch should be disabled
    const disabledSwitch = switches.find((s) => s.getAttribute("disabled") !== null);
    expect(disabledSwitch).toBeDefined();
  });

  it("shows user override count", () => {
    const propsWithOverrides = {
      flags: [
        createFlag({ flag: "billing", user_override_count: 5 }),
      ],
    };

    render(<FeatureFlagsIndex {...propsWithOverrides} />);

    expect(screen.getByText("5 users")).toBeInTheDocument();
  });

  it("shows route-dependent warning when env=off but override=on", () => {
    render(<FeatureFlagsIndex {...defaultProps} />);

    // The notifications flag has env_default=false and global_override=true
    expect(screen.getByText("Route unavailable")).toBeInTheDocument();
  });
});
