import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";

import type { AdminDashboardProps } from "@/types/admin";

vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    usePage: vi.fn(() => ({
      url: "/admin",
      props: {
        auth: { user: { name: "Admin", email: "admin@test.com" } },
        features: { billing: false, socialAuth: false, emailVerification: true, apiTokens: true, userSettings: true, notifications: false },
      },
    })),
    Link: ({ children, href, ...rest }: { children: React.ReactNode; href: string; className?: string }) => (
      <a href={href} {...rest}>{children}</a>
    ),
  };
});

vi.mock("@/Components/ui/count-up", () => ({
  CountUp: ({ end, format }: { end: number; format?: (n: number) => string }) => (
    <span>{format ? format(end) : end}</span>
  ),
}));

vi.mock("@/Components/theme/use-theme", () => ({
  useTheme: vi.fn(() => ({ theme: "system", setTheme: vi.fn(), resolvedTheme: "light" })),
}));

// Recharts mocks — prevent canvas-related errors in jsdom
vi.mock("recharts", () => ({
  ResponsiveContainer: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
  AreaChart: ({ children }: { children: React.ReactNode }) => <div data-testid="area-chart">{children}</div>,
  Area: () => null,
  XAxis: () => null,
  YAxis: () => null,
  CartesianGrid: () => null,
  Tooltip: () => null,
  Legend: () => null,
  defs: () => null,
  linearGradient: () => null,
  stop: () => null,
}));

import AdminDashboard from "./Dashboard";

const defaultProps: AdminDashboardProps = {
  stats: {
    total_users: 150,
    new_users_7d: 12,
    new_users_30d: 45,
    admin_count: 3,
  },
  signup_chart: [
    { date: "2025-01-01", count: 5 },
    { date: "2025-01-02", count: 8 },
  ],
  recent_activity: [
    {
      id: 1,
      event: "auth.login",
      user_name: "John Doe",
      user_email: "john@test.com",
      ip: "192.168.1.1",
      created_at: new Date().toISOString(),
    },
  ],
};

describe("AdminDashboard", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("renders stats grid with user counts", () => {
    render(<AdminDashboard {...defaultProps} />);
    expect(screen.getByText("Total Users")).toBeInTheDocument();
    expect(screen.getByText("150")).toBeInTheDocument();
    expect(screen.getByText("New (7d)")).toBeInTheDocument();
    expect(screen.getByText("12")).toBeInTheDocument();
  });

  it("renders admin count stat", () => {
    render(<AdminDashboard {...defaultProps} />);
    expect(screen.getByText("Admins")).toBeInTheDocument();
    expect(screen.getByText("3")).toBeInTheDocument();
  });

  it("renders signup chart section", () => {
    render(<AdminDashboard {...defaultProps} />);
    expect(screen.getByText("User Signups")).toBeInTheDocument();
  });

  it("renders recent activity table", () => {
    render(<AdminDashboard {...defaultProps} />);
    expect(screen.getByText("Recent Activity")).toBeInTheDocument();
    expect(screen.getByText("auth.login")).toBeInTheDocument();
    expect(screen.getByText("John Doe")).toBeInTheDocument();
  });

  it("renders recent activity IP", () => {
    render(<AdminDashboard {...defaultProps} />);
    expect(screen.getByText("192.168.1.1")).toBeInTheDocument();
  });

  it("shows empty state for activity when none exist", () => {
    render(<AdminDashboard {...defaultProps} recent_activity={[]} />);
    expect(screen.getByText("No activity recorded yet")).toBeInTheDocument();
  });

  it("shows active subscriptions stat when billing enabled", () => {
    render(
      <AdminDashboard
        {...defaultProps}
        stats={{ ...defaultProps.stats, active_subscriptions: 25 }}
      />,
    );
    expect(screen.getByText("Active Subscriptions")).toBeInTheDocument();
    expect(screen.getByText("25")).toBeInTheDocument();
  });

  it("does not show subscriptions stat when not in stats", () => {
    render(<AdminDashboard {...defaultProps} />);
    expect(screen.queryByText("Active Subscriptions")).not.toBeInTheDocument();
  });

  it("renders system user as fallback for activity without user", () => {
    const activity = [
      {
        id: 2,
        event: "system.check",
        user_name: null,
        user_email: null,
        ip: "127.0.0.1",
        created_at: new Date().toISOString(),
      },
    ];
    render(<AdminDashboard {...defaultProps} recent_activity={activity} />);
    // "System" appears in both sidebar nav and activity table — verify it appears more than once
    const systemElements = screen.getAllByText("System");
    expect(systemElements.length).toBeGreaterThanOrEqual(2);
  });

  it("links stat cards to filtered admin pages", () => {
    render(<AdminDashboard {...defaultProps} />);
    const links = screen.getAllByRole("link");
    const hrefs = links.map((l) => l.getAttribute("href"));
    expect(hrefs).toContain("/admin/users");
    expect(hrefs).toContain("/admin/users?admin=1");
  });
});
