import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { usePage } from '@inertiajs/react';

import Pricing from './Pricing';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    usePage: vi.fn(),
    Head: ({ title, children }: { title: string; children?: React.ReactNode }) => (
      <>
        <title>{title}</title>
        {children}
      </>
    ),
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
    router: {
      post: vi.fn(),
    },
  };
});

vi.mock('@/Layouts/DashboardLayout', () => ({
  default: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="dashboard-layout">{children}</div>
  ),
}));

vi.mock('@/Components/layout/PageHeader', () => ({
  default: ({ title, subtitle }: { title: string; subtitle?: string }) => (
    <div data-testid="page-header">
      <h1>{title}</h1>
      {subtitle && <p>{subtitle}</p>}
    </div>
  ),
}));

vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(() => ({
    theme: 'system',
    setTheme: vi.fn(),
    resolvedTheme: 'light',
  })),
}));

const mockedUsePage = vi.mocked(usePage);

const baseTiers = {
  free: {
    name: 'Free',
    description: 'For individuals getting started',
    price: 0,
    features: ['1 project', 'Basic support'],
  },
  pro: {
    name: 'Pro',
    description: 'For growing teams',
    price: 29,
    price_annual: 290,
    stripe_price_id: 'price_pro_monthly',
    stripe_price_id_annual: 'price_pro_annual',
    features: ['Unlimited projects', 'Priority support'],
  },
  enterprise: {
    name: 'Enterprise',
    description: 'For large organizations',
    price: null,
    features: ['Custom integrations', 'Dedicated support'],
  },
};

function mockPageProps(overrides: Record<string, unknown> = {}) {
  mockedUsePage.mockReturnValue({
    props: {
      tiers: baseTiers,
      currentPlan: null,
      trial: null,
      trialEnabled: false,
      trialDays: 14,
      auth: { user: null },
      features: {},
      flash: {},
      errors: {},
      ...overrides,
    },
  } as ReturnType<typeof usePage>);
}

