import { TrendingUp } from "lucide-react";

import { EmptyState } from "@/Components/ui/empty-state";

export interface FunnelStage {
  stage: string;
  label: string;
  count: number;
}

interface AdminFunnelChartProps {
  stages: FunnelStage[];
}

/** Ordered list of stages for the primary funnel (visitor → paying) */
const PRIMARY_FUNNEL = ["visitor", "trial", "activated", "paying"];

export function AdminFunnelChart({ stages }: AdminFunnelChartProps) {
  if (!stages || stages.length === 0) {
    return (
      <EmptyState
        icon={TrendingUp}
        title="No lifecycle data yet"
        size="sm"
        description="Lifecycle stage data will appear here once users are transitioned through stages."
      />
    );
  }

  const stageMap = Object.fromEntries(stages.map((s) => [s.stage, s]));

  // Build the primary conversion funnel
  const funnelStages = PRIMARY_FUNNEL.map((key) => stageMap[key]).filter(Boolean) as FunnelStage[];

  const maxCount = Math.max(...stages.map((s) => s.count), 1);

  /** Compute conversion rate between two adjacent funnel stages */
  const conversionRate = (from: FunnelStage, to: FunnelStage): string => {
    if (from.count === 0) return "—";
    return ((to.count / from.count) * 100).toFixed(1) + "%";
  };

  // Sidebar stages (at_risk, churned, expansion)
  const sidebarStages = stages.filter((s) => !PRIMARY_FUNNEL.includes(s.stage));

  return (
    <div className="space-y-6">
      {/* Primary funnel */}
      <div className="space-y-2">
        <p className="text-sm font-medium text-muted-foreground mb-3">Conversion Funnel</p>
        {funnelStages.map((stage, idx) => {
          const barWidth = maxCount > 0 ? (stage.count / maxCount) * 100 : 0;
          const prev = funnelStages[idx - 1];

          return (
            <div key={stage.stage} className="space-y-1">
              {idx > 0 && prev && (
                <div className="text-xs text-muted-foreground text-center py-0.5">
                  ↓ {conversionRate(prev, stage)} conversion
                </div>
              )}
              <div className="flex items-center gap-3">
                <span className="w-20 shrink-0 text-sm font-medium text-right text-foreground">
                  {stage.label}
                </span>
                <div className="flex-1 h-7 bg-muted rounded-sm overflow-hidden">
                  <div
                    className="h-full bg-primary/70 rounded-sm transition-all"
                    style={{ width: `${barWidth}%` }}
                    role="meter"
                    aria-valuenow={stage.count}
                    aria-valuemin={0}
                    aria-valuemax={maxCount}
                    aria-label={`${stage.label}: ${stage.count} users`}
                  />
                </div>
                <span className="w-12 shrink-0 text-sm tabular-nums text-muted-foreground">
                  {stage.count.toLocaleString()}
                </span>
              </div>
            </div>
          );
        })}
      </div>

      {/* Sidebar stages */}
      {sidebarStages.length > 0 && (
        <div className="border-t pt-4">
          <p className="text-sm font-medium text-muted-foreground mb-3">Other Stages</p>
          <div className="grid grid-cols-3 gap-3">
            {sidebarStages.map((stage) => (
              <div key={stage.stage} className="text-center p-3 rounded-md bg-muted/50">
                <p className="text-lg font-bold tabular-nums">{stage.count.toLocaleString()}</p>
                <p className="text-xs text-muted-foreground mt-0.5">{stage.label}</p>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
