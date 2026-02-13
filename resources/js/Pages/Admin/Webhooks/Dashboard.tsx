import { AlertTriangle, Radio, Zap } from "lucide-react";

import { Head } from "@inertiajs/react";

import { AdminStatsGrid, type StatCard } from "@/Components/admin/AdminStatsGrid";
import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import {
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import AdminLayout from "@/Layouts/AdminLayout";
import { formatProviderName, formatRelativeTime } from "@/lib/format";
import type { AdminWebhooksDashboardProps } from "@/types/admin";

export default function WebhooksDashboard({ stats, delivery_chart, recent_failures }: AdminWebhooksDashboardProps) {
  const incomingByProvider = Object.entries(stats.incoming_by_provider).map(([provider, count]) => ({
    provider: formatProviderName(provider),
    count,
  }));

  return (
    <AdminLayout>
      <Head title="Admin - Webhooks" />
      <PageHeader title="Webhooks Overview" subtitle="Delivery stats and endpoint health" />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid stats={[
          { title: "Active Endpoints", value: stats.active_endpoints, icon: Radio, description: `${stats.total_endpoints} total` },
          { title: "Total Deliveries", value: stats.total_deliveries, icon: Zap, description: `${stats.successful_deliveries} successful` },
          { title: "Failure Rate", value: stats.failure_rate, icon: AlertTriangle, format: (n) => `${n}%`, description: `${stats.failed_deliveries} failed`, valueClassName: stats.failure_rate > 10 ? "text-destructive" : undefined },
          { title: "Incoming Webhooks", value: stats.total_incoming, description: `${stats.pending_deliveries} pending` },
        ] satisfies StatCard[]} />

        <div className="grid gap-6 md:grid-cols-2">
          {/* Delivery Volume Chart */}
          <Card>
            <CardHeader>
              <CardTitle>Delivery Volume (14d)</CardTitle>
              <CardDescription>Success vs failed deliveries by day</CardDescription>
            </CardHeader>
            <CardContent>
              {delivery_chart.length === 0 ? (
                <EmptyState icon={Zap} title="No deliveries yet" description="Delivery data will appear here." size="sm" />
              ) : (
                <ChartContainer height={250}>
                  <BarChart data={delivery_chart}>
                    <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                    <XAxis
                      dataKey="date"
                      tickFormatter={(v) => new Date(v).toLocaleDateString(undefined, { month: "short", day: "numeric" })}
                      className="text-xs"
                    />
                    <YAxis allowDecimals={false} className="text-xs" />
                    <ChartTooltip />
                    <Legend />
                    <Bar dataKey="success" name="Success" fill="hsl(142 71% 45%)" stackId="a" radius={[0, 0, 0, 0]} animationDuration={600} />
                    <Bar dataKey="failed" name="Failed" fill="hsl(0 84% 60%)" stackId="a" radius={[4, 4, 0, 0]} animationDuration={600} animationBegin={200} />
                  </BarChart>
                </ChartContainer>
              )}
            </CardContent>
          </Card>

          {/* Incoming by Provider */}
          <Card>
            <CardHeader>
              <CardTitle>Incoming by Provider</CardTitle>
              <CardDescription>Webhook sources</CardDescription>
            </CardHeader>
            <CardContent>
              {incomingByProvider.length === 0 ? (
                <EmptyState icon={Radio} title="No incoming webhooks" description="Provider data will appear here." size="sm" />
              ) : (
                <ChartContainer height={250}>
                  <PieChart>
                    <Pie data={incomingByProvider} dataKey="count" nameKey="provider" cx="50%" cy="50%" outerRadius={80} label={({ name, value }) => `${name}: ${value}`} animationDuration={800} animationBegin={100}>
                      {incomingByProvider.map((_entry, index) => (
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
        </div>

        {/* Recent Failures */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Failures</CardTitle>
            <CardDescription>Last 10 failed webhook deliveries</CardDescription>
          </CardHeader>
          <CardContent>
            {recent_failures.length === 0 ? (
              <EmptyState icon={AlertTriangle} title="No failures" description="No failed deliveries to show." size="sm" />
            ) : (
              <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Endpoint</TableHead>
                    <TableHead>Event</TableHead>
                    <TableHead>Response</TableHead>
                    <TableHead>Attempts</TableHead>
                    <TableHead>Time</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {recent_failures.map((failure) => (
                    <TableRow key={failure.id}>
                      <TableCell className="text-sm font-mono max-w-[200px] truncate">{failure.endpoint_url}</TableCell>
                      <TableCell><Badge variant="secondary">{failure.event_type}</Badge></TableCell>
                      <TableCell>
                        <Badge variant="destructive">{failure.response_code ?? "timeout"}</Badge>
                      </TableCell>
                      <TableCell className="text-sm">{failure.attempts}</TableCell>
                      <TableCell className="text-sm text-muted-foreground">{formatRelativeTime(failure.created_at)}</TableCell>
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
