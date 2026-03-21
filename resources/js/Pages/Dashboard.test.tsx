import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { usePage } from '@inertiajs/react';

import Dashboard from './Dashboard';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    usePage: vi.fn(() => ({
      props: {
        auth: {
          user: {
            name: 'Test User',
            email: 'test@example.com',
          },
        },
        features: {
          notifications: false,
        },
      },
    })),
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
  };
});

// Mock the useTheme hook to avoid needing ThemeProvider
vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(() => ({
    theme: 'system',
    setTheme: vi.fn(),
    resolvedTheme: 'light',
  })),
}));

const mockedUsePage = vi.mocked(usePage);

const defaultStats = {
  days_since_signup: 5,
  health_score: 60,
  email_verified: true,
  has_subscription: false,
  plan_name: 'Free',
  settings_count: 2,
  tokens_count: 1,
};

const defaultRecentActivity: { event: string; created_at: string }[] = [];

describe('Dashboard', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            name: 'Test User',
            email: 'test@example.com',
          },
        },
        features: {
          notifications: false,
        },
      },
    } as ReturnType<typeof usePage>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the dashboard page', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      const dashboardTexts = screen.getAllByText('Dashboard');
      expect(dashboardTexts.length).toBeGreaterThan(0);
    });

    it('renders the welcome subtitle', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(
        screen.getByText(/complete setup to unlock all features/i)
      ).toBeInTheDocument();
    });
  });

  // ============================================
  // Stats cards tests
  // ============================================

  describe('stats cards', () => {
    it('renders Account Health card', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Account Health')).toBeInTheDocument();
      expect(screen.getByText('60/100')).toBeInTheDocument();
      expect(screen.getByText('Moderate')).toBeInTheDocument();
    });

    it('renders Plan card', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Plan')).toBeInTheDocument();
      expect(screen.getByText('Free')).toBeInTheDocument();
    });

    it('renders Settings card', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Settings')).toBeInTheDocument();
      expect(screen.getByText('Preferences configured')).toBeInTheDocument();
    });

    it('renders API Tokens card', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('API Tokens')).toBeInTheDocument();
      expect(screen.getByText('Active tokens')).toBeInTheDocument();
    });

    it('renders all four stat cards', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Account Health')).toBeInTheDocument();
      expect(screen.getByText('Plan')).toBeInTheDocument();
      expect(screen.getByText('Settings')).toBeInTheDocument();
      expect(screen.getByText('API Tokens')).toBeInTheDocument();
    });

    it('shows healthy label for high scores', () => {
      render(<Dashboard stats={{ ...defaultStats, health_score: 85 }} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Healthy')).toBeInTheDocument();
    });

    it('shows getting started label for low scores', () => {
      render(<Dashboard stats={{ ...defaultStats, health_score: 10 }} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Getting Started')).toBeInTheDocument();
    });
  });

  // ============================================
  // Account setup tests
  // ============================================

  describe('account setup', () => {
    it('renders Account Setup section', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Account Setup')).toBeInTheDocument();
    });

    it('renders Recent Activity section', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Recent Activity')).toBeInTheDocument();
      expect(
        screen.getByText(/latest actions in your account/i)
      ).toBeInTheDocument();
    });

    it('shows setup items with completion status', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('Verify your email address')).toBeInTheDocument();
      expect(
        screen.getByText('Configure your preferences')
      ).toBeInTheDocument();
      expect(screen.getByText('Create an API token')).toBeInTheDocument();
      expect(screen.getByText('Choose a plan')).toBeInTheDocument();
    });

    it('shows welcome message for new users', () => {
      render(<Dashboard stats={{ ...defaultStats, days_since_signup: 0 }} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText(/account created today/i)).toBeInTheDocument();
    });

    it('shows empty state for non-new users', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(screen.getByText('No activity recorded yet.')).toBeInTheDocument();
    });
  });

  // ============================================
  // Layout integration tests
  // ============================================

  describe('layout integration', () => {
    it('uses DashboardLayout', () => {
      const { container } = render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(container.querySelector('.min-h-screen')).toBeInTheDocument();
    });

    it('renders within container with proper spacing', () => {
      const { container } = render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(container.querySelector('.container')).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('sets the page title', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      expect(document.querySelector('title')).toHaveTextContent('Dashboard');
    });

    it('has proper card structure', () => {
      render(<Dashboard stats={defaultStats} recent_activity={defaultRecentActivity} />);

      const cardTitles = screen.getAllByText(
        /Account Health|Plan|Settings|API Tokens/
      );
      expect(cardTitles.length).toBe(4);
    });
  });
});
