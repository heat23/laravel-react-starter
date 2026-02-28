import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import Charts from './Charts';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    usePage: vi.fn(() => ({
      props: {
        auth: { user: { name: 'Test User', email: 'test@example.com' } },
        features: { notifications: false },
      },
    })),
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
  };
});

vi.mock('@/Layouts/DashboardLayout', () => ({
  default: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="dashboard-layout">{children}</div>
  ),
}));

vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(() => ({
    theme: 'system',
    setTheme: vi.fn(),
    resolvedTheme: 'light',
  })),
}));

// Mock Recharts components to avoid ResizeObserver issues in test environment
vi.mock('recharts', () => ({
  ResponsiveContainer: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="responsive-container">{children}</div>
  ),
  AreaChart: () => <div data-testid="area-chart" />,
  BarChart: () => <div data-testid="bar-chart" />,
  LineChart: () => <div data-testid="line-chart" />,
  PieChart: () => <div data-testid="pie-chart" />,
  Area: () => null,
  Bar: () => null,
  Line: () => null,
  Pie: () => null,
  Cell: () => null,
  XAxis: () => null,
  YAxis: () => null,
  CartesianGrid: () => null,
  Tooltip: () => null,
  Legend: () => null,
}));

describe('Charts Page', () => {
  it('renders within DashboardLayout', () => {
    render(<Charts />);

    expect(screen.getByTestId('dashboard-layout')).toBeInTheDocument();
  });

  it('sets the page title', () => {
    render(<Charts />);

    expect(document.querySelector('title')).toHaveTextContent('Charts');
  });

  it('renders the page heading', () => {
    render(<Charts />);

    expect(screen.getByRole('heading', { name: 'Charts', level: 1 })).toBeInTheDocument();
  });

  it('renders the description text', () => {
    render(<Charts />);

    expect(screen.getByText(/theme-aware chart components powered by recharts/i)).toBeInTheDocument();
  });

  it('renders all four chart sections', () => {
    render(<Charts />);

    expect(screen.getByText('Revenue vs Expenses')).toBeInTheDocument();
    expect(screen.getByText('Weekly Visitors')).toBeInTheDocument();
    expect(screen.getByText('Category Breakdown')).toBeInTheDocument();
    expect(screen.getByText('Traffic Trend')).toBeInTheDocument();
  });

  it('uses accessible section landmarks with aria-labelledby', () => {
    render(<Charts />);

    const sections = screen.getAllByRole('region');
    expect(sections).toHaveLength(4);
  });

  it('has proper heading hierarchy', () => {
    render(<Charts />);

    const h1 = screen.getByRole('heading', { level: 1 });
    expect(h1).toHaveTextContent('Charts');

    const h2s = screen.getAllByRole('heading', { level: 2 });
    expect(h2s).toHaveLength(4);
  });
});
