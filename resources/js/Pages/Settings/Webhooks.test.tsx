import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import Webhooks from './Webhooks';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    usePage: vi.fn(() => ({
      props: {
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com', has_password: true } },
        flash: {},
        errors: {},
        features: { twoFactor: false, billing: false, socialAuth: false, emailVerification: true, apiTokens: true, userSettings: true, notifications: false, onboarding: false, apiDocs: false, webhooks: true },
        notifications_unread_count: 0,
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

vi.mock('@/Components/layout/PageHeader', () => ({
  default: ({ title, subtitle, actions }: { title: string; subtitle?: string; actions?: React.ReactNode }) => (
    <div data-testid="page-header">
      <h1>{title}</h1>
      {subtitle && <p>{subtitle}</p>}
      {actions}
    </div>
  ),
}));

vi.mock('@/Components/ui/confirm-dialog', () => ({
  ConfirmDialog: () => null,
}));

// Mock fetch for endpoint loading
global.fetch = vi.fn().mockResolvedValue({
  ok: true,
  json: async () => [],
}) as typeof fetch;

describe('Webhooks Page', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => [],
    }) as typeof fetch;
  });

  it('renders the webhooks page with DashboardLayout', () => {
    render(<Webhooks available_events={['user.created', 'user.updated']} />);

    expect(screen.getByTestId('dashboard-layout')).toBeInTheDocument();
  });

  it('renders page header', () => {
    render(<Webhooks available_events={['user.created']} />);

    expect(screen.getByTestId('page-header')).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: /webhooks/i })).toBeInTheDocument();
  });

  it('renders add endpoint button', () => {
    render(<Webhooks available_events={['user.created']} />);

    expect(screen.getByRole('button', { name: /add endpoint/i })).toBeInTheDocument();
  });

  it('shows empty state when no endpoints', async () => {
    render(<Webhooks available_events={['user.created']} />);

    // Wait for the fetch to resolve and component to update
    const emptyMessage = await screen.findByText(/no webhook endpoints/i);
    expect(emptyMessage).toBeInTheDocument();
  });

  it('shows loading state initially', () => {
    // Don't resolve the fetch immediately
    (global.fetch as ReturnType<typeof vi.fn>).mockReturnValue(new Promise(() => {}));

    render(<Webhooks available_events={['user.created']} />);

    expect(screen.getByText(/loading endpoints/i)).toBeInTheDocument();
  });

  it('renders endpoint list when endpoints exist', async () => {
    const mockEndpoints = [
      {
        id: 1,
        url: 'https://example.com/webhook',
        events: ['user.created'],
        active: true,
        secret: 'whsec_test123',
      },
    ];

    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => mockEndpoints,
    }) as typeof fetch;

    render(<Webhooks available_events={['user.created']} />);

    expect(await screen.findByText('https://example.com/webhook')).toBeInTheDocument();
  });

  it('displays active status badge for active endpoints', async () => {
    const mockEndpoints = [
      {
        id: 1,
        url: 'https://example.com/webhook',
        events: ['user.created'],
        active: true,
        secret: 'whsec_test123',
      },
    ];

    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => mockEndpoints,
    }) as typeof fetch;

    render(<Webhooks available_events={['user.created']} />);

    expect(await screen.findByText('Active')).toBeInTheDocument();
  });

  it('displays event subscriptions for each endpoint', async () => {
    const mockEndpoints = [
      {
        id: 1,
        url: 'https://example.com/webhook',
        events: ['user.created', 'user.updated'],
        active: true,
        secret: 'whsec_test123',
      },
    ];

    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => mockEndpoints,
    }) as typeof fetch;

    render(<Webhooks available_events={['user.created', 'user.updated']} />);

    await screen.findByText('https://example.com/webhook');

    expect(screen.getByText('user.created')).toBeInTheDocument();
    expect(screen.getByText('user.updated')).toBeInTheDocument();
  });
});
