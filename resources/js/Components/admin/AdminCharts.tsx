import type { LucideIcon } from "lucide-react";

import { ReactNode } from "react";

import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  CHART_COLORS,
  ChartContainer,
  ChartTooltip,
  Legend,
  Pie,
  PieChart,
  XAxis,
  YAxis,
} from "@/Components/ui/chart";
import { EmptyState } from "@/Components/ui/empty-state";

const defaultDateFormatter = (v: string) =>
  new Date(v).toLocaleDateString(undefined, { month: "short", day: "numeric" });

// ---------------------------------------------------------------------------
// AdminAreaChart
// ---------------------------------------------------------------------------

interface AdminAreaChartProps<T> {
  data: T[];
  dataKey: string;
  name: string;
  height?: number;
  emptyIcon?: LucideIcon;
  emptyTitle?: string;
  emptyDescription?: string;
  gradientId?: string;
  xAxisKey?: string;
  xAxisFormatter?: (v: string) => string;
}

export function AdminAreaChart<T>({
  data,
  dataKey,
  name,
  height = 300,
  emptyIcon,
  emptyTitle = "No data yet",
  emptyDescription = "Data will appear here once available.",
  gradientId = "adminAreaGradient",
  xAxisKey = "date",
  xAxisFormatter = defaultDateFormatter,
}: AdminAreaChartProps<T>) {
  if (data.length === 0) {
    return (
      <EmptyState
        icon={emptyIcon}
        title={emptyTitle}
        description={emptyDescription}
        size="sm"
      />
    );
  }

  return (
    <ChartContainer height={height}>
      <AreaChart data={data}>
        <defs>
          <linearGradient id={gradientId} x1="0" y1="0" x2="0" y2="1">
            <stop offset="5%" stopColor="hsl(var(--primary))" stopOpacity={0.3} />
            <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0} />
          </linearGradient>
        </defs>
        <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
        <XAxis
          dataKey={xAxisKey}
          tickFormatter={xAxisFormatter}
          className="text-xs"
        />
        <YAxis allowDecimals={false} className="text-xs" />
        <ChartTooltip />
        <Area
          type="monotone"
          dataKey={dataKey}
          name={name}
          stroke="hsl(var(--primary))"
          fill={`url(#${gradientId})`}
          strokeWidth={2}
          animationDuration={800}
          animationBegin={200}
        />
      </AreaChart>
    </ChartContainer>
  );
}

// ---------------------------------------------------------------------------
// AdminBarChart
// ---------------------------------------------------------------------------

interface AdminBarChartProps<T> {
  data: T[];
  dataKey: string;
  xAxisKey: string;
  name: string;
  height?: number;
  emptyIcon?: LucideIcon;
  emptyTitle?: string;
  emptyDescription?: string;
  fillColor?: string;
  children?: ReactNode;
}

export function AdminBarChart<T>({
  data,
  dataKey,
  xAxisKey,
  name,
  height = 250,
  emptyIcon,
  emptyTitle = "No data yet",
  emptyDescription = "Data will appear here once available.",
  fillColor = "hsl(var(--primary))",
  children,
}: AdminBarChartProps<T>) {
  if (data.length === 0) {
    return (
      <EmptyState
        icon={emptyIcon}
        title={emptyTitle}
        description={emptyDescription}
        size="sm"
      />
    );
  }

  return (
    <ChartContainer height={height}>
      <BarChart data={data}>
        <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
        <XAxis dataKey={xAxisKey} className="text-xs" />
        <YAxis allowDecimals={false} className="text-xs" />
        <ChartTooltip />
        {children ?? (
          <Bar
            dataKey={dataKey}
            name={name}
            fill={fillColor}
            radius={[4, 4, 0, 0]}
            animationDuration={600}
          />
        )}
      </BarChart>
    </ChartContainer>
  );
}

// ---------------------------------------------------------------------------
// AdminPieChart
// ---------------------------------------------------------------------------

interface AdminPieChartProps<T> {
  data: T[];
  dataKey: string;
  nameKey: string;
  height?: number;
  emptyIcon?: LucideIcon;
  emptyTitle?: string;
  emptyDescription?: string;
}

export function AdminPieChart<T>({
  data,
  dataKey,
  nameKey,
  height = 250,
  emptyIcon,
  emptyTitle = "No data yet",
  emptyDescription = "Data will appear here once available.",
}: AdminPieChartProps<T>) {
  if (data.length === 0) {
    return (
      <EmptyState
        icon={emptyIcon}
        title={emptyTitle}
        description={emptyDescription}
        size="sm"
      />
    );
  }

  return (
    <ChartContainer height={height}>
      <PieChart>
        <Pie
          data={data}
          dataKey={dataKey}
          nameKey={nameKey}
          cx="50%"
          cy="50%"
          outerRadius={80}
          label={({ name, value }) => `${name}: ${value}`}
          animationDuration={800}
          animationBegin={100}
        >
          {data.map((_entry, index) => (
            <Cell key={`cell-${index}`} fill={CHART_COLORS[index % CHART_COLORS.length]} />
          ))}
        </Pie>
        <ChartTooltip />
        <Legend />
      </PieChart>
    </ChartContainer>
  );
}
