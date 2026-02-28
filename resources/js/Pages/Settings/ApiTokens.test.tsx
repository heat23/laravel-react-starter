import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import ApiTokens from './ApiTokens';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    usePage: vi.fn(() => ({
      props: {
        auth: { user: { id: 1, name: 'Test User', email: 'test@example.com', has_password: true } },
        flash: {},
        errors: {},
        features: { twoFactor: false, billing: false, socialAuth: false, emailVerification: true, apiTokens: true, userSettings: true, notifications: false, onboarding: false, apiDocs: false, webhooks: false },
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

vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(() => ({
    theme: 'system',
    setTheme: vi.fn(),
    resolvedTheme: 'light',
  })),
}));

const mockTokens = [
  {
    id: 1,
    name: 'Production Server',
    abilities: ['read', 'write'],
    last_used_at: '2026-02-27T10:00:00Z',
    expires_at: '2026-12-31T23:59:59Z',
    created_at: '2026-01-01T00:00:00Z',
  },
  {
    id: 2,
    name: 'CI Pipeline',
    abilities: ['read'],
    last_used_at: null,
    expires_at: null,
    created_at: '2026-02-15T00:00:00Z',
  },
];

describe('ApiTokens Page', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => [],
    }) as typeof fetch;
  });

  it('renders within DashboardLayout', () => {
    render(<ApiTokens />);

    expect(screen.getByTestId('dashboard-layout')).toBeInTheDocument();
  });

  it('sets the page title', () => {
    render(<ApiTokens />);

    expect(document.querySelector('title')).toHaveTextContent('API Tokens');
  });

  it('renders the page header', () => {
    render(<ApiTokens />);

    expect(screen.getByTestId('page-header')).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: /api tokens/i })).toBeInTheDocument();
  });

  it('renders Create token button in header', () => {
    render(<ApiTokens />);

    expect(screen.getByRole('button', { name: /create token/i })).toBeInTheDocument();
  });

  it('shows loading state initially', () => {
    (global.fetch as ReturnType<typeof vi.fn>).mockReturnValue(new Promise(() => {}));

    render(<ApiTokens />);

    expect(screen.getByText(/loading tokens/i)).toBeInTheDocument();
  });

  it('shows empty state when no tokens exist', async () => {
    render(<ApiTokens />);

    expect(await screen.findByText(/no api tokens/i)).toBeInTheDocument();
    expect(screen.getByText(/create a token to authenticate with the api/i)).toBeInTheDocument();
  });

  it('shows empty state Create token button', async () => {
    render(<ApiTokens />);

    await screen.findByText(/no api tokens/i);
    const buttons = screen.getAllByRole('button', { name: /create token/i });
    expect(buttons.length).toBeGreaterThanOrEqual(2); // header + empty state
  });

  it('renders token list when tokens exist', async () => {
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => mockTokens,
    }) as typeof fetch;

    render(<ApiTokens />);

    expect(await screen.findByText('Production Server')).toBeInTheDocument();
    expect(screen.getByText('CI Pipeline')).toBeInTheDocument();
  });

  it('displays token abilities as badges', async () => {
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => mockTokens,
    }) as typeof fetch;

    render(<ApiTokens />);

    await screen.findByText('Production Server');
    // "read" appears on both tokens (Production Server has read+write, CI Pipeline has read)
    const readBadges = screen.getAllByText('read');
    expect(readBadges.length).toBe(2);
    expect(screen.getByText('write')).toBeInTheDocument();
  });

  it('shows revoke button with accessible label for each token', async () => {
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => mockTokens,
    }) as typeof fetch;

    render(<ApiTokens />);

    await screen.findByText('Production Server');
    expect(screen.getByRole('button', { name: /revoke token production server/i })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /revoke token ci pipeline/i })).toBeInTheDocument();
  });

  it('displays last used date when available', async () => {
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => mockTokens,
    }) as typeof fetch;

    render(<ApiTokens />);

    await screen.findByText('Production Server');
    expect(screen.getByText(/last used/i)).toBeInTheDocument();
  });

  it('displays expiration date when set', async () => {
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => mockTokens,
    }) as typeof fetch;

    render(<ApiTokens />);

    await screen.findByText('Production Server');
    expect(screen.getByText(/expires/i)).toBeInTheDocument();
  });

  it('opens create dialog when Create token button is clicked', async () => {
    const user = userEvent.setup();

    render(<ApiTokens />);

    await screen.findByText(/no api tokens/i);
    const headerButton = screen.getAllByRole('button', { name: /create token/i })[0];
    await user.click(headerButton);

    expect(screen.getByText('Create API Token')).toBeInTheDocument();
    expect(screen.getByLabelText(/token name/i)).toBeInTheDocument();
  });

  it('renders permission checkboxes in create dialog', async () => {
    const user = userEvent.setup();

    render(<ApiTokens />);

    await screen.findByText(/no api tokens/i);
    await user.click(screen.getAllByRole('button', { name: /create token/i })[0]);

    expect(screen.getByText('Read')).toBeInTheDocument();
    expect(screen.getByText('Write')).toBeInTheDocument();
    expect(screen.getByText('Delete')).toBeInTheDocument();
  });

  it('fetches tokens on mount', () => {
    render(<ApiTokens />);

    expect(global.fetch).toHaveBeenCalledWith('/api/tokens', expect.objectContaining({
      headers: { Accept: 'application/json' },
    }));
  });
});
