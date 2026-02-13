/**
 * Admin UI Smoke Tests
 *
 * Basic rendering tests for all admin pages to ensure they don't crash.
 * These are intentionally lightweight - full interaction testing is done in dedicated test files.
 */

import { render, screen } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock window.location before imports (needed by useAdminFilters)
beforeEach(() => {
  delete (window as any).location;
  (window as any).location = { search: "", pathname: "/admin" };
});

// Mock Inertia
vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    usePage: vi.fn(() => ({
      url: "/admin",
      props: {
        auth: { user: { id: 1, name: "Admin", email: "admin@test.com", is_admin: true } },
        features: {
          billing: true,
          socialAuth: true,
          emailVerification: true,
          apiTokens: true,
          userSettings: true,
          notifications: true,
          onboarding: false,
          apiDocs: false,
          twoFactor: true,
          webhooks: true,
          admin: true,
        },
      },
    })),
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
    router: {
      visit: vi.fn(),
      get: vi.fn(),
      post: vi.fn(),
      patch: vi.fn(),
      delete: vi.fn(),
      on: vi.fn(() => vi.fn()), // Returns cleanup function
    },
  };
});

// Mock useTheme
vi.mock("@/Components/theme/use-theme", () => ({
  useTheme: vi.fn(() => ({ theme: "system", setTheme: vi.fn(), resolvedTheme: "light" })),
}));

// Mock CountUp
vi.mock("@/Components/ui/count-up", () => ({
  CountUp: ({ end }: { end: number }) => <span>{end}</span>,
}));

// Mock Recharts
vi.mock("recharts", () => ({
  ResponsiveContainer: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
  AreaChart: ({ children }: { children: React.ReactNode }) => <div data-testid="area-chart">{children}</div>,
  BarChart: ({ children }: { children: React.ReactNode }) => <div data-testid="bar-chart">{children}</div>,
  PieChart: ({ children }: { children: React.ReactNode }) => <div data-testid="pie-chart">{children}</div>,
  LineChart: ({ children }: { children: React.ReactNode }) => <div data-testid="line-chart">{children}</div>,
  Area: () => null,
  Bar: () => null,
  Pie: () => null,
  Line: () => null,
  Cell: () => null,
  XAxis: () => null,
  YAxis: () => null,
  CartesianGrid: () => null,
  Tooltip: () => null,
  Legend: () => null,
}));

