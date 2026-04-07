/**
 * Admin UI Smoke Tests
 *
 * Basic rendering tests for all admin pages to ensure they don't crash.
 * These are intentionally lightweight - full interaction testing is done in dedicated test files.
 *
 * Static imports are used (instead of dynamic await import()) so Vitest loads
 * all modules upfront via its transform cache, avoiding per-test timeout issues
 * caused by slow first-time module transformation.
 */

import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

// Mock Inertia
vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    usePage: vi.fn(() => ({
      url: '/admin',
      props: {
        auth: {
          user: {
            id: 1,
            name: 'Admin',
            email: 'admin@test.com',
            is_admin: true,
          },
        },
        features: {
          billing: true,
          socialAuth: true,
          emailVerification: true,
          apiTokens: true,
          userSettings: true,
          notifications: true,
          onboarding: false,
          apiDocs: false,
          twoFactor: true,
          webhooks: true,
          admin: true,
        },
      },
    })),
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
    router: {
      visit: vi.fn(),
      get: vi.fn(),
      post: vi.fn(),
      patch: vi.fn(),
      delete: vi.fn(),
      on: vi.fn(() => vi.fn()), // Returns cleanup function
    },
  };
});

// Mock useTheme
vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(() => ({
    theme: 'system',
    setTheme: vi.fn(),
    resolvedTheme: 'light',
  })),
}));

// Mock CountUp
vi.mock('@/Components/ui/count-up', () => ({
  CountUp: ({ end }: { end: number }) => <span>{end}</span>,
}));

// Mock Recharts
vi.mock('recharts', () => ({
  ResponsiveContainer: ({ children }: { children: React.ReactNode }) => (
    <div>{children}</div>
  ),
  AreaChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="area-chart">{children}</div>
  ),
  BarChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="bar-chart">{children}</div>
  ),
  PieChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="pie-chart">{children}</div>
  ),
  LineChart: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="line-chart">{children}</div>
  ),
  Area: () => null,
  Bar: () => null,
  Pie: () => null,
  Line: () => null,
  Cell: () => null,
  XAxis: () => null,
  YAxis: () => null,
  CartesianGrid: () => null,
  Tooltip: () => null,
  Legend: () => null,
}));

// Mock sonner (used by FeatureFlags and other pages)
vi.mock('sonner', () => ({
  toast: { success: vi.fn(), error: vi.fn() },
}));

// Static imports — vi.mock() calls above are hoisted before these by Vitest
import AuditLogsIndex from './AuditLogs/Index';
import AuditLogsShow from './AuditLogs/Show';
import BillingDashboard from './Billing/Dashboard';
import BillingShow from './Billing/Show';
import BillingSubscriptions from './Billing/Subscriptions';
import CacheIndex from './Cache/Index';
import Config from './Config';
import ContactSubmissionsIndex from './ContactSubmissions/Index';
import ContactSubmissionsShow from './ContactSubmissions/Show';
import DataHealth from './DataHealth';
import EmailSendLogsIndex from './EmailSendLogs/Index';
import FailedJobsIndex from './FailedJobs/Index';
import FailedJobShow from './FailedJobs/Show';
import FeatureFlagsIndex from './FeatureFlags/Index';
import FeedbackIndex from './Feedback/Index';
import FeedbackShow from './Feedback/Show';
import Health from './Health';
import NotificationsDashboard from './Notifications/Dashboard';
import NpsResponsesIndex from './NpsResponses/Index';
import RoadmapCreate from './Roadmap/Create';
import RoadmapIndex from './Roadmap/Index';
import ScheduleIndex from './Schedule/Index';
import SessionsIndex from './Sessions/Index';
import SocialAuthDashboard from './SocialAuth/Dashboard';
import System from './System';
import TokensDashboard from './Tokens/Dashboard';
import TokensIndex from './Tokens/Index';
import TwoFactorDashboard from './TwoFactor/Dashboard';
import UsersCreate from './Users/Create';
import UsersIndex from './Users/Index';
import UsersShow from './Users/Show';
import WebhooksDashboard from './Webhooks/Dashboard';
import WebhooksDeliveryDetail from './Webhooks/DeliveryDetail';
import WebhooksEndpoints from './Webhooks/Endpoints';
import WebhooksIncoming from './Webhooks/IncomingWebhooks';

