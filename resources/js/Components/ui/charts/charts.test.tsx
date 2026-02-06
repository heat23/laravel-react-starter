import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

// Mock recharts to avoid canvas/SVG issues in jsdom
vi.mock("recharts", () => ({
  ResponsiveContainer: ({
    children,
  }: {
    children: React.ReactNode;
  }) => <div data-testid="responsive-container">{children}</div>,
  AreaChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="area-chart">{children}</div>
  ),
  BarChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="bar-chart">{children}</div>
  ),
  LineChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="line-chart">{children}</div>
  ),
  PieChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="pie-chart">{children}</div>
  ),
  Area: () => <div data-testid="area" />,
  Bar: () => <div data-testid="bar" />,
  Line: () => <div data-testid="line" />,
  Pie: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="pie">{children}</div>
  ),
  Cell: () => <div data-testid="cell" />,
  CartesianGrid: () => null,
  XAxis: () => null,
  YAxis: () => null,
  Tooltip: () => null,
  Legend: () => null,
}));

vi.mock("@/Components/theme", () => ({
  useTheme: () => ({ resolvedTheme: "light" }),
}));

import { AreaChart } from "./AreaChart";
import { BarChart } from "./BarChart";
import { LineChart } from "./LineChart";
import { PieChart } from "./PieChart";

const sampleData = [
  { month: "Jan", value: 100, other: 50 },
  { month: "Feb", value: 200, other: 80 },
  { month: "Mar", value: 150, other: 60 },
];

const pieData = [
  { name: "A", value: 100 },
  { name: "B", value: 200 },
  { name: "C", value: 300 },
];

describe("Chart components", () => {
  it("renders area chart with data", () => {
    render(<AreaChart data={sampleData} xKey="month" yKeys={["value"]} />);
    expect(screen.getByTestId("area-chart")).toBeInTheDocument();
  });

  it("renders bar chart with data", () => {
    render(<BarChart data={sampleData} xKey="month" yKeys={["value"]} />);
    expect(screen.getByTestId("bar-chart")).toBeInTheDocument();
  });

  it("renders line chart with data", () => {
    render(<LineChart data={sampleData} xKey="month" yKeys={["value"]} />);
    expect(screen.getByTestId("line-chart")).toBeInTheDocument();
  });

  it("renders pie chart with data", () => {
    render(<PieChart data={pieData} />);
    expect(screen.getByTestId("pie-chart")).toBeInTheDocument();
  });

  it("handles empty data gracefully for area chart", () => {
    render(<AreaChart data={[]} xKey="month" yKeys={["value"]} />);
    expect(screen.getByText("No data available")).toBeInTheDocument();
  });

  it("handles empty data gracefully for bar chart", () => {
    render(<BarChart data={[]} xKey="month" yKeys={["value"]} />);
    expect(screen.getByText("No data available")).toBeInTheDocument();
  });

  it("handles empty data gracefully for line chart", () => {
    render(<LineChart data={[]} xKey="month" yKeys={["value"]} />);
    expect(screen.getByText("No data available")).toBeInTheDocument();
  });

  it("handles empty data gracefully for pie chart", () => {
    render(<PieChart data={[]} />);
    expect(screen.getByText("No data available")).toBeInTheDocument();
  });
});
