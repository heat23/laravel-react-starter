import { Bell, CheckCircle, Mail, TrendingUp } from "lucide-react";

import { Head } from "@inertiajs/react";

import { AdminStatsGrid, type StatCard } from "@/Components/admin/AdminStatsGrid";
import PageHeader from "@/Components/layout/PageHeader";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  CHART_COLORS,
  ChartContainer,
  ChartTooltip,
  XAxis,
  YAxis,
} from "@/Components/ui/chart";
import { EmptyState } from "@/Components/ui/empty-state";
import AdminLayout from "@/Layouts/AdminLayout";
import type { AdminNotificationsDashboardProps } from "@/types/admin";

export default function NotificationsDashboard({ stats, volume_chart }: AdminNotificationsDashboardProps) {
  return (
    <AdminLayout>
      <Head title="Admin - Notifications" />
      <PageHeader title="Notifications" subtitle="Delivery stats and read rates" />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid stats={[
          { title: "Total Sent", value: stats.total_sent, icon: Mail },
          { title: "Unread", value: stats.unread, icon: Bell },
          { title: "Read Rate", value: stats.read_rate, icon: CheckCircle, format: (n) => `${n}%` },
          { title: "Sent Last 7d", value: stats.sent_last_7d, icon: TrendingUp },
        ] satisfies StatCard[]} />

        <div className="grid gap-6 md:grid-cols-2">
          {/* Volume Chart */}
          <Card>
            <CardHeader>
              <CardTitle>Notification Volume (14d)</CardTitle>
              <CardDescription>Notifications sent per day</CardDescription>
            </CardHeader>
            <CardContent>
              {volume_chart.length === 0 ? (
                <EmptyState icon={Mail} title="No data yet" description="Notification volume will appear here." size="sm" animated={false} />
              ) : (
                <ChartContainer height={250}>
                  <AreaChart data={volume_chart}>
                    <defs>
                      <linearGradient id="notifGradient" x1="0" y1="0" x2="0" y2="1">
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
                    <Area type="monotone" dataKey="count" name="Sent" stroke="hsl(var(--primary))" fill="url(#notifGradient)" strokeWidth={2} />
                  </AreaChart>
                </ChartContainer>
              )}
            </CardContent>
          </Card>

          {/* By Type */}
          <Card>
            <CardHeader>
              <CardTitle>By Type</CardTitle>
              <CardDescription>Notifications grouped by type</CardDescription>
            </CardHeader>
            <CardContent>
              {stats.by_type.length === 0 ? (
                <EmptyState icon={Bell} title="No data" description="Type breakdown will appear here." size="sm" animated={false} />
              ) : (
                <ChartContainer height={250}>
                  <BarChart data={stats.by_type}>
                    <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                    <XAxis dataKey="type" className="text-xs" />
                    <YAxis allowDecimals={false} className="text-xs" />
                    <ChartTooltip />
                    <Bar dataKey="count" name="Count" fill={CHART_COLORS[0]} radius={[4, 4, 0, 0]} />
                  </BarChart>
                </ChartContainer>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AdminLayout>
  );
}
