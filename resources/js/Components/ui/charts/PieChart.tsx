import {
  Cell,
  Legend,
  Pie,
  PieChart as RechartsPieChart,
  Tooltip,
} from "recharts";

import { ChartContainer, useChartColors } from "./ChartContainer";

interface PieChartProps {
  data: { name: string; value: number }[];
  height?: number;
  showLegend?: boolean;
  showTooltip?: boolean;
  innerRadius?: number;
  className?: string;
  "aria-label"?: string;
}

export function PieChart({
  data,
  height = 300,
  showLegend = true,
  showTooltip = true,
  innerRadius = 0,
  className,
  "aria-label": ariaLabel,
}: PieChartProps) {
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
      <RechartsPieChart>
        {showTooltip && <Tooltip />}
        {showLegend && <Legend />}
        <Pie
          data={data}
          dataKey="value"
          nameKey="name"
          cx="50%"
          cy="50%"
          innerRadius={innerRadius}
          outerRadius="80%"
          label={({ name, percent }) =>
            `${name} ${(percent * 100).toFixed(0)}%`
          }
        >
          {data.map((entry, i) => (
            <Cell key={`${entry.name}-${i}`} fill={colors[i % colors.length]} />
          ))}
        </Pie>
      </RechartsPieChart>
    </ChartContainer>
  );
}
