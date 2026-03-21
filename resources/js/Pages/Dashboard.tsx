import {
  Activity,
  CheckCircle2,
  ChevronRight,
  CreditCard,
  Flame,
  Heart,
  Key,
  MailWarning,
  Settings,
  Users,
  Zap,
} from 'lucide-react';

import { useEffect, useRef, useState } from 'react';

import { Head, Link, router, usePage } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import { Button } from '@/Components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { EmptyState } from '@/Components/ui/empty-state';
import { Progress } from '@/Components/ui/progress';
import { useAnalytics } from '@/hooks/useAnalytics';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { AnalyticsEvents } from '@/lib/events';
import { formatRelativeTime } from '@/lib/format';
import type { PageProps } from '@/types';

interface DashboardStats {
  days_since_signup: number;
  health_score: number;
  email_verified: boolean;
  has_subscription: boolean;
  plan_name: string | null;
  settings_count: number;
  tokens_count: number;
  login_streak: number;
}

interface RecentActivityItem {
  event: string;
  created_at: string;
}

interface DashboardProps {
  stats: DashboardStats;
  recent_activity: RecentActivityItem[];
}

function healthLabel(score: number): { label: string; color: string } {
  if (score >= 76) return { label: 'Healthy', color: 'text-success' };
  if (score >= 51) return { label: 'Moderate', color: 'text-warning' };
  if (score >= 26) return { label: 'At Risk', color: 'text-orange-500' };
  return { label: 'Getting Started', color: 'text-muted-foreground' };
}

