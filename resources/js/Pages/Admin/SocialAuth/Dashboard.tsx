import { Clock, Link2, Users } from "lucide-react";

import { Head } from "@inertiajs/react";

import { AdminStatsGrid, type StatCard } from "@/Components/admin/AdminStatsGrid";
import PageHeader from "@/Components/layout/PageHeader";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Cell, CHART_COLORS, ChartContainer, ChartTooltip, Legend, Pie, PieChart } from "@/Components/ui/chart";
import { EmptyState } from "@/Components/ui/empty-state";
import AdminLayout from "@/Layouts/AdminLayout";
import { formatProviderName } from "@/lib/format";
import type { AdminSocialAuthDashboardProps } from "@/types/admin";

export default function SocialAuthDashboard({ stats }: AdminSocialAuthDashboardProps) {
  const providerData = Object.entries(stats.by_provider).map(([provider, count]) => ({
    provider: formatProviderName(provider),
    count,
  }));

  return (
    <AdminLayout>
      <Head title="Admin - Social Auth" />
      <PageHeader title="Social Authentication" subtitle="OAuth provider statistics" />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid columns="grid-cols-1 md:grid-cols-3" stats={[
          { title: "Linked Accounts", value: stats.total_linked, icon: Link2 },
          { title: "Users with Social Auth", value: stats.users_with_social, icon: Users },
          { title: "Expired Tokens", value: stats.expired_tokens, icon: Clock },
        ] satisfies StatCard[]} />

        {/* Provider Distribution */}
        <Card>
          <CardHeader>
            <CardTitle>Accounts by Provider</CardTitle>
            <CardDescription>Distribution of linked OAuth accounts</CardDescription>
          </CardHeader>
          <CardContent>
            {providerData.length === 0 ? (
              <EmptyState icon={Link2} title="No linked accounts" description="OAuth provider data will appear here." size="sm" animated={false} />
            ) : (
              <ChartContainer height={300}>
                <PieChart>
                  <Pie data={providerData} dataKey="count" nameKey="provider" cx="50%" cy="50%" outerRadius={100} label={({ name, value }) => `${name}: ${value}`}>
                    {providerData.map((_entry, index) => (
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
    </AdminLayout>
  );
}
