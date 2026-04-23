import {
  Activity,
  CreditCard,
  DollarSign,
  TrendingDown,
  TrendingUp,
  Users,
} from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import {
  AdminAreaChart,
  AdminBarChart,
  AdminPieChart,
} from '@/Components/admin/AdminCharts';
import {
  AdminStatsGrid,
  type StatCard,
} from '@/Components/admin/AdminStatsGrid';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { Bar, Cell, CHART_COLORS } from '@/Components/ui/chart';
import { EmptyState } from '@/Components/ui/empty-state';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import { SUBSCRIPTION_STATUS_COLORS } from '@/config/billing-constants';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatCurrency, formatRelativeTime } from '@/lib/format';
import type { AdminBillingDashboardProps } from '@/types/admin';

// Thresholds previously lived in config/analytics-thresholds.php.
// Inlined here now that the scoring subsystem has been removed.
const CHURN_THRESHOLDS = { warning: 5, critical: 10 };
const TRIAL_CONVERSION_THRESHOLDS = { warning_below: 25, critical_below: 15 };

export default function BillingDashboard({
  stats,
  tier_distribution,
  status_breakdown,
  growth_chart,
  trial_stats,
  recent_events,
  cohort_retention,
}: AdminBillingDashboardProps) {
  const hasSubscriptions = stats.total_ever > 0;

  const primaryKpis: StatCard[] = [
    {
      title: 'MRR',
      value: stats.mrr,
      icon: DollarSign,
      format: formatCurrency,
      description:
        'Excludes trialing subscriptions. Committed MRR from active paying subscribers only.',
    },
    {
      title: 'Active Subscriptions',
      value: stats.active_subscriptions,
      icon: CreditCard,
      description: 'Paying customers',
      href: '/admin/billing/subscriptions?status=active',
    },
    {
      title: 'Churn Rate (30d)',
      value: stats.churn_rate,
      icon: TrendingDown,
      format: (n) => `${n}%`,
      description: 'Cancellations vs active',
      threshold: {
        warning: CHURN_THRESHOLDS.warning,
        critical: CHURN_THRESHOLDS.critical,
        direction: 'above',
      },
    },
    {
      title: 'Trial Conversion',
      value: stats.trial_conversion_rate,
      icon: TrendingUp,
      format: (n) => `${n}%`,
      description: 'Trial to paid',
      href: '/admin/billing/subscriptions?status=trialing',
      threshold: {
        warning: TRIAL_CONVERSION_THRESHOLDS.warning_below,
        critical: TRIAL_CONVERSION_THRESHOLDS.critical_below,
        direction: 'below',
      },
    },
  ];

  const secondaryKpis: StatCard[] = [
    { title: 'Trialing', value: stats.trialing },
    {
      title: 'Past Due',
      value: stats.past_due,
      valueClassName: 'text-warning',
    },
    { title: 'Canceled', value: stats.canceled },
    {
      title: 'Trials Expiring Soon',
      value: trial_stats.expiring_soon,
      valueClassName: 'text-warning',
      description: 'Within 3 days',
    },
  ];

  const growthKpis: StatCard[] = [
    {
      title: 'Activation Rate',
      value: stats.activation_rate,
      icon: TrendingUp,
      format: (n) => `${n}%`,
      description: 'Completed onboarding',
    },
    {
      title: 'Lifetime Conversion',
      value: stats.signup_to_paid_conversion,
      icon: CreditCard,
      format: (n) => `${n}%`,
      description: 'All-time free to paid',
    },
    {
      title: '30d Cohort Conversion',
      value: stats.cohort_conversion_30d,
      icon: TrendingUp,
      format: (n) => `${n}%`,
      description: 'Last 30 days signup to paid',
    },
  ];

  return (
    <AdminLayout>
      <Head title="Admin - Billing" />
      <PageHeader
        title="Billing Overview"
        subtitle="Revenue metrics and subscription analytics"
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link href="/admin/billing/subscriptions">
              View All Subscriptions
            </Link>
          </Button>
        }
      />

      <div className="container py-8 space-y-8">
        {/* Primary KPIs */}
        <AdminStatsGrid stats={primaryKpis} cachedAt={stats.cached_at} />

        {/* Secondary stats */}
        <AdminStatsGrid stats={secondaryKpis} cachedAt={stats.cached_at} />

        {/* Growth metrics */}
        <AdminStatsGrid stats={growthKpis} cachedAt={stats.cached_at} />

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
                  <CardDescription>
                    Active subscription distribution
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <AdminPieChart
                    data={tier_distribution}
                    dataKey="count"
                    nameKey="tier"
                    emptyIcon={Users}
                    emptyTitle="No active subscriptions"
                    emptyDescription="Tier data will appear when subscriptions are active."
                  />
                </CardContent>
              </Card>

              {/* Status Breakdown */}
              <Card>
                <CardHeader>
                  <CardTitle>Status Breakdown</CardTitle>
                  <CardDescription>All subscriptions by status</CardDescription>
                </CardHeader>
                <CardContent>
                  <AdminBarChart
                    data={status_breakdown}
                    dataKey="count"
                    xAxisKey="status"
                    name="Subscriptions"
                    emptyIcon={Activity}
                    emptyTitle="No data"
                    emptyDescription="Status breakdown will appear here."
                  >
                    <Bar
                      dataKey="count"
                      name="Subscriptions"
                      radius={[4, 4, 0, 0]}
                      animationDuration={600}
                    >
                      {status_breakdown.map((entry, index) => (
                        <Cell
                          key={`cell-${index}`}
                          fill={
                            SUBSCRIPTION_STATUS_COLORS[entry.status] ??
                            CHART_COLORS[index % CHART_COLORS.length]
                          }
                        />
                      ))}
                    </Bar>
                  </AdminBarChart>
                </CardContent>
              </Card>
            </div>

            {/* Growth Chart */}
            <Card>
              <CardHeader>
                <CardTitle>New Subscriptions</CardTitle>
                <CardDescription>
                  New subscriptions over the last 30 days
                </CardDescription>
              </CardHeader>
              <CardContent>
                <AdminAreaChart
                  data={growth_chart}
                  dataKey="count"
                  name="Subscriptions"
                  gradientId="billingGrowthGradient"
                  emptyIcon={TrendingUp}
                  emptyTitle="No recent subscriptions"
                  emptyDescription="Subscription growth data will appear here."
                />
              </CardContent>
            </Card>
          </>
        )}

        {/* Cohort Retention */}
        {cohort_retention?.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle>Cohort Retention</CardTitle>
              <CardDescription>
                Percentage of users active at week 1, 2, 4, and 8 after signup
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Cohort</TableHead>
                      <TableHead className="text-right">Users</TableHead>
                      <TableHead className="text-right">Week 1</TableHead>
                      <TableHead className="text-right">Week 2</TableHead>
                      <TableHead className="text-right">Week 4</TableHead>
                      <TableHead className="text-right">Week 8</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {cohort_retention.map((cohort) => (
                      <TableRow key={cohort.cohort}>
                        <TableCell className="font-medium">
                          {cohort.cohort}
                        </TableCell>
                        <TableCell className="text-right">
                          {cohort.total}
                        </TableCell>
                        {[
                          cohort.week_1,
                          cohort.week_2,
                          cohort.week_4,
                          cohort.week_8,
                        ].map((value, i) => (
                          <TableCell
                            key={i}
                            className="text-right text-sm text-muted-foreground"
                          >
                            {value !== null ? `${value}%` : '—'}
                          </TableCell>
                        ))}
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Recent Billing Events */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Billing Events</CardTitle>
            <CardDescription>
              Latest billing-related audit log entries
            </CardDescription>
          </CardHeader>
          <CardContent>
            {recent_events.length === 0 ? (
              <EmptyState
                icon={Activity}
                title="No billing events yet"
                description="Billing events will appear here as subscription changes occur."
                size="sm"
              />
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
                          <Link
                            href={`/admin/audit-logs/${event.id}`}
                            className="hover:opacity-80 transition-opacity"
                          >
                            <Badge variant="secondary">{event.event}</Badge>
                          </Link>
                        </TableCell>
                        <TableCell className="text-sm">
                          {event.user_name ?? (
                            <span className="text-muted-foreground">
                              System
                            </span>
                          )}
                        </TableCell>
                        <TableCell className="text-sm text-muted-foreground">
                          {formatRelativeTime(event.created_at)}
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