describe('Pricing Page', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockPageProps();
  });

  describe('rendering', () => {
    it('sets the page title', () => {
      render(<Pricing />);

      expect(document.querySelector('title')).toHaveTextContent('Pricing');
    });

    it('renders the page header', () => {
      render(<Pricing />);

      expect(screen.getByTestId('page-header')).toBeInTheDocument();
      expect(screen.getByRole('heading', { name: 'Pricing' })).toBeInTheDocument();
    });

    it('renders all plan tiers', () => {
      render(<Pricing />);

      // Tier names appear in CardTitle elements
      expect(screen.getAllByText('Free').length).toBeGreaterThan(0);
      expect(screen.getByText('Pro')).toBeInTheDocument();
      expect(screen.getByText('Enterprise')).toBeInTheDocument();
    });

    it('displays tier descriptions', () => {
      render(<Pricing />);

      expect(screen.getByText('For individuals getting started')).toBeInTheDocument();
      expect(screen.getByText('For growing teams')).toBeInTheDocument();
      expect(screen.getByText('For large organizations')).toBeInTheDocument();
    });

    it('displays tier features', () => {
      render(<Pricing />);

      expect(screen.getByText('1 project')).toBeInTheDocument();
      expect(screen.getByText('Unlimited projects')).toBeInTheDocument();
      expect(screen.getByText('Custom integrations')).toBeInTheDocument();
    });

    it('displays Free for $0 tier', () => {
      render(<Pricing />);

      // "Free" appears as both tier name and price label
      const freeElements = screen.getAllByText('Free');
      expect(freeElements.length).toBeGreaterThanOrEqual(2);
    });

    it('displays Custom for null price tier', () => {
      render(<Pricing />);

      expect(screen.getByText('Custom')).toBeInTheDocument();
    });
  });

  describe('guest user', () => {
    it('shows Get Started buttons for guests', () => {
      render(<Pricing />);

      const getStartedLinks = screen.getAllByRole('link', { name: /get started/i });
      expect(getStartedLinks.length).toBeGreaterThan(0);
    });

    it('links Get Started buttons to register page', () => {
      render(<Pricing />);

      const links = screen.getAllByRole('link', { name: /get started/i });
      links.forEach((link) => {
        expect(link).toHaveAttribute('href', '/register');
      });
    });

    it('shows trial CTA when trial is enabled', () => {
      mockPageProps({ trialEnabled: true, trialDays: 14 });

      render(<Pricing />);

      expect(screen.getByText(/start with a 14-day free pro trial/i)).toBeInTheDocument();
    });

    it('shows trial button text for pro tier when trial enabled', () => {
      mockPageProps({ trialEnabled: true, trialDays: 14 });

      render(<Pricing />);

      expect(screen.getByRole('link', { name: /start 14-day free trial/i })).toBeInTheDocument();
    });
  });

  describe('authenticated user', () => {
    it('shows current plan badge', () => {
      mockPageProps({
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com' } },
        currentPlan: 'pro',
      });

      render(<Pricing />);

      expect(screen.getByText('Current')).toBeInTheDocument();
    });

    it('shows Manage Billing for current plan', () => {
      mockPageProps({
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com' } },
        currentPlan: 'pro',
      });

      render(<Pricing />);

      expect(screen.getByRole('link', { name: /manage billing/i })).toBeInTheDocument();
    });

    it('shows Contact Sales for enterprise tier', () => {
      mockPageProps({
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com' } },
        currentPlan: 'free',
      });

      render(<Pricing />);

      expect(screen.getByRole('link', { name: /contact sales/i })).toBeInTheDocument();
    });

    it('uses DashboardLayout for authenticated users', () => {
      mockPageProps({
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com' } },
      });

      render(<Pricing />);

      expect(screen.getByTestId('dashboard-layout')).toBeInTheDocument();
    });

    it('shows active trial alert', () => {
      mockPageProps({
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com' } },
        trial: { active: true, daysRemaining: 7, endsAt: '2026-03-07' },
      });

      render(<Pricing />);

      expect(screen.getByText(/pro trial active/i)).toBeInTheDocument();
      expect(screen.getByText('7')).toBeInTheDocument();
    });
  });

  describe('billing period toggle', () => {
    it('renders monthly/annual toggle when annual pricing exists', () => {
      render(<Pricing />);

      expect(screen.getByText('Monthly')).toBeInTheDocument();
      expect(screen.getByText('Annual')).toBeInTheDocument();
    });

    it('shows annual savings percentage', () => {
      render(<Pricing />);

      // 29*12=348, annual=290, savings = 58/348 ≈ 17%
      expect(screen.getByText(/save 17%/i)).toBeInTheDocument();
    });

    it('switches to annual pricing on toggle', async () => {
      const user = userEvent.setup();

      render(<Pricing />);

      await user.click(screen.getByText('Annual'));

      expect(screen.getByText('$290/year')).toBeInTheDocument();
    });

    it('does not show toggle when no annual pricing available', () => {
      const tiersWithoutAnnual = {
        free: { ...baseTiers.free },
        pro: { ...baseTiers.pro, price_annual: null, stripe_price_id_annual: null },
        enterprise: { ...baseTiers.enterprise },
      };

      mockPageProps({ tiers: tiersWithoutAnnual });

      render(<Pricing />);

      expect(screen.queryByText('Annual')).not.toBeInTheDocument();
    });
  });

  describe('coming soon tier', () => {
    it('shows Coming Soon badge and disabled button', () => {
      const tiersWithComingSoon = {
        ...baseTiers,
        team: {
          name: 'Team',
          description: 'For teams',
          price: 49,
          coming_soon: true,
          features: ['Team collaboration'],
        },
      };

      mockPageProps({
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com' } },
        tiers: tiersWithComingSoon,
        currentPlan: 'free',
      });

      render(<Pricing />);

      // "Coming Soon" appears as badge and button text
      const comingSoonElements = screen.getAllByText('Coming Soon');
      expect(comingSoonElements.length).toBeGreaterThan(0);
      const comingSoonBtn = screen.getAllByRole('button', { name: /coming soon/i });
      expect(comingSoonBtn[0]).toBeDisabled();
    });
  });
});