export default function Dashboard({ stats, recent_activity }: DashboardProps) {
  const { track } = useAnalytics();
  const { flash, features, limit_warnings } = usePage<PageProps>().props;
  const health = healthLabel(stats.health_score);
  const activationFiredRef = useRef(false);
  const healthCelebrationFiredRef = useRef(false);
  const limitWarningsFiredRef = useRef(false);
  const [resendingVerification, setResendingVerification] = useState(false);

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'dashboard' });
  }, [track]);

  useEffect(() => {
    if (flash?.new_registration) {
      track(AnalyticsEvents.AUTH_REGISTER, { source: String(flash.social_provider ?? 'social') });
    }
  }, [flash?.new_registration, flash?.social_provider, track]);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    const params = new URLSearchParams(window.location.search);
    if (params.get('verified') === '1') {
      track(AnalyticsEvents.AUTH_EMAIL_VERIFIED);
    }
  }, [track]);

  // Activation milestone: health_score >= 51 AND has at least one API token
  useEffect(() => {
    if (
      !activationFiredRef.current &&
      stats.health_score >= 51 &&
      stats.tokens_count > 0
    ) {
      activationFiredRef.current = true;
      track(AnalyticsEvents.ACTIVATION_MILESTONE, { trigger: 'api_token_created' });
    }
  }, [stats.health_score, stats.tokens_count, track]);

  // Health milestone celebration (health >= 76 first time this session)
  useEffect(() => {
    if (!healthCelebrationFiredRef.current && stats.health_score >= 76) {
      healthCelebrationFiredRef.current = true;
      track(AnalyticsEvents.ACTIVATION_MILESTONE, { trigger: 'health_score_healthy' });
    }
  }, [stats.health_score, track]);

  // PQL limit warnings — fire threshold events for resources approaching plan limits
  useEffect(() => {
    if (limitWarningsFiredRef.current || !limit_warnings) return;
    limitWarningsFiredRef.current = true;

    for (const [resource, info] of Object.entries(limit_warnings)) {
      if (info.threshold >= 100) {
        track(AnalyticsEvents.LIMIT_THRESHOLD_100, { resource, current_value: info.current });
      } else if (info.threshold >= 80) {
        track(AnalyticsEvents.LIMIT_THRESHOLD_80, { resource, current_value: info.current });
      }
    }
  }, [limit_warnings, track]);

  const completedCount = [
    stats.email_verified,
    stats.settings_count > 0,
    stats.tokens_count > 0,
    stats.has_subscription,
  ].filter(Boolean).length;

  const allSetupDone = completedCount === 4;

  const setupItems: Array<{ done: boolean; label: string; href: string }> = [
    {
      done: stats.email_verified,
      label: 'Verify your email address',
      href: '/email/verify',
    },
    {
      done: stats.settings_count > 0,
      label: 'Configure your preferences',
      href: '/settings',
    },
    {
      done: stats.tokens_count > 0,
      label: 'Create an API token',
      href: '/settings/tokens',
    },
    {
      done: stats.has_subscription,
      label: 'Choose a plan',
      href: features?.billing ? '/billing' : '/pricing',
    },
  ];

  // Next suggested action for incomplete setup
  const nextIncomplete = setupItems.find((item) => !item.done);

  // Explore-next actions shown once all basics are done
  const exploreActions = [
    ...(features?.webhooks ? [{ label: 'Set up webhooks', href: '/settings/webhooks' }] : []),
    ...(features?.apiDocs ? [{ label: 'Read the API docs', href: '/docs' }] : []),
    { label: 'View the roadmap', href: '/roadmap' },
    { label: 'Submit feedback', href: '#feedback' },
  ];

  const statCards = [
    {
      title: 'Account Health',
      value: `${stats.health_score}/100`,
      description: health.label,
      icon: Heart,
      valueClass: health.color,
      extra: null,
    },
    {
      title: 'Plan',
      value: stats.plan_name ?? 'Free',
      description: stats.has_subscription
        ? 'Active subscription'
        : 'No subscription',
      icon: CreditCard,
      valueClass: undefined,
      extra: !stats.has_subscription && features?.billing ? (
        <Link
          href="/pricing?ref=dashboard_plan_card"
          className="mt-1 inline-flex items-center text-xs font-medium text-primary hover:underline"
        >
          View Plans →
        </Link>
      ) : null,
    },
    {
      title: 'Settings',
      value: String(stats.settings_count),
      description: 'Preferences configured',
      icon: Settings,
      valueClass: undefined,
      extra: null,
    },
    {
      title: 'API Tokens',
      value: String(stats.tokens_count),
      description: 'Active tokens',
      icon: Key,
      valueClass: undefined,
      extra: null,
    },
  ];

  const handleResendVerification = () => {
    setResendingVerification(true);
    router.post(
      '/email/verification-notification',
      {},
      {
        onFinish: () => setResendingVerification(false),
      }
    );
  };

  return (
    <DashboardLayout>
      <Head title="Dashboard" />

      <PageHeader
        title="Dashboard"
        subtitle={allSetupDone
          ? "Here's what's happening in your app today."
          : "Your app is ready. Complete setup to unlock all features."}
      />

      <div className="container py-8">
        {/* Email verification nudge banner */}
        {!stats.email_verified && (
          <div className="mb-6 flex items-center justify-between gap-4 rounded-lg border border-warning/30 bg-warning/10 px-4 py-3 text-sm">
            <div className="flex items-center gap-2">
              <MailWarning className="h-4 w-4 text-warning shrink-0" />
              <span className="text-foreground">
                Verify your email to unlock all features.
              </span>
            </div>
            <Button
              variant="outline"
              size="sm"
              onClick={handleResendVerification}
              disabled={resendingVerification}
            >
              {resendingVerification ? 'Sending…' : 'Resend email'}
            </Button>
          </div>
        )}

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
                {stat.extra}
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Progress + Activity + Streak */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
          <Card className="col-span-4">
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle>
                    {allSetupDone ? 'What to explore next' : 'Account Setup'}
                  </CardTitle>
                  <CardDescription>
                    {allSetupDone
                      ? 'Your account is fully set up. Here are some advanced features to try.'
                      : 'Complete these steps to get the most out of your account.'}
                  </CardDescription>
                </div>
                {!allSetupDone && (
                  <span className="text-sm font-medium text-muted-foreground">
                    {completedCount}/4
                  </span>
                )}
              </div>
              {!allSetupDone && (
                <Progress value={(completedCount / 4) * 100} className="h-1.5 mt-2" />
              )}
            </CardHeader>
            <CardContent>
              {allSetupDone ? (
                <div className="space-y-3">
                  {exploreActions.map((action) => (
                    <div key={action.label} className="flex items-center gap-3">
                      <Zap className="h-4 w-4 shrink-0 text-primary" />
                      <Link
                        href={action.href}
                        className="flex flex-1 items-center justify-between text-sm text-muted-foreground hover:text-foreground transition-colors"
                      >
                        {action.label}
                        <ChevronRight className="h-3.5 w-3.5 text-muted-foreground/60" />
                      </Link>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="space-y-3">
                  {setupItems.map((item) => (
                    <SetupItem
                      key={item.label}
                      done={item.done}
                      label={item.label}
                      href={item.href}
                    />
                  ))}
                  {nextIncomplete && (
                    <div className="pt-2 border-t">
                      <Button asChild size="sm" className="w-full">
                        <Link href={nextIncomplete.href}>
                          {nextIncomplete.label} →
                        </Link>
                      </Button>
                    </div>
                  )}
                </div>
              )}
            </CardContent>
          </Card>

          <div className="col-span-3 flex flex-col gap-4">
            {/* Login Streak */}
            {stats.login_streak > 0 && (
              <Card>
                <CardHeader className="pb-2">
                  <div className="flex items-center gap-2">
                    <Flame className="h-4 w-4 text-orange-500" />
                    <CardTitle className="text-sm font-medium">Login Streak</CardTitle>
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {stats.login_streak}{' '}
                    <span className="text-sm font-normal text-muted-foreground">
                      {stats.login_streak === 1 ? 'day' : 'days'} in a row
                    </span>
                  </div>
                  {stats.login_streak >= 3 && (
                    <p className="mt-1 text-xs text-muted-foreground">
                      You're building a habit. Keep it up!
                    </p>
                  )}
                </CardContent>
              </Card>
            )}

            {/* Recent Activity */}
            <Card className="flex-1">
              <CardHeader>
                <CardTitle>Recent Activity</CardTitle>
                <CardDescription>Latest actions in your account.</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {recent_activity.length > 0 ? (
                    <div className="space-y-2">
                      {recent_activity.map((item, i) => (
                        <div key={i} className="flex items-center justify-between gap-3 text-sm">
                          <div className="flex items-center gap-2 min-w-0">
                            <Activity className="h-4 w-4 text-muted-foreground shrink-0" />
                            <span className="truncate">{item.event}</span>
                          </div>
                          <span className="text-xs text-muted-foreground shrink-0">
                            {formatRelativeTime(item.created_at)}
                          </span>
                        </div>
                      ))}
                    </div>
                  ) : stats.days_since_signup === 0 ? (
                    <div className="flex items-center gap-3 text-sm">
                      <Users className="h-4 w-4 text-primary" />
                      <span>Account created today — welcome!</span>
                    </div>
                  ) : (
                    <EmptyState
                      icon={Activity}
                      title="No activity recorded yet."
                      description="Actions like logins, setting changes, and billing events will appear here automatically."
                      size="sm"
                      animated={false}
                    />
                  )}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

function SetupItem({
  done,
  label,
  href,
}: {
  done: boolean;
  label: string;
  href: string;
}) {
  return (
    <div className="flex items-center gap-3">
      <CheckCircle2
        className={`h-4 w-4 shrink-0 ${done ? 'text-success' : 'text-muted-foreground/40'}`}
      />
      {done ? (
        <span className="text-sm text-foreground line-through decoration-muted-foreground/40">
          {label}
        </span>
      ) : (
        <Link
          href={href}
          className="flex flex-1 items-center justify-between text-sm text-muted-foreground hover:text-foreground transition-colors"
        >
          {label}
          <ChevronRight className="h-3.5 w-3.5 text-muted-foreground/60" />
        </Link>
      )}
    </div>
  );
}
