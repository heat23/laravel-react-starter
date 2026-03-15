import { Clock, Link2, Users } from 'lucide-react';

import { Head } from '@inertiajs/react';

import { AdminPieChart } from '@/Components/admin/AdminCharts';
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
import { formatProviderName } from '@/lib/format';
import type { AdminSocialAuthDashboardProps } from '@/types/admin';

export default function SocialAuthDashboard({
  stats,
}: AdminSocialAuthDashboardProps) {
  const providerData = Object.entries(stats.by_provider).map(
    ([provider, count]) => ({
      provider: formatProviderName(provider),
      count,
    })
  );

  return (
    <AdminLayout>
      <Head title="Admin - Social Auth" />
      <PageHeader
        title="Social Authentication"
        subtitle="OAuth provider statistics"
      />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid
          columns="grid-cols-1 md:grid-cols-3"
          stats={
            [
              {
                title: 'Linked Accounts',
                value: stats.total_linked,
                icon: Link2,
              },
              {
                title: 'Users with Social Auth',
                value: stats.users_with_social,
                icon: Users,
              },
              {
                title: 'Expired Tokens',
                value: stats.expired_tokens,
                icon: Clock,
              },
            ] satisfies StatCard[]
          }
        />

        {/* Provider Distribution */}
        <Card>
          <CardHeader>
            <CardTitle>Accounts by Provider</CardTitle>
            <CardDescription>
              Distribution of linked OAuth accounts
            </CardDescription>
          </CardHeader>
          <CardContent>
            <AdminPieChart
              data={providerData}
              dataKey="count"
              nameKey="provider"
              emptyIcon={Link2}
              emptyTitle="No linked accounts"
              emptyDescription="OAuth provider data will appear here."
            />
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
