import { ResponsiveContainer } from "recharts";

import { useEffect, useState } from "react";

import { useTheme } from "@/Components/theme";
import { useIsMobile } from "@/hooks/use-mobile";
import { cn } from "@/lib/utils";

const DEFAULT_COLORS = [
  "hsl(220 90% 45%)",
  "hsl(160 60% 45%)",
  "hsl(30 80% 55%)",
  "hsl(280 65% 60%)",
  "hsl(10 80% 55%)",
];

export function useChartColors(): string[] {
  const { resolvedTheme } = useTheme();
  const [colors, setColors] = useState<string[]>(DEFAULT_COLORS);

  useEffect(() => {
    if (typeof window === "undefined") return;

    const root = document.documentElement;
    const style = getComputedStyle(root);
    setColors(
      [1, 2, 3, 4, 5].map((i) => {
        const hsl = style.getPropertyValue(`--chart-${i}`).trim();
        return hsl ? `hsl(${hsl})` : DEFAULT_COLORS[i - 1];
      }),
    );
  }, [resolvedTheme]);

  return colors;
}

interface ChartContainerProps {
  height?: number;
  className?: string;
  "aria-label"?: string;
  children: React.ReactNode;
}

export function ChartContainer({
  height,
  className,
  "aria-label": ariaLabel,
  children,
}: ChartContainerProps) {
  const isMobile = useIsMobile();
  const resolvedHeight = height ?? (isMobile ? 250 : 300);

  return (
    <div role="img" aria-label={ariaLabel} className={cn("w-full", className)}>
      <ResponsiveContainer width="100%" height={resolvedHeight}>
        {children as React.ReactElement}
      </ResponsiveContainer>
    </div>
  );
}
