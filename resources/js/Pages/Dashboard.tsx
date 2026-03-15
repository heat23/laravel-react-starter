import {
  Activity,
  CheckCircle2,
  CreditCard,
  Heart,
  Key,
  Settings,
  Users,
} from 'lucide-react';

import { Head } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { EmptyState } from '@/Components/ui/empty-state';
import DashboardLayout from '@/Layouts/DashboardLayout';

interface DashboardStats {
  days_since_signup: number;
  health_score: number;
  email_verified: boolean;
  has_subscription: boolean;
  plan_name: string | null;
  settings_count: number;
  tokens_count: number;
}

interface DashboardProps {
  stats: DashboardStats;
}

function healthLabel(score: number): { label: string; color: string } {
  if (score >= 76) return { label: 'Healthy', color: 'text-success' };
  if (score >= 51) return { label: 'Moderate', color: 'text-warning' };
  if (score >= 26) return { label: 'At Risk', color: 'text-orange-500' };
  return { label: 'Getting Started', color: 'text-muted-foreground' };
}

export default function Dashboard({ stats }: DashboardProps) {
  const health = healthLabel(stats.health_score);

  const statCards = [
    {
      title: 'Account Health',
      value: `${stats.health_score}/100`,
      description: health.label,
      icon: Heart,
      valueClass: health.color,
    },
    {
      title: 'Plan',
      value: stats.plan_name ?? 'Free',
      description: stats.has_subscription
        ? 'Active subscription'
        : 'No subscription',
      icon: CreditCard,
    },
    {
      title: 'Settings',
      value: String(stats.settings_count),
      description: 'Preferences configured',
      icon: Settings,
    },
    {
      title: 'API Tokens',
      value: String(stats.tokens_count),
      description: 'Active tokens',
      icon: Key,
    },
  ];

  return (
    <DashboardLayout>
      <Head title="Dashboard" />

      <PageHeader
        title="Dashboard"
        subtitle="Welcome to your application dashboard"
      />

      <div className="container py-8">
        {/* Stats Grid */}
        <div className="mb-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {statCards.map((stat) => (
            <Card key={stat.title}>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">
                  {stat.title}
                </CardTitle>
                <stat.icon className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className={`text-2xl font-bold ${stat.valueClass ?? ''}`}>
                  {stat.value}
                </div>
                <p className="text-xs text-muted-foreground">
                  {stat.description}
                </p>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Account Setup Progress */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
          <Card className="col-span-4">
            <CardHeader>
              <CardTitle>Account Setup</CardTitle>
              <CardDescription>
                Complete these steps to get the most out of your account.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                <SetupItem
                  done={stats.email_verified}
                  label="Verify your email address"
                />
                <SetupItem
                  done={stats.settings_count > 0}
                  label="Configure your preferences"
                />
                <SetupItem
                  done={stats.tokens_count > 0}
                  label="Create an API token"
                />
                <SetupItem
                  done={stats.has_subscription}
                  label="Choose a plan"
                />
              </div>
            </CardContent>
          </Card>

          <Card className="col-span-3">
            <CardHeader>
              <CardTitle>Recent Activity</CardTitle>
              <CardDescription>Latest actions in your account.</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {stats.days_since_signup === 0 ? (
                  <div className="flex items-center gap-3 text-sm">
                    <Users className="h-4 w-4 text-primary" />
                    <span>Account created today — welcome!</span>
                  </div>
                ) : (
                  <EmptyState
                    icon={Activity}
                    title="No Recent Activity"
                    description="Your recent actions will appear here as you use the app."
                    size="sm"
                    animated={false}
                  />
                )}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}

function SetupItem({ done, label }: { done: boolean; label: string }) {
  return (
    <div className="flex items-center gap-3">
      <CheckCircle2
        className={`h-4 w-4 ${done ? 'text-success' : 'text-muted-foreground/40'}`}
      />
      <span
        className={`text-sm ${done ? 'text-foreground' : 'text-muted-foreground'}`}
      >
        {label}
      </span>
    </div>
  );
}