describe("Admin UI Smoke Tests", () => {
  describe("Users Pages", () => {
    it("renders Users Index page", async () => {
      const UsersIndex = (await import("./Users/Index")).default;

      render(
        <UsersIndex
          users={{
            data: [
              {
                id: 1,
                name: "Test User",
                email: "test@example.com",
                email_verified_at: "2026-01-01",
                is_admin: false,
                created_at: "2026-01-01",
                deleted_at: null,
                last_login_at: null,
                tokens_count: 0,
              },
            ],
            current_page: 1,
            per_page: 15,
            total: 1,
            last_page: 1,
            from: 1,
            to: 1,
          }}
          filters={{}}
        />
      );

      expect(screen.getByRole("heading", { name: "Users", level: 1 })).toBeInTheDocument();
    });

    it("renders Users Show page", async () => {
      const UsersShow = (await import("./Users/Show")).default;

      render(
        <UsersShow
          user={{
            id: 1,
            name: "Test User",
            email: "test@example.com",
            email_verified_at: "2026-01-01",
            is_admin: false,
            created_at: "2026-01-01",
            deleted_at: null,
            last_login_at: null,
            has_password: true,
            signup_source: "direct",
            tokens_count: 0,
          }}
          recent_audit_logs={[]}
          subscription={null}
        />
      );

      expect(screen.getByRole("heading", { name: "Test User", level: 1 })).toBeInTheDocument();
    });
  });

  describe("Audit Logs Pages", () => {
    it("renders Audit Logs Index page", async () => {
      const AuditLogsIndex = (await import("./AuditLogs/Index")).default;

      render(
        <AuditLogsIndex
          logs={{
            data: [],
            current_page: 1,
            per_page: 50,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          eventTypes={["auth.login", "auth.logout"]}
          filters={{}}
        />
      );

      expect(screen.getByRole("heading", { name: "Audit Logs", level: 1 })).toBeInTheDocument();
    });

    it("renders Audit Logs Show page", async () => {
      const AuditLogsShow = (await import("./AuditLogs/Show")).default;

      render(
        <AuditLogsShow
          auditLog={{
            id: 1,
            event: "auth.login",
            user_id: 1,
            user_name: "Test User",
            user_email: "test@example.com",
            ip: "192.168.1.1",
            user_agent: "Mozilla/5.0",
            data: {},
            created_at: "2026-02-13T10:00:00Z",
          }}
        />
      );

      expect(screen.getByText("auth.login")).toBeInTheDocument();
    });
  });

  describe("Billing Pages", () => {
    it("renders Billing Dashboard page", async () => {
      const BillingDashboard = (await import("./Billing/Dashboard")).default;

      render(
        <BillingDashboard
          stats={{
            total_subscriptions: 10,
            active_subscriptions: 8,
            canceled_subscriptions: 2,
            mrr: 8000,
            past_due_count: 1,
            total_ever: 10,
          }}
          tier_distribution={[]}
          status_breakdown={[]}
          growth_chart={[]}
          trial_stats={{ expiring_soon: 0, total_trials: 0 }}
          recent_events={[]}
        />
      );

      expect(screen.getByText("Billing Overview")).toBeInTheDocument();
    });

    it("renders Billing Subscriptions page", async () => {
      const BillingSubscriptions = (await import("./Billing/Subscriptions")).default;

      render(
        <BillingSubscriptions
          subscriptions={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          filters={{}}
          statuses={["active", "canceled"]}
          tiers={["free", "pro", "team"]}
        />
      );

      expect(screen.getByRole("heading", { name: "Subscriptions", level: 1 })).toBeInTheDocument();
    });

    it("renders Billing Show page", async () => {
      const BillingShow = (await import("./Billing/Show")).default;

      render(
        <BillingShow
          subscription={{
            id: 1,
            user_id: 1,
            user_name: "Test User",
            user_email: "test@example.com",
            stripe_id: "sub_test123",
            stripe_status: "active",
            tier: "pro",
            quantity: 1,
            trial_ends_at: null,
            ends_at: null,
            created_at: "2026-01-01",
          }}
          items={[]}
          audit_logs={[]}
        />
      );

      expect(screen.getByText("sub_test123")).toBeInTheDocument();
    });
  });

  describe("Config & System Pages", () => {
    it("renders Config page", async () => {
      const Config = (await import("./Config")).default;

      render(
        <Config
          feature_flags={[
            { key: "billing", enabled: true, env_var: "FEATURE_BILLING" },
            { key: "admin", enabled: true, env_var: "FEATURE_ADMIN" },
          ]}
          warnings={[]}
          environment={{ APP_NAME: "Test App", APP_ENV: "testing" }}
        />
      );

      expect(screen.getByRole("heading", { name: "Configuration", level: 1 })).toBeInTheDocument();
    });

    it("renders System page", async () => {
      const System = (await import("./System")).default;

      render(
        <System
          system={{
            php_version: "8.3.0",
            laravel_version: "12.0.0",
            node_version: "20.0.0",
            server: {
              os: "Linux",
              server_software: "nginx",
            },
            database: {
              driver: "mysql",
              version: "8.0",
            },
            queue: {
              driver: "redis",
              pending_jobs: 0,
              failed_jobs: 0,
            },
            packages: [],
          }}
        />
      );

      expect(screen.getByRole("heading", { name: "System Info", level: 1 })).toBeInTheDocument();
    });

    it("renders Health page", async () => {
      const Health = (await import("./Health")).default;

      render(
        <Health
          health={{
            status: "healthy",
            timestamp: "2026-02-13T10:00:00Z",
            checks: {
              database: { status: "healthy", message: "Connection successful", response_time_ms: 5 },
              cache: { status: "healthy", message: "Redis responding", response_time_ms: 2 },
            },
          }}
        />
      );

      expect(screen.getByRole("heading", { name: "Health Status", level: 1 })).toBeInTheDocument();
    });
  });

  describe("Feature Dashboards", () => {
    it("renders Notifications Dashboard", async () => {
      const NotificationsDashboard = (await import("./Notifications/Dashboard")).default;

      render(
        <NotificationsDashboard
          stats={{
            total_sent: 100,
            unread: 25,
            read: 75,
            read_rate: 75,
            sent_last_7d: 50,
            by_type: [{ type: "info", count: 50 }, { type: "success", count: 50 }],
          }}
          volume_chart={[]}
        />
      );

      expect(screen.getByRole("heading", { name: "Notifications", level: 1 })).toBeInTheDocument();
    });

    it("renders Social Auth Dashboard", async () => {
      const SocialAuthDashboard = (await import("./SocialAuth/Dashboard")).default;

      render(
        <SocialAuthDashboard
          stats={{
            total_connections: 50,
            google_connections: 30,
            github_connections: 20,
            by_provider: { google: 30, github: 20 },
          }}
        />
      );

      expect(screen.getByText("Social Authentication")).toBeInTheDocument();
    });

    it("renders Tokens Dashboard", async () => {
      const TokensDashboard = (await import("./Tokens/Dashboard")).default;

      render(
        <TokensDashboard
          stats={{
            total_tokens: 25,
            active_tokens: 20,
            revoked_tokens: 5,
          }}
          most_active={[]}
        />
      );

      expect(screen.getByRole("heading", { name: "API Tokens", level: 1 })).toBeInTheDocument();
    });

    it("renders TwoFactor Dashboard", async () => {
      const TwoFactorDashboard = (await import("./TwoFactor/Dashboard")).default;

      render(
        <TwoFactorDashboard
          stats={{
            total_users_with_2fa: 40,
            percentage_with_2fa: 40,
            total_users: 100,
          }}
        />
      );

      expect(screen.getByText("Two-Factor Authentication")).toBeInTheDocument();
    });

    it("renders Webhooks Dashboard", async () => {
      const WebhooksDashboard = (await import("./Webhooks/Dashboard")).default;

      render(
        <WebhooksDashboard
          stats={{
            total_endpoints: 15,
            active_endpoints: 12,
            inactive_endpoints: 3,
            total_deliveries: 1000,
            successful_deliveries: 950,
            failed_deliveries: 50,
            incoming_by_provider: { github: 100, stripe: 200 },
          }}
          delivery_chart={[]}
          recent_failures={[]}
        />
      );

      expect(screen.getByText("Webhooks Overview")).toBeInTheDocument();
    });
  });
});
