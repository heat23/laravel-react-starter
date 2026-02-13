import type { LucideIcon } from "lucide-react";

import { Link } from "@inertiajs/react";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { CountUp } from "@/Components/ui/count-up";

export interface StatCard {
  title: string;
  value: number;
  icon?: LucideIcon;
  description?: string;
  /** Custom number formatter — defaults to toLocaleString() */
  format?: (n: number) => string;
  /** Additional CSS class on the value text */
  valueClassName?: string;
  /** Optional link — makes the entire card clickable and navigable. */
  href?: string;
}

interface AdminStatsGridProps {
  stats: StatCard[];
  /** Tailwind grid columns class — defaults to "grid-cols-1 md:grid-cols-2 lg:grid-cols-4" */
  columns?: string;
}

export function AdminStatsGrid({ stats, columns = "grid-cols-1 md:grid-cols-2 lg:grid-cols-4" }: AdminStatsGridProps) {
  return (
    <div className={`grid gap-4 ${columns}`}>
      {stats.map((stat) => {
        const card = (
          <Card key={stat.title} className={stat.href ? "transition-colors hover:border-primary/50" : undefined}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">{stat.title}</CardTitle>
              {stat.icon && <stat.icon className="h-4 w-4 text-muted-foreground" />}
            </CardHeader>
            <CardContent>
              <div className={`text-2xl font-bold ${stat.valueClassName ?? ""}`}>
                <CountUp end={stat.value} format={stat.format} />
              </div>
              {stat.description && (
                <p className="text-xs text-muted-foreground">{stat.description}</p>
              )}
            </CardContent>
          </Card>
        );

        return stat.href ? (
          <Link key={stat.title} href={stat.href} className="no-underline">
            {card}
          </Link>
        ) : (
          card
        );
      })}
    </div>
  );
}
