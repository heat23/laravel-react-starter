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
      },
    } as ReturnType<typeof usePage>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the dashboard page', () => {
      render(<Dashboard />);

      // Dashboard text appears multiple times (nav link, page title, etc.)
      const dashboardTexts = screen.getAllByText('Dashboard');
      expect(dashboardTexts.length).toBeGreaterThan(0);
    });

    it('renders the welcome subtitle', () => {
      render(<Dashboard />);

      expect(screen.getByText(/welcome to your application dashboard/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Stats cards tests
  // ============================================

  describe('stats cards', () => {
    it('renders Total Users card', () => {
      render(<Dashboard />);

      expect(screen.getByText('Total Users')).toBeInTheDocument();
      expect(screen.getByText('Active accounts')).toBeInTheDocument();
    });

    it('renders Revenue card', () => {
      render(<Dashboard />);

      expect(screen.getByText('Revenue')).toBeInTheDocument();
      expect(screen.getByText('This month')).toBeInTheDocument();
    });

    it('renders Active Sessions card', () => {
      render(<Dashboard />);

      expect(screen.getByText('Active Sessions')).toBeInTheDocument();
      expect(screen.getByText('Currently online')).toBeInTheDocument();
    });

    it('renders Growth card', () => {
      render(<Dashboard />);

      expect(screen.getByText('Growth')).toBeInTheDocument();
      expect(screen.getByText('vs last month')).toBeInTheDocument();
    });

    it('renders all four stat cards', () => {
      render(<Dashboard />);

      // All stats should have default "0" values
      expect(screen.getAllByText('0')).toHaveLength(2); // Total Users and Active Sessions
      expect(screen.getByText('$0')).toBeInTheDocument();
      expect(screen.getByText('0%')).toBeInTheDocument();
    });
  });

  // ============================================
  // Main content area tests
  // ============================================

  describe('main content area', () => {
    it('renders Overview section', () => {
      render(<Dashboard />);

      expect(screen.getByText('Overview')).toBeInTheDocument();
      expect(screen.getByText(/your activity overview for this period/i)).toBeInTheDocument();
    });

    it('renders analytics coming soon empty state', () => {
      render(<Dashboard />);

      expect(screen.getByText('Analytics Coming Soon')).toBeInTheDocument();
      expect(
        screen.getByText(/charts and insights will appear here once you have activity data/i),
      ).toBeInTheDocument();
    });

    it('renders Recent Activity section', () => {
      render(<Dashboard />);

      expect(screen.getByText('Recent Activity')).toBeInTheDocument();
      expect(screen.getByText(/latest actions in your account/i)).toBeInTheDocument();
    });

    it('renders no recent activity empty state', () => {
      render(<Dashboard />);

      expect(screen.getByText('No Recent Activity')).toBeInTheDocument();
      expect(
        screen.getByText(/your recent actions will appear here as you use the app/i),
      ).toBeInTheDocument();
    });
  });

  // ============================================
  // Layout integration tests
  // ============================================

  describe('layout integration', () => {
    it('uses DashboardLayout', () => {
      const { container } = render(<Dashboard />);

      // Dashboard should render within a layout container
      expect(container.querySelector('.min-h-screen')).toBeInTheDocument();
    });

    it('renders within container with proper spacing', () => {
      const { container } = render(<Dashboard />);

      expect(container.querySelector('.container')).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('sets the page title', () => {
      render(<Dashboard />);

      // The Head component is mocked to render a title element
      expect(document.querySelector('title')).toHaveTextContent('Dashboard');
    });

    it('has proper card structure', () => {
      render(<Dashboard />);

      // Cards should have headers with titles
      const cardTitles = screen.getAllByText(/Total Users|Revenue|Active Sessions|Growth/);
      expect(cardTitles.length).toBe(4);
    });
  });
});
