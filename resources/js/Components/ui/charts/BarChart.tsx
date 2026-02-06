import {
  Bar,
  BarChart as RechartsBarChart,
  CartesianGrid,
  Legend,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";

import { ChartContainer, useChartColors } from "./ChartContainer";

interface BarChartProps {
  data: Record<string, unknown>[];
  xKey: string;
  yKeys: string[];
  height?: number;
  showGrid?: boolean;
  showLegend?: boolean;
  showTooltip?: boolean;
  className?: string;
  "aria-label"?: string;
}

export function BarChart({
  data,
  xKey,
  yKeys,
  height = 300,
  showGrid = true,
  showLegend = false,
  showTooltip = true,
  className,
  "aria-label": ariaLabel,
}: BarChartProps) {
  const colors = useChartColors();

  if (data.length === 0) {
    return (
      <div
        role="status"
        className="flex items-center justify-center text-sm text-muted-foreground"
        style={{ height }}
      >
        No data available
      </div>
    );
  }

  return (
    <ChartContainer height={height} className={className} aria-label={ariaLabel}>
      <RechartsBarChart data={data}>
        {showGrid && <CartesianGrid strokeDasharray="3 3" opacity={0.3} />}
        <XAxis dataKey={xKey} tick={{ fontSize: 12 }} />
        <YAxis tick={{ fontSize: 12 }} />
        {showTooltip && <Tooltip />}
        {showLegend && <Legend />}
        {yKeys.map((key, i) => (
          <Bar
            key={key}
            dataKey={key}
            fill={colors[i % colors.length]}
            radius={[4, 4, 0, 0]}
          />
        ))}
      </RechartsBarChart>
    </ChartContainer>
  );
}
