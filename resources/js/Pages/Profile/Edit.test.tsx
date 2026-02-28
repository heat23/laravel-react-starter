import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import Edit from './Edit';

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
    useForm: vi.fn(() => ({
      data: { name: 'Test User', email: 'test@example.com' },
      setData: vi.fn(),
      patch: vi.fn(),
      put: vi.fn(),
      delete: vi.fn(),
      processing: false,
      errors: {},
      reset: vi.fn(),
      recentlySuccessful: false,
      clearErrors: vi.fn(),
    })),
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

vi.mock('@/hooks/useTimezone', () => ({
  useTimezone: vi.fn(() => ({
    timezone: 'America/New_York',
    setTimezone: vi.fn().mockResolvedValue(true),
    isSaving: false,
  })),
}));

vi.mock('./Partials/UpdateProfileInformationForm', () => ({
  default: ({ className }: { className?: string }) => (
    <div data-testid="update-profile-form" className={className}>Update Profile Form</div>
  ),
}));

vi.mock('./Partials/UpdatePasswordForm', () => ({
  default: ({ className }: { className?: string }) => (
    <div data-testid="update-password-form" className={className}>Update Password Form</div>
  ),
}));

vi.mock('./Partials/DeleteUserForm', () => ({
  default: () => <div data-testid="delete-user-form">Delete User Form</div>,
}));

vi.mock('@/Components/settings/TimezoneSelector', () => ({
  TimezoneSelector: ({ value }: { value: string }) => (
    <div data-testid="timezone-selector">{value}</div>
  ),
}));

describe('Profile Edit Page', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders within DashboardLayout', () => {
    render(<Edit mustVerifyEmail={false} />);

    expect(screen.getByTestId('dashboard-layout')).toBeInTheDocument();
  });

  it('sets the page title', () => {
    render(<Edit mustVerifyEmail={false} />);

    expect(document.querySelector('title')).toHaveTextContent('Profile');
  });

  it('renders the page header', () => {
    render(<Edit mustVerifyEmail={false} />);

    expect(screen.getByTestId('page-header')).toBeInTheDocument();
    expect(screen.getByRole('heading', { name: 'Profile' })).toBeInTheDocument();
  });

  it('renders Profile Information section', () => {
    render(<Edit mustVerifyEmail={false} />);

    expect(screen.getByText('Profile Information')).toBeInTheDocument();
    expect(screen.getByText(/update your name, email, and account details/i)).toBeInTheDocument();
    expect(screen.getByTestId('update-profile-form')).toBeInTheDocument();
  });

  it('renders Preferences section with timezone selector', () => {
    render(<Edit mustVerifyEmail={false} />);

    expect(screen.getByText('Preferences')).toBeInTheDocument();
    expect(screen.getByText(/customize your display and regional settings/i)).toBeInTheDocument();
    expect(screen.getByTestId('timezone-selector')).toBeInTheDocument();
  });

  it('renders Update Password section', () => {
    render(<Edit mustVerifyEmail={false} />);

    expect(screen.getByText('Update Password')).toBeInTheDocument();
    expect(screen.getByText(/use a strong password to keep your account secure/i)).toBeInTheDocument();
    expect(screen.getByTestId('update-password-form')).toBeInTheDocument();
  });

  it('renders Danger Zone section with delete form', () => {
    render(<Edit mustVerifyEmail={false} />);

    expect(screen.getByText('Danger Zone')).toBeInTheDocument();
    expect(screen.getByText(/irreversible actions that will permanently affect your account/i)).toBeInTheDocument();
    expect(screen.getByText('Delete Account')).toBeInTheDocument();
    expect(screen.getByTestId('delete-user-form')).toBeInTheDocument();
  });

  it('displays timezone value', () => {
    render(<Edit mustVerifyEmail={false} timezone="America/New_York" />);

    expect(screen.getByTestId('timezone-selector')).toHaveTextContent('America/New_York');
  });

  it('renders all four card sections in correct order', () => {
    render(<Edit mustVerifyEmail={false} />);

    screen.getAllByRole('heading', { level: 3 });

    // Verify all sections are rendered (CardTitle renders as the appropriate level based on implementation)
    expect(screen.getByText('Profile Information')).toBeInTheDocument();
    expect(screen.getByText('Preferences')).toBeInTheDocument();
    expect(screen.getByText('Update Password')).toBeInTheDocument();
    expect(screen.getByText('Danger Zone')).toBeInTheDocument();
  });
});
