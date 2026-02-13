import { render, screen } from "@testing-library/react";
import { Users, TrendingUp } from "lucide-react";
import { describe, it, expect, vi } from "vitest";

import { AdminStatsGrid, type StatCard } from "./AdminStatsGrid";

vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    Link: ({ children, href, ...rest }: { children: React.ReactNode; href: string; className?: string }) => (
      <a href={href} {...rest}>
        {children}
      </a>
    ),
  };
});

vi.mock("@/Components/ui/count-up", () => ({
  CountUp: ({ end, format }: { end: number; format?: (n: number) => string }) => (
    <span>{format ? format(end) : end}</span>
  ),
}));

describe("AdminStatsGrid", () => {
  const stats: StatCard[] = [
    { title: "Total Users", value: 150, icon: Users, description: "All users" },
    { title: "New (7d)", value: 12, icon: TrendingUp, description: "This week" },
  ];

  it("renders all stat cards", () => {
    render(<AdminStatsGrid stats={stats} />);
    expect(screen.getByText("Total Users")).toBeInTheDocument();
    expect(screen.getByText("New (7d)")).toBeInTheDocument();
  });

  it("renders stat values", () => {
    render(<AdminStatsGrid stats={stats} />);
    expect(screen.getByText("150")).toBeInTheDocument();
    expect(screen.getByText("12")).toBeInTheDocument();
  });

  it("renders descriptions", () => {
    render(<AdminStatsGrid stats={stats} />);
    expect(screen.getByText("All users")).toBeInTheDocument();
    expect(screen.getByText("This week")).toBeInTheDocument();
  });

  it("uses custom format function", () => {
    const formatted: StatCard[] = [
      { title: "MRR", value: 2999, format: (n) => `$${(n / 100).toFixed(2)}` },
    ];
    render(<AdminStatsGrid stats={formatted} />);
    expect(screen.getByText("$29.99")).toBeInTheDocument();
  });

  it("renders as link when href is provided", () => {
    const linked: StatCard[] = [
      { title: "Users", value: 50, href: "/admin/users" },
    ];
    render(<AdminStatsGrid stats={linked} />);
    const link = screen.getByRole("link");
    expect(link).toHaveAttribute("href", "/admin/users");
  });

  it("does not render link when href is not provided", () => {
    const noLink: StatCard[] = [
      { title: "Count", value: 10 },
    ];
    render(<AdminStatsGrid stats={noLink} />);
    expect(screen.queryByRole("link")).not.toBeInTheDocument();
  });

  it("uses custom columns class", () => {
    const { container } = render(
      <AdminStatsGrid stats={stats} columns="grid-cols-2" />,
    );
    const grid = container.firstElementChild;
    expect(grid?.className).toContain("grid-cols-2");
  });

  it("uses default columns when not specified", () => {
    const { container } = render(<AdminStatsGrid stats={stats} />);
    const grid = container.firstElementChild;
    expect(grid?.className).toContain("lg:grid-cols-4");
  });

  it("renders empty grid when no stats", () => {
    const { container } = render(<AdminStatsGrid stats={[]} />);
    const grid = container.firstElementChild;
    expect(grid?.children.length).toBe(0);
  });
});