beforeEach(() => {
  vi.clearAllMocks();
});

describe('Admin UI Smoke Tests', () => {
  describe('Users Pages', () => {
    it('renders Users Index page', () => {
      render(
        <UsersIndex
          users={{
            data: [
              {
                id: 1,
                name: 'Test User',
                email: 'test@example.com',
                email_verified_at: '2026-01-01',
                is_admin: false,
                created_at: '2026-01-01',
                deleted_at: null,
                last_login_at: null,
                tokens_count: 0,
                engagement_score: 0,
              },
            ],
            current_page: 1,
            per_page: 15,
            total: 1,
            last_page: 1,
            from: 1,
            to: 1,
          }}
          filters={{}}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Users', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Users Show page', () => {
      render(
        <UsersShow
          user={{
            id: 1,
            name: 'Test User',
            email: 'test@example.com',
            email_verified_at: '2026-01-01',
            is_admin: false,
            created_at: '2026-01-01',
            deleted_at: null,
            last_login_at: null,
            has_password: true,
            signup_source: 'direct',
            tokens_count: 0,
          }}
          recent_audit_logs={[]}
          subscription={null}
          stage_history={[]}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Test User', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Audit Logs Pages', () => {
    it('renders Audit Logs Index page', () => {
      render(
        <AuditLogsIndex
          logs={{
            data: [],
            current_page: 1,
            per_page: 50,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          eventTypes={['auth.login', 'auth.logout']}
          filters={{}}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Audit Logs', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Audit Logs Show page', () => {
      render(
        <AuditLogsShow
          auditLog={{
            id: 1,
            event: 'auth.login',
            user_id: 1,
            user_name: 'Test User',
            user_email: 'test@example.com',
            ip: '192.168.1.1',
            user_agent: 'Mozilla/5.0',
            data: {},
            created_at: '2026-02-13T10:00:00Z',
          }}
        />
      );

      expect(screen.getByText('auth.login')).toBeInTheDocument();
    });
  });

  describe('Billing Pages', () => {
    it('renders Billing Dashboard page', () => {
      render(
        <BillingDashboard
          stats={{
            active_subscriptions: 8,
            trialing: 1,
            past_due: 1,
            canceled: 2,
            scheduled_cancellations: 0,
            total_ever: 10,
            mrr: 8000,
            churn_rate: 0,
            trial_conversion_rate: 0,
            activation_rate: 0,
            activation_rate_all_time: 0,
            signup_to_paid_conversion: 0,
            cohort_conversion_30d: 0,
          }}
          tier_distribution={[]}
          status_breakdown={[]}
          growth_chart={[]}
          trial_stats={{ active_trials: 1, expiring_soon: 0 }}
          recent_events={[]}
          cohort_retention={[]}
          analyticsThresholds={{
            churn_rate: { warning: 5, critical: 10 },
            mrr_drop_percent: { warning: 10, critical: 20 },
            trial_conversion: { warning_below: 10, critical_below: 5 },
          }}
        />
      );

      expect(screen.getByText('Billing Overview')).toBeInTheDocument();
    });

    it('renders Billing Subscriptions page', () => {
      render(
        <BillingSubscriptions
          subscriptions={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          filters={{}}
          statuses={['active', 'canceled']}
          tiers={['free', 'pro', 'team']}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Subscriptions', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Billing Show page', () => {
      render(
        <BillingShow
          subscription={{
            id: 1,
            user_id: 1,
            user_name: 'Test User',
            user_email: 'test@example.com',
            stripe_id: 'sub_test123',
            stripe_status: 'active',
            tier: 'pro',
            quantity: 1,
            trial_ends_at: null,
            ends_at: null,
            created_at: '2026-01-01',
          }}
          items={[]}
          audit_logs={[]}
        />
      );

      expect(screen.getByText('sub_test123')).toBeInTheDocument();
    });
  });

  describe('Config & System Pages', () => {
    it('renders Config page', () => {
      render(
        <Config
          feature_flags={[
            { key: 'billing', enabled: true, env_var: 'FEATURE_BILLING' },
            { key: 'admin', enabled: true, env_var: 'FEATURE_ADMIN' },
          ]}
          warnings={[]}
          environment={{ APP_NAME: 'Test App', APP_ENV: 'testing' }}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Configuration', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders System page', () => {
      render(
        <System
          system={{
            php_version: '8.3.0',
            laravel_version: '12.0.0',
            node_version: '20.0.0',
            server: {
              os: 'Linux',
              server_software: 'nginx',
            },
            database: {
              driver: 'mysql',
              version: '8.0',
            },
            queue: {
              driver: 'redis',
              pending_jobs: 0,
              failed_jobs: 0,
            },
            packages: [],
          }}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'System Info', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Health page', () => {
      render(
        <Health
          health={{
            status: 'healthy',
            timestamp: '2026-02-13T10:00:00Z',
            checks: {
              database: {
                status: 'healthy',
                message: 'Connection successful',
                response_time_ms: 5,
              },
              cache: {
                status: 'healthy',
                message: 'Redis responding',
                response_time_ms: 2,
              },
            },
          }}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Health Status', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Feature Dashboards', () => {
    it('renders Notifications Dashboard', () => {
      render(
        <NotificationsDashboard
          stats={{
            total_sent: 100,
            unread: 25,
            read: 75,
            read_rate: 75,
            sent_last_7d: 50,
            by_type: [
              { type: 'info', count: 50 },
              { type: 'success', count: 50 },
            ],
          }}
          volume_chart={[]}
          recent_notifications={[]}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Notifications', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Social Auth Dashboard', () => {
      render(
        <SocialAuthDashboard
          stats={{
            total_connections: 50,
            google_connections: 30,
            github_connections: 20,
            by_provider: { google: 30, github: 20 },
          }}
        />
      );

      expect(screen.getByText('Social Authentication')).toBeInTheDocument();
    });

    it('renders Tokens Dashboard', () => {
      render(
        <TokensDashboard
          stats={{
            total_tokens: 25,
            active_tokens: 20,
            revoked_tokens: 5,
          }}
          most_active={[]}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'API Tokens', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders TwoFactor Dashboard', () => {
      render(
        <TwoFactorDashboard
          stats={{
            total_users_with_2fa: 40,
            percentage_with_2fa: 40,
            total_users: 100,
          }}
        />
      );

      expect(screen.getByText('Two-Factor Authentication')).toBeInTheDocument();
    });

    it('renders Webhooks Dashboard', () => {
      render(
        <WebhooksDashboard
          stats={{
            total_endpoints: 15,
            active_endpoints: 12,
            inactive_endpoints: 3,
            total_deliveries: 1000,
            successful_deliveries: 950,
            failed_deliveries: 50,
            incoming_by_provider: { github: 100, stripe: 200 },
          }}
          delivery_chart={[]}
          recent_failures={[]}
        />
      );

      expect(screen.getByText('Webhooks Overview')).toBeInTheDocument();
    });
  });

  describe('Failed Jobs Pages', () => {
    it('renders Failed Jobs Index', () => {
      render(
        <FailedJobsIndex
          jobs={{
            data: [
              {
                id: 1,
                uuid: 'abc-123-uuid',
                connection: 'redis',
                queue: 'default',
                payload_summary: 'App\\Jobs\\TestJob',
                failed_at: '2026-03-20T10:00:00Z',
                exception_summary: 'RuntimeException: Something went wrong',
              },
            ],
            current_page: 1,
            per_page: 25,
            total: 1,
            last_page: 1,
            from: 1,
            to: 1,
          }}
          queues={['default', 'emails']}
          filters={{}}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Failed Jobs', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Failed Jobs Show', () => {
      render(
        <FailedJobShow
          job={{
            id: 1,
            uuid: 'abc-123-uuid',
            connection: 'redis',
            queue: 'default',
            payload_summary: 'App\\Jobs\\TestJob',
            payload: '{"displayName":"App\\\\Jobs\\\\TestJob"}',
            exception: 'RuntimeException: Something went wrong\n  at line 1',
            failed_at: '2026-03-20T10:00:00Z',
          }}
        />
      );

      expect(screen.getByText('abc-123-uuid')).toBeInTheDocument();
    });
  });

  describe('Feature Flags Page', () => {
    it('renders Feature Flags Index', () => {
      render(
        <FeatureFlagsIndex
          flags={[
            {
              flag: 'billing',
              env_default: true,
              global_override: null,
              effective: true,
              user_override_count: 0,
              is_protected: false,
              is_route_dependent: false,
            },
            {
              flag: 'social_auth',
              env_default: false,
              global_override: true,
              effective: true,
              user_override_count: 2,
              is_protected: false,
              is_route_dependent: true,
            },
          ]}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Feature Flags', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Data Health Page', () => {
    it('renders Data Health', () => {
      render(
        <DataHealth
          checks={{
            orphaned_tokens: {
              status: 'ok',
              count: 0,
              description: 'No orphaned tokens found',
            },
            missing_settings: {
              status: 'warning',
              count: 3,
              description: 'Users missing default settings',
            },
          }}
          ran_at="2026-03-20T10:00:00Z"
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Data Health', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Cache Page', () => {
    it('renders Cache Index', () => {
      render(
        <CacheIndex
          cacheKeys={[
            { key: 'admin.dashboard_stats', name: 'Dashboard Stats', exists: true },
            { key: 'admin.billing_stats', name: 'Billing Stats', exists: false },
          ]}
          scopes={['admin', 'billing']}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Cache Management', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Contact Submissions Pages', () => {
    it('renders Contact Submissions Index', () => {
      render(
        <ContactSubmissionsIndex
          submissions={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          filters={{}}
          counts={{ new: 0, replied: 0, spam: 0 }}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Contact Submissions', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Contact Submissions Show', () => {
      render(
        <ContactSubmissionsShow
          submission={{
            id: 42,
            name: 'Jane Doe',
            email: 'jane@example.com',
            subject: 'Test Subject',
            message: 'Test message body',
            status: 'new',
            replied_at: null,
            created_at: '2026-03-20T10:00:00Z',
            updated_at: '2026-03-20T10:00:00Z',
          }}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Contact #42', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Email Send Logs Page', () => {
    it('renders Email Send Logs Index', () => {
      render(
        <EmailSendLogsIndex
          logs={{
            data: [],
            current_page: 1,
            per_page: 50,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          sequenceTypes={['onboarding', 'trial_ending']}
          filters={{}}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Email Send Logs', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Feedback Pages', () => {
    it('renders Feedback Index', () => {
      render(
        <FeedbackIndex
          feedback={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          filters={{}}
          counts={{ open: 0, in_review: 0, resolved: 0 }}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Feedback Inbox', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Feedback Show', () => {
      render(
        <FeedbackShow
          feedback={{
            id: 7,
            type: 'bug',
            status: 'open',
            priority: 'medium',
            message: 'Something is broken',
            admin_notes: null,
            created_at: '2026-03-20T10:00:00Z',
            user_id: 1,
            user_name: 'Test User',
            user_email: 'test@example.com',
          }}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Feedback #7', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('NPS Responses Page', () => {
    it('renders NPS Responses Index', () => {
      render(
        <NpsResponsesIndex
          responses={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          filters={{}}
          summary={{
            total: 0,
            promoters: 0,
            passives: 0,
            detractors: 0,
            nps_score: null,
          }}
          surveyTriggers={['post_signup', 'monthly']}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'NPS Responses', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Roadmap Pages', () => {
    it('renders Roadmap Index', () => {
      render(
        <RoadmapIndex
          entries={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          filters={{}}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Roadmap', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Roadmap Create', () => {
      render(<RoadmapCreate />);

      expect(
        screen.getByRole('heading', { name: 'New Roadmap Entry', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Schedule Page', () => {
    it('renders Schedule Index', () => {
      render(
        <ScheduleIndex
          tasks={[
            {
              command: 'inspire',
              expression: '0 * * * *',
              description: 'Display an inspiring quote',
              timezone: 'UTC',
              next_run_date: '2026-04-07T00:00:00Z',
            },
          ]}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Schedule Monitor', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Sessions Page', () => {
    it('renders Sessions Index', () => {
      render(
        <SessionsIndex
          sessions={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          driver="database"
          driverSupported={true}
          filters={{}}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Session Manager', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Sessions Index with unsupported driver warning', () => {
      render(
        <SessionsIndex
          sessions={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          driver="file"
          driverSupported={false}
          filters={{}}
        />
      );

      expect(screen.getByText('Session Driver Not Supported')).toBeInTheDocument();
    });
  });

  describe('Tokens Index Page', () => {
    it('renders Tokens Index', () => {
      render(
        <TokensIndex
          tokens={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          filters={{}}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'All API Tokens', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Users Create Page', () => {
    it('renders Users Create', () => {
      render(<UsersCreate isSuperAdmin={false} />);

      expect(
        screen.getByRole('heading', { name: 'Create User', level: 1 })
      ).toBeInTheDocument();
    });
  });

  describe('Webhooks Sub-pages', () => {
    it('renders Webhooks Endpoints', () => {
      render(
        <WebhooksEndpoints
          endpoints={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Webhook Endpoints', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Webhooks Incoming', () => {
      render(
        <WebhooksIncoming
          webhooks={{
            data: [],
            current_page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
          }}
          providers={['github', 'stripe']}
          filters={{}}
        />
      );

      expect(
        screen.getByRole('heading', { name: 'Incoming Webhooks', level: 1 })
      ).toBeInTheDocument();
    });

    it('renders Webhooks Delivery Detail', () => {
      render(
        <WebhooksDeliveryDetail
          delivery={{
            id: 1,
            uuid: 'del-uuid-123',
            event_type: 'user.created',
            payload: { userId: 1 },
            status: 'delivered',
            response_code: 200,
            response_body: '{"ok":true}',
            attempts: 1,
            delivered_at: '2026-03-20T10:01:00Z',
            created_at: '2026-03-20T10:00:00Z',
            endpoint_id: 5,
            endpoint_url: 'https://example.com/hook',
            endpoint_deleted: false,
            user_id: 1,
            user_name: 'Test User',
            user_email: 'test@example.com',
          }}
        />
      );

      expect(screen.getByText('del-uuid-123')).toBeInTheDocument();
    });

    it('renders Webhooks Delivery Detail with deleted endpoint indicator', () => {
      render(
        <WebhooksDeliveryDetail
          delivery={{
            id: 2,
            uuid: 'del-uuid-456',
            event_type: 'user.deleted',
            payload: { userId: 2 },
            status: 'delivered',
            response_code: 200,
            response_body: '{"ok":true}',
            attempts: 1,
            delivered_at: '2026-03-20T10:01:00Z',
            created_at: '2026-03-20T10:00:00Z',
            endpoint_id: null,
            endpoint_url: 'https://example.com/deleted-hook',
            endpoint_deleted: true,
            user_id: 1,
            user_name: 'Test User',
            user_email: 'test@example.com',
          }}
        />
      );

      expect(screen.getByText('Deleted')).toBeInTheDocument();
    });
  });
});
