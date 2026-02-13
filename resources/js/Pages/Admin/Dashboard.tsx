import { Activity, CreditCard, Shield, TrendingUp, Users } from "lucide-react";

import { Head, Link } from "@inertiajs/react";

import { AdminAreaChart } from "@/Components/admin/AdminCharts";
import { AdminStatsGrid, type StatCard } from "@/Components/admin/AdminStatsGrid";
import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { EmptyState } from "@/Components/ui/empty-state";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import AdminLayout from "@/Layouts/AdminLayout";
import { formatRelativeTime } from "@/lib/format";
import type { AdminDashboardProps } from "@/types/admin";

export default function AdminDashboard({ stats, signup_chart, recent_activity }: AdminDashboardProps) {
  const statCards: StatCard[] = [
    { title: "Total Users", value: stats.total_users, icon: Users, description: "All registered users", href: "/admin/users" },
    { title: "New (7d)", value: stats.new_users_7d, icon: TrendingUp, description: "Signups this week", href: "/admin/users?sort=created_at&dir=desc" },
    { title: "New (30d)", value: stats.new_users_30d, icon: TrendingUp, description: "Signups this month", href: "/admin/users?sort=created_at&dir=desc" },
    { title: "Admins", value: stats.admin_count, icon: Shield, description: "Admin accounts", href: "/admin/users?admin=1" },
  ];

  if (stats.active_subscriptions !== undefined) {
    statCards.push({
      title: "Active Subscriptions",
      value: stats.active_subscriptions,
      icon: CreditCard,
      description: "Paying customers",
      href: "/admin/billing/subscriptions?status=active",
    });
  }

  return (
    <AdminLayout>
      <Head title="Admin Dashboard" />
      <PageHeader title="Admin Dashboard" subtitle="System overview and metrics" />

      <div className="container py-8 space-y-8">
        <AdminStatsGrid stats={statCards} />

        {/* Signup Chart */}
        <Card>
          <CardHeader>
            <CardTitle>User Signups</CardTitle>
            <CardDescription>New user registrations over the last 30 days</CardDescription>
          </CardHeader>
          <CardContent>
            <AdminAreaChart
              data={signup_chart}
              dataKey="count"
              name="Signups"
              gradientId="signupGradient"
              emptyIcon={TrendingUp}
              emptyTitle="No signups yet"
              emptyDescription="User signup data will appear here."
            />
          </CardContent>
        </Card>

        {/* Recent Activity */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
            <CardDescription>Latest audit log entries</CardDescription>
          </CardHeader>
          <CardContent>
            {recent_activity.length === 0 ? (
              <EmptyState
                icon={Activity}
                title="No activity recorded yet"
                description="Audit log entries will appear here as users interact with the app."
                size="sm"
              />
            ) : (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Event</TableHead>
                      <TableHead>User</TableHead>
                      <TableHead>IP</TableHead>
                      <TableHead>Time</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {recent_activity.map((log) => (
                      <TableRow key={log.id} className="hover:bg-muted/50">
                        <TableCell>
                          <Link href={`/admin/audit-logs/${log.id}`} className="hover:opacity-80 transition-opacity">
                            <Badge variant="secondary">{log.event}</Badge>
                          </Link>
                        </TableCell>
                        <TableCell className="text-sm">
                          {log.user_name ?? <span className="text-muted-foreground">System</span>}
                        </TableCell>
                        <TableCell className="text-sm text-muted-foreground font-mono">{log.ip}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">
                          {formatRelativeTime(log.created_at)}
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
