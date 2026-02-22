import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm, usePage } from '@inertiajs/react';

import Security from './Security';

const mockPost = vi.fn();
const mockDelete = vi.fn();
const mockSetData = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: {},
      setData: mockSetData,
      post: mockPost,
      delete: mockDelete,
      processing: false,
      errors: {},
    })),
    usePage: vi.fn(() => ({
      props: {
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com', has_password: true } },
        flash: {},
        errors: {},
        features: { twoFactor: true, billing: false, socialAuth: false, emailVerification: true, apiTokens: true, userSettings: true, notifications: false, onboarding: false, apiDocs: false },
        notifications_unread_count: 0,
      },
    })),
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    router: { post: vi.fn() },
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

vi.mock('@/Components/ui/input-otp', () => ({
  InputOTP: ({ children, onChange, ...props }: { children: React.ReactNode; onChange?: (val: string) => void; [key: string]: unknown }) => (
    <div data-testid="input-otp" {...props}>
      <input
        data-testid="otp-input"
        onChange={(e) => onChange?.(e.target.value)}
        aria-label="Verification code"
      />
      {children}
    </div>
  ),
  InputOTPGroup: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
  InputOTPSlot: ({ index }: { index: number }) => <div data-testid={`otp-slot-${index}`} />,
}));

vi.mock('@/Components/ui/confirm-dialog', () => ({
  ConfirmDialog: ({ open, title, description }: { open: boolean; title: string; description: React.ReactNode }) => (
    open ? (
      <div data-testid="confirm-dialog">
        <h2>{title}</h2>
        <div>{description}</div>
      </div>
    ) : null
  ),
}));

const mockedUseForm = vi.mocked(useForm);
const mockedUsePage = vi.mocked(usePage);

describe('Security Page', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    mockedUseForm.mockReturnValue({
      data: {},
      setData: mockSetData,
      post: mockPost,
      delete: mockDelete,
      processing: false,
      errors: {},
    } as ReturnType<typeof useForm>);
    mockedUsePage.mockReturnValue({
      props: {
        auth: { user: { id: 1, name: 'Test', email: 'test@example.com', has_password: true } },
        flash: {},
        errors: {},
        features: { twoFactor: true, billing: false, socialAuth: false, emailVerification: true, apiTokens: true, userSettings: true, notifications: false, onboarding: false, apiDocs: false },
        notifications_unread_count: 0,
      },
    } as ReturnType<typeof usePage>);
  });

  describe('not-enabled state', () => {
    it('renders the security page with DashboardLayout', () => {
      render(<Security enabled={false} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByTestId('dashboard-layout')).toBeInTheDocument();
    });

    it('renders page header', () => {
      render(<Security enabled={false} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByTestId('page-header')).toBeInTheDocument();
      expect(screen.getByRole('heading', { name: /security/i })).toBeInTheDocument();
    });

    it('shows not enabled badge', () => {
      render(<Security enabled={false} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByText(/not enabled/i)).toBeInTheDocument();
    });

    it('shows enable button', () => {
      render(<Security enabled={false} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByRole('button', { name: /enable two-factor authentication/i })).toBeInTheDocument();
    });

    it('submits enable form', async () => {
      render(<Security enabled={false} qr_code={null} secret={null} recovery_codes={null} />);

      const enableButton = screen.getByRole('button', { name: /enable two-factor authentication/i });
      await user.click(enableButton);

      expect(mockPost).toHaveBeenCalled();
    });
  });

  describe('setup state', () => {
    it('shows QR code when in setup', () => {
      render(
        <Security
          enabled={false}
          qr_code='<svg>mock-qr</svg>'
          secret="ABCDEF123456"
          recovery_codes={null}
        />
      );

      expect(screen.getByText(/scan the qr code/i)).toBeInTheDocument();
    });

    it('shows manual entry key', () => {
      render(
        <Security
          enabled={false}
          qr_code='<svg>mock-qr</svg>'
          secret="ABCDEF123456"
          recovery_codes={null}
        />
      );

      expect(screen.getByText('ABCDEF123456')).toBeInTheDocument();
    });

    it('shows verification code input', () => {
      render(
        <Security
          enabled={false}
          qr_code='<svg>mock-qr</svg>'
          secret="ABCDEF123456"
          recovery_codes={null}
        />
      );

      expect(screen.getByTestId('input-otp')).toBeInTheDocument();
    });

    it('shows confirm button', () => {
      render(
        <Security
          enabled={false}
          qr_code='<svg>mock-qr</svg>'
          secret="ABCDEF123456"
          recovery_codes={null}
        />
      );

      expect(screen.getByRole('button', { name: /confirm and enable/i })).toBeInTheDocument();
    });

    it('shows copy secret button', () => {
      render(
        <Security
          enabled={false}
          qr_code='<svg>mock-qr</svg>'
          secret="ABCDEF123456"
          recovery_codes={null}
        />
      );

      expect(screen.getByRole('button', { name: /copy secret key/i })).toBeInTheDocument();
    });

    it('sanitizes QR code HTML to prevent XSS', async () => {
      const DOMPurify = await import('dompurify');
      const sanitizeSpy = vi.spyOn(DOMPurify.default, 'sanitize');

      const maliciousQr = '<svg><rect width="100" height="100"/></svg><script>alert("xss")</script>';
      render(
        <Security
          enabled={false}
          qr_code={maliciousQr}
          secret="ABCDEF123456"
          recovery_codes={null}
        />
      );

      expect(sanitizeSpy).toHaveBeenCalledWith(
        maliciousQr,
        { USE_PROFILES: { svg: true, svgFilters: true } },
      );

      sanitizeSpy.mockRestore();
    });
  });

  describe('enabled state', () => {
    it('shows enabled badge', () => {
      render(<Security enabled={true} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByText('Enabled')).toBeInTheDocument();
    });

    it('shows protection message', () => {
      render(<Security enabled={true} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByText(/account is protected/i)).toBeInTheDocument();
    });

    it('shows view recovery codes button', () => {
      render(<Security enabled={true} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByRole('button', { name: /view recovery codes/i })).toBeInTheDocument();
    });

    it('shows regenerate codes button', () => {
      render(<Security enabled={true} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByRole('button', { name: /regenerate codes/i })).toBeInTheDocument();
    });

    it('shows disable button', () => {
      render(<Security enabled={true} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByRole('button', { name: /disable/i })).toBeInTheDocument();
    });

    it('opens confirm dialog when disable clicked', async () => {
      render(<Security enabled={true} qr_code={null} secret={null} recovery_codes={null} />);

      await user.click(screen.getByRole('button', { name: /disable/i }));

      expect(screen.getByTestId('confirm-dialog')).toBeInTheDocument();
    });
  });

  describe('flash messages', () => {
    it('shows success flash message', () => {
      mockedUsePage.mockReturnValue({
        props: {
          auth: { user: { id: 1, name: 'Test', email: 'test@example.com', has_password: true } },
          flash: { success: 'Two-factor authentication has been enabled.' },
          errors: {},
          features: { twoFactor: true, billing: false, socialAuth: false, emailVerification: true, apiTokens: true, userSettings: true, notifications: false, onboarding: false, apiDocs: false },
          notifications_unread_count: 0,
        },
      } as ReturnType<typeof usePage>);

      render(<Security enabled={true} qr_code={null} secret={null} recovery_codes={null} />);

      expect(screen.getByText(/two-factor authentication has been enabled/i)).toBeInTheDocument();
    });
  });
});
