import { BarChart2, TrendingUp, Users, Zap } from 'lucide-react';

import { Head } from '@inertiajs/react';

import { AdminAreaChart } from '@/Components/admin/AdminCharts';
import { AdminStatsGrid, type StatCard } from '@/Components/admin/AdminStatsGrid';
import PageHeader from '@/Components/layout/PageHeader';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
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

interface FunnelStep {
  label: string;
  count: number;
  rate?: number;
}

interface OnboardingFunnel {
  registered: number;
  started: number;
  completed: number;
  start_rate: number;
  completion_rate: number;
}

interface Activation {
  total_users: number;
  activated_users: number;
  activation_rate: number;
}

interface FeatureAdoptionRow {
  feature_name: string;
  count: number;
}

interface SubscriptionEvents {
  created: number;
  canceled: number;
  resumed: number;
}

interface ProductAnalyticsProps {
  signup_trend: Array<{ date: string; count: number }>;
  onboarding_funnel: OnboardingFunnel;
  activation: Activation;
  feature_adoption: FeatureAdoptionRow[];
  subscription_events: SubscriptionEvents;
}

export default function ProductAnalytics({
  signup_trend,
  onboarding_funnel,
  activation,
  feature_adoption,
  subscription_events,
}: ProductAnalyticsProps) {
  const statCards: StatCard[] = [
    {
      title: 'Activation Rate',
      value: `${activation.activation_rate}%`,
      icon: Zap,
      description: `${activation.activated_users} of ${activation.total_users} users completed onboarding`,
    },
    {
      title: 'Onboarding Start Rate',
      value: `${onboarding_funnel.start_rate}%`,
      icon: TrendingUp,
      description: 'Signups who started onboarding (last 30d)',
    },
    {
      title: 'Onboarding Completion',
      value: `${onboarding_funnel.completion_rate}%`,
      icon: Users,
      description: 'Started → completed onboarding (last 30d)',
    },
    {
      title: 'New Subscriptions (30d)',
      value: subscription_events.created,
      icon: BarChart2,
      description: `${subscription_events.canceled} canceled · ${subscription_events.resumed} resumed`,
    },
  ];

  const funnelSteps: FunnelStep[] = [
    { label: 'Registered', count: onboarding_funnel.registered },
    {
      label: 'Started Onboarding',
      count: onboarding_funnel.started,
      rate: onboarding_funnel.start_rate,
    },
    {
      label: 'Completed Onboarding',
      count: onboarding_funnel.completed,
      rate: onboarding_funnel.completion_rate,
    },
  ];

  return (
    <AdminLayout>
      <Head title="Product Analytics" />
      <PageHeader
        title="Product Analytics"
        subtitle="Funnel conversion, feature adoption, and activation metrics (last 30 days)"
      />

      <div className="container py-8 space-y-8">
        <AdminStatsGrid stats={statCards} />

        {/* Signup Trend */}
        <Card>
          <CardHeader>
            <CardTitle>Signup Trend (7 days)</CardTitle>
            <CardDescription>
              Daily registrations from audit_logs — consent-independent
            </CardDescription>
          </CardHeader>
          <CardContent>
            <AdminAreaChart
              data={signup_trend}
              dataKey="count"
              name="Signups"
              gradientId="signupTrendGradient"
              emptyIcon={TrendingUp}
              emptyTitle="No signup data yet"
              emptyDescription="Registration events will appear here once users sign up."
            />
          </CardContent>
        </Card>

        {/* Onboarding Funnel */}
        <Card>
          <CardHeader>
            <CardTitle>Onboarding Funnel (30 days)</CardTitle>
            <CardDescription>
              Server-side counts — independent of cookie consent
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {funnelSteps.map((step, index) => {
                const maxCount = funnelSteps[0].count || 1;
                const pct = Math.round((step.count / maxCount) * 100);
                return (
                  <div key={step.label} className="space-y-1">
                    <div className="flex items-center justify-between text-sm">
                      <span className="font-medium">
                        {index + 1}. {step.label}
                      </span>
                      <span className="text-muted-foreground">
                        {step.count.toLocaleString()}
                        {step.rate !== undefined && (
                          <span className="ml-2 text-xs">({step.rate}% of prev step)</span>
                        )}
                      </span>
                    </div>
                    <div className="h-2 rounded-full bg-muted overflow-hidden">
                      <div
                        className="h-full rounded-full bg-primary transition-all"
                        style={{ width: `${pct}%` }}
                      />
                    </div>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>

        {/* Feature Adoption */}
        <Card>
          <CardHeader>
            <CardTitle>Feature Adoption (30 days)</CardTitle>
            <CardDescription>
              Top features used, ranked by event count
            </CardDescription>
          </CardHeader>
          <CardContent>
            {feature_adoption.length === 0 ? (
              <EmptyState
                icon={BarChart2}
                title="No feature usage data"
                description="feature.used events will appear here once users interact with the app."
                size="sm"
              />
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Feature</TableHead>
                    <TableHead className="text-right">Events (30d)</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {feature_adoption.map((row) => (
                    <TableRow key={row.feature_name}>
                      <TableCell className="font-mono text-sm">
                        {row.feature_name}
                      </TableCell>
                      <TableCell className="text-right text-sm">
                        {row.count.toLocaleString()}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
