import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Legend,
  Line,
  LineChart,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  type TooltipProps,
  XAxis,
  YAxis,
} from "recharts";

import { ReactNode } from "react";

interface ChartContainerProps {
  height?: number;
  children: ReactNode;
}

export function ChartContainer({ height = 300, children }: ChartContainerProps) {
  return (
    <ResponsiveContainer width="100%" height={height}>
      {children as React.ReactElement}
    </ResponsiveContainer>
  );
}

function ChartTooltipContent({ active, payload, label }: TooltipProps<number, string>) {
  if (!active || !payload?.length) return null;

  return (
    <div className="rounded-lg border bg-popover p-3 text-popover-foreground shadow-md">
      <p className="mb-1 text-xs font-medium text-muted-foreground">{label}</p>
      {payload.map((entry, index) => (
        <p key={index} className="text-sm font-semibold">
          {entry.name}: {entry.value}
        </p>
      ))}
    </div>
  );
}

export function ChartTooltip(props: TooltipProps<number, string>) {
  return <Tooltip content={<ChartTooltipContent {...props} />} {...props} />;
}

export const CHART_COLORS = [
  "hsl(var(--primary))",
  "hsl(var(--chart-2, 173 58% 39%))",
  "hsl(var(--chart-3, 197 37% 24%))",
  "hsl(var(--chart-4, 43 74% 66%))",
  "hsl(var(--chart-5, 27 87% 67%))",
] as const;

export { Area, AreaChart, Bar, BarChart, CartesianGrid, Cell, Legend, Line, LineChart, Pie, PieChart, XAxis, YAxis };
