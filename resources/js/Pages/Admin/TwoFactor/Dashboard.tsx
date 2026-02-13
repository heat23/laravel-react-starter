import { ShieldCheck, Users } from "lucide-react";

import { Head } from "@inertiajs/react";

import { AdminStatsGrid, type StatCard } from "@/Components/admin/AdminStatsGrid";
import PageHeader from "@/Components/layout/PageHeader";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Bar, BarChart, CartesianGrid, Cell, ChartContainer, ChartTooltip, XAxis, YAxis } from "@/Components/ui/chart";
import AdminLayout from "@/Layouts/AdminLayout";
import type { AdminTwoFactorDashboardProps } from "@/types/admin";

export default function TwoFactorDashboard({ stats }: AdminTwoFactorDashboardProps) {
  const chartData = [
    { label: "Enabled", count: stats.two_factor_enabled },
    { label: "Disabled", count: stats.without_two_factor },
  ];

  return (
    <AdminLayout>
      <Head title="Admin - Two-Factor" />
      <PageHeader title="Two-Factor Authentication" subtitle="2FA adoption metrics" />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid columns="grid-cols-1 md:grid-cols-3" stats={[
          { title: "2FA Enabled", value: stats.two_factor_enabled, icon: ShieldCheck, description: `of ${stats.total_users} total users` },
          { title: "Adoption Rate", value: stats.adoption_rate, icon: Users, format: (n) => `${n}%`, description: "Users with 2FA enabled" },
          { title: "Without 2FA", value: stats.without_two_factor, valueClassName: "text-muted-foreground", description: "Users without protection" },
        ] satisfies StatCard[]} />

        {/* Adoption Chart */}
        <Card>
          <CardHeader>
            <CardTitle>2FA Adoption</CardTitle>
            <CardDescription>Users with vs without two-factor authentication</CardDescription>
          </CardHeader>
          <CardContent>
            <ChartContainer height={250}>
              <BarChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                <XAxis dataKey="label" className="text-xs" />
                <YAxis allowDecimals={false} className="text-xs" />
                <ChartTooltip />
                <Bar dataKey="count" name="Users" radius={[4, 4, 0, 0]}>
                  {chartData.map((entry, index) => (
                    <Cell
                      key={`cell-${index}`}
                      fill={entry.label === "Enabled" ? "hsl(var(--success))" : "hsl(var(--muted-foreground))"}
                    />
                  ))}
                </Bar>
              </BarChart>
            </ChartContainer>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
