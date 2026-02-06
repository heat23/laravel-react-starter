import {
  Area,
  AreaChart as RechartsAreaChart,
  CartesianGrid,
  Legend,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";

import { ChartContainer, useChartColors } from "./ChartContainer";

interface AreaChartProps {
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

export function AreaChart({
  data,
  xKey,
  yKeys,
  height = 300,
  showGrid = true,
  showLegend = false,
  showTooltip = true,
  className,
  "aria-label": ariaLabel,
}: AreaChartProps) {
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
      <RechartsAreaChart data={data}>
        {showGrid && <CartesianGrid strokeDasharray="3 3" opacity={0.3} />}
        <XAxis dataKey={xKey} tick={{ fontSize: 12 }} />
        <YAxis tick={{ fontSize: 12 }} />
        {showTooltip && <Tooltip />}
        {showLegend && <Legend />}
        {yKeys.map((key, i) => (
          <Area
            key={key}
            type="monotone"
            dataKey={key}
            fill={colors[i % colors.length]}
            stroke={colors[i % colors.length]}
            fillOpacity={0.2}
          />
        ))}
      </RechartsAreaChart>
    </ChartContainer>
  );
}
