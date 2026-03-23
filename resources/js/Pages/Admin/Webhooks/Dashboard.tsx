import { AlertTriangle, Radio, Zap } from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import { AdminBarChart, AdminPieChart } from '@/Components/admin/AdminCharts';
import {
  AdminStatsGrid,
  type StatCard,
} from '@/Components/admin/AdminStatsGrid';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { Bar, Legend } from '@/Components/ui/chart';
import { EmptyState } from '@/Components/ui/empty-state';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatProviderName, formatRelativeTime } from '@/lib/format';
import type { AdminWebhooksDashboardProps } from '@/types/admin';

export default function WebhooksDashboard({
  stats,
  delivery_chart,
  recent_failures,
}: AdminWebhooksDashboardProps) {
  const incomingByProvider = Object.entries(stats.incoming_by_provider).map(
    ([provider, count]) => ({
      provider: formatProviderName(provider),
      count,
    })
  );

  return (
    <AdminLayout>
      <Head title="Admin - Webhooks" />
      <PageHeader
        title="Webhooks Overview"
        subtitle="Delivery stats and endpoint health"
      />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid
          stats={
            [
              {
                title: 'Active Endpoints',
                value: stats.active_endpoints,
                icon: Radio,
                description: `${stats.total_endpoints} total`,
              },
              {
                title: 'Total Deliveries',
                value: stats.total_deliveries,
                icon: Zap,
                description: `${stats.successful_deliveries} successful`,
              },
              {
                title: 'Failure Rate',
                value: stats.failure_rate,
                icon: AlertTriangle,
                format: (n) => `${n}%`,
                description: `${stats.failed_deliveries} failed`,
                valueClassName:
                  stats.failure_rate > 10 ? 'text-destructive' : undefined,
              },
              {
                title: 'Incoming Webhooks',
                value: stats.total_incoming,
                description: `${stats.pending_deliveries} pending`,
                href: '/admin/webhooks/incoming',
              },
            ] satisfies StatCard[]
          }
        />

        <div className="grid gap-6 md:grid-cols-2">
          {/* Delivery Volume Chart — stacked bar */}
          <Card>
            <CardHeader>
              <CardTitle>Delivery Volume (14d)</CardTitle>
              <CardDescription>
                Success vs failed deliveries by day
              </CardDescription>
            </CardHeader>
            <CardContent>
              <AdminBarChart
                data={delivery_chart}
                dataKey="success"
                xAxisKey="date"
                name="Success"
                emptyIcon={Zap}
                emptyTitle="No deliveries yet"
                emptyDescription="Delivery data will appear here."
              >
                <Legend />
                <Bar
                  dataKey="success"
                  name="Success"
                  fill="hsl(var(--success))"
                  stackId="a"
                  radius={[0, 0, 0, 0]}
                  animationDuration={600}
                />
                <Bar
                  dataKey="failed"
                  name="Failed"
                  fill="hsl(var(--destructive))"
                  stackId="a"
                  radius={[4, 4, 0, 0]}
                  animationDuration={600}
                  animationBegin={200}
                />
              </AdminBarChart>
            </CardContent>
          </Card>

          {/* Incoming by Provider */}
          <Card>
            <CardHeader>
              <CardTitle>Incoming by Provider</CardTitle>
              <CardDescription>Webhook sources</CardDescription>
            </CardHeader>
            <CardContent>
              <AdminPieChart
                data={incomingByProvider}
                dataKey="count"
                nameKey="provider"
                emptyIcon={Radio}
                emptyTitle="No incoming webhooks"
                emptyDescription="Provider data will appear here."
              />
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
              <EmptyState
                icon={AlertTriangle}
                title="No failures"
                description="No failed deliveries to show."
                size="sm"
              />
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
                      <TableHead className="sr-only">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {recent_failures.map((failure) => (
                      <TableRow key={failure.id}>
                        <TableCell className="text-sm font-mono max-w-[200px] truncate" title={failure.endpoint_url}>
                          {failure.endpoint_url}
                        </TableCell>
                        <TableCell>
                          <Badge variant="secondary">
                            {failure.event_type}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <Badge variant="destructive">
                            {failure.response_code ?? 'timeout'}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-sm">
                          {failure.attempts}
                        </TableCell>
                        <TableCell className="text-sm text-muted-foreground">
                          {formatRelativeTime(failure.created_at)}
                        </TableCell>
                        <TableCell>
                          <Link
                            href={`/admin/webhooks/deliveries/${failure.id}`}
                            className="text-xs text-muted-foreground hover:text-foreground hover:underline"
                          >
                            View
                          </Link>
                        </TableCell>
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
