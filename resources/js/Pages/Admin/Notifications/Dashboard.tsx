import { Bell, CheckCircle, Mail, TrendingUp } from 'lucide-react';

import { Head } from '@inertiajs/react';

import { AdminAreaChart, AdminBarChart } from '@/Components/admin/AdminCharts';
import {
  AdminStatsGrid,
  type StatCard,
} from '@/Components/admin/AdminStatsGrid';
import PageHeader from '@/Components/layout/PageHeader';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import type { AdminNotificationsDashboardProps } from '@/types/admin';

export default function NotificationsDashboard({
  stats,
  volume_chart,
}: AdminNotificationsDashboardProps) {
  return (
    <AdminLayout>
      <Head title="Admin - Notifications" />
      <PageHeader
        title="Notifications"
        subtitle="Delivery stats and read rates"
      />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid
          stats={
            [
              { title: 'Total Sent', value: stats.total_sent, icon: Mail },
              { title: 'Unread', value: stats.unread, icon: Bell },
              {
                title: 'Read Rate',
                value: stats.read_rate,
                icon: CheckCircle,
                format: (n) => `${n}%`,
              },
              {
                title: 'Sent Last 7d',
                value: stats.sent_last_7d,
                icon: TrendingUp,
              },
            ] satisfies StatCard[]
          }
        />

        <div className="grid gap-6 md:grid-cols-2">
          {/* Volume Chart */}
          <Card>
            <CardHeader>
              <CardTitle>Notification Volume (14d)</CardTitle>
              <CardDescription>Notifications sent per day</CardDescription>
            </CardHeader>
            <CardContent>
              <AdminAreaChart
                data={volume_chart}
                dataKey="count"
                name="Sent"
                gradientId="notifGradient"
                emptyIcon={Mail}
                emptyTitle="No data yet"
                emptyDescription="Notification volume will appear here."
              />
            </CardContent>
          </Card>

          {/* By Type */}
          <Card>
            <CardHeader>
              <CardTitle>By Type</CardTitle>
              <CardDescription>Notifications grouped by type</CardDescription>
            </CardHeader>
            <CardContent>
              <AdminBarChart
                data={stats.by_type}
                dataKey="count"
                xAxisKey="type"
                name="Count"
                emptyIcon={Bell}
                emptyTitle="No data"
                emptyDescription="Type breakdown will appear here."
              />
            </CardContent>
          </Card>
        </div>
      </div>
    </AdminLayout>
  );
}
