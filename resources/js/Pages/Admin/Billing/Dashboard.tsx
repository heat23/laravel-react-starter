import { Activity, CreditCard, DollarSign, TrendingDown, TrendingUp, Users } from "lucide-react";

import { Head, Link } from "@inertiajs/react";

import { AdminStatsGrid, type StatCard } from "@/Components/admin/AdminStatsGrid";
import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
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
import { CountUp } from "@/Components/ui/count-up";
import { EmptyState } from "@/Components/ui/empty-state";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { SUBSCRIPTION_STATUS_COLORS } from "@/config/billing-constants";
import AdminLayout from "@/Layouts/AdminLayout";
import { formatCurrency, formatRelativeTime } from "@/lib/format";
import type { AdminBillingDashboardProps } from "@/types/admin";

export default function BillingDashboard({
  stats,
  tier_distribution,
  status_breakdown,
  growth_chart,
  trial_stats,
  recent_events,
}: AdminBillingDashboardProps) {
  const hasSubscriptions = stats.total_ever > 0;

  const primaryKpis: StatCard[] = [
    { title: "MRR", value: stats.mrr, icon: DollarSign, format: formatCurrency, description: "Monthly recurring revenue" },
    { title: "Active Subscriptions", value: stats.active_subscriptions, icon: CreditCard, description: "Paying customers", href: "/admin/billing/subscriptions?status=active" },
    { title: "Churn Rate (30d)", value: stats.churn_rate, icon: TrendingDown, format: (n) => `${n}%`, description: "Cancellations vs active" },
    { title: "Trial Conversion", value: stats.trial_conversion_rate, icon: TrendingUp, format: (n) => `${n}%`, description: "Trial to paid", href: "/admin/billing/subscriptions?status=trialing" },
  ];

  return (
    <AdminLayout>
      <Head title="Admin - Billing" />
      <PageHeader
        title="Billing Overview"
        subtitle="Revenue metrics and subscription analytics"
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link href="/admin/billing/subscriptions">View All Subscriptions</Link>
          </Button>
        }
      />

      <div className="container py-8 space-y-8">
        {/* Primary KPIs */}
        <AdminStatsGrid stats={primaryKpis} />

        {/* Secondary stats */}
        <div className="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Trialing</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-xl font-bold"><CountUp end={stats.trialing} /></div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Past Due</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-xl font-bold text-amber-600 dark:text-amber-400"><CountUp end={stats.past_due} /></div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Canceled</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-xl font-bold"><CountUp end={stats.canceled} /></div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Trials Expiring Soon</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-xl font-bold text-amber-600 dark:text-amber-400"><CountUp end={trial_stats.expiring_soon} /></div>
              <p className="text-xs text-muted-foreground">Within 3 days</p>
            </CardContent>
          </Card>
        </div>

        {!hasSubscriptions ? (
          <EmptyState
            icon={CreditCard}
            title="No subscriptions yet"
            description="Subscription data will appear here once users start subscribing."
          />
        ) : (
          <>
            {/* Charts row */}
            <div className="grid gap-6 md:grid-cols-2">
              {/* Tier Distribution */}
              <Card>
                <CardHeader>
                  <CardTitle>Subscription by Tier</CardTitle>
                  <CardDescription>Active subscription distribution</CardDescription>
                </CardHeader>
                <CardContent>
                  {tier_distribution.length === 0 ? (
                    <EmptyState icon={Users} title="No active subscriptions" description="Tier data will appear when subscriptions are active." size="sm" />
                  ) : (
                    <ChartContainer height={250}>
                      <PieChart>
                        <Pie data={tier_distribution} dataKey="count" nameKey="tier" cx="50%" cy="50%" outerRadius={80} label={({ name, value }) => `${name}: ${value}`} animationDuration={800} animationBegin={100}>
                          {tier_distribution.map((_entry, index) => (
                            <Cell key={`cell-${index}`} fill={CHART_COLORS[index % CHART_COLORS.length]} />
                          ))}
                        </Pie>
                        <ChartTooltip />
                        <Legend />
                      </PieChart>
                    </ChartContainer>
                  )}
                </CardContent>
              </Card>

              {/* Status Breakdown */}
              <Card>
                <CardHeader>
                  <CardTitle>Status Breakdown</CardTitle>
                  <CardDescription>All subscriptions by status</CardDescription>
                </CardHeader>
                <CardContent>
                  {status_breakdown.length === 0 ? (
                    <EmptyState icon={Activity} title="No data" description="Status breakdown will appear here." size="sm" />
                  ) : (
                    <ChartContainer height={250}>
                      <BarChart data={status_breakdown}>
                        <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                        <XAxis dataKey="status" className="text-xs" />
                        <YAxis allowDecimals={false} className="text-xs" />
                        <ChartTooltip />
                        <Bar dataKey="count" name="Subscriptions" radius={[4, 4, 0, 0]} animationDuration={600}>
                          {status_breakdown.map((entry, index) => (
                            <Cell key={`cell-${index}`} fill={SUBSCRIPTION_STATUS_COLORS[entry.status] ?? CHART_COLORS[index % CHART_COLORS.length]} />
                          ))}
                        </Bar>
                      </BarChart>
                    </ChartContainer>
                  )}
                </CardContent>
              </Card>
            </div>

            {/* Growth Chart */}
            <Card>
              <CardHeader>
                <CardTitle>New Subscriptions</CardTitle>
                <CardDescription>New subscriptions over the last 30 days</CardDescription>
              </CardHeader>
              <CardContent>
                {growth_chart.length === 0 ? (
                  <EmptyState icon={TrendingUp} title="No recent subscriptions" description="Subscription growth data will appear here." size="sm" />
                ) : (
                  <ChartContainer height={300}>
                    <AreaChart data={growth_chart}>
                      <defs>
                        <linearGradient id="billingGrowthGradient" x1="0" y1="0" x2="0" y2="1">
                          <stop offset="5%" stopColor="hsl(var(--primary))" stopOpacity={0.3} />
                          <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0} />
                        </linearGradient>
                      </defs>
                      <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                      <XAxis
                        dataKey="date"
                        tickFormatter={(v) => new Date(v).toLocaleDateString(undefined, { month: "short", day: "numeric" })}
                        className="text-xs"
                      />
                      <YAxis allowDecimals={false} className="text-xs" />
                      <ChartTooltip />
                      <Area type="monotone" dataKey="count" name="Subscriptions" stroke="hsl(var(--primary))" fill="url(#billingGrowthGradient)" strokeWidth={2} animationDuration={800} animationBegin={200} />
                    </AreaChart>
                  </ChartContainer>
                )}
              </CardContent>
            </Card>
          </>
        )}

        {/* Recent Billing Events */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Billing Events</CardTitle>
            <CardDescription>Latest billing-related audit log entries</CardDescription>
          </CardHeader>
          <CardContent>
            {recent_events.length === 0 ? (
              <EmptyState icon={Activity} title="No billing events yet" description="Billing events will appear here as subscription changes occur." size="sm" />
            ) : (
              <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Event</TableHead>
                    <TableHead>User</TableHead>
                    <TableHead>Time</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {recent_events.map((event) => (
                    <TableRow key={event.id} className="hover:bg-muted/50">
                      <TableCell>
                        <Link href={`/admin/audit-logs/${event.id}`} className="hover:opacity-80 transition-opacity">
                          <Badge variant="secondary">{event.event}</Badge>
                        </Link>
                      </TableCell>
                      <TableCell className="text-sm">
                        {event.user_name ?? <span className="text-muted-foreground">System</span>}
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground">{formatRelativeTime(event.created_at)}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
