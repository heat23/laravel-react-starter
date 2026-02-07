import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm } from '@inertiajs/react';

import TwoFactorChallenge from './TwoFactorChallenge';

const mockPost = vi.fn();
const mockSetData = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { code: '', recovery_code: '' },
      setData: mockSetData,
      post: mockPost,
      processing: false,
      errors: {},
    })),
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
    Head: ({ title }: { title: string }) => <title>{title}</title>,
  };
});

vi.mock('@/Layouts/AuthLayout', () => ({
  default: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="auth-layout">{children}</div>
  ),
}));

vi.mock('@/Components/ui/input-otp', () => ({
  InputOTP: ({ children, onChange, ...props }: { children: React.ReactNode; onChange?: (val: string) => void; [key: string]: unknown }) => (
    <div data-testid="input-otp" {...props}>
      <input
        data-testid="otp-input"
        onChange={(e) => onChange?.(e.target.value)}
        aria-label="Authentication code"
      />
      {children}
    </div>
  ),
  InputOTPGroup: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
  InputOTPSlot: ({ index }: { index: number }) => <div data-testid={`otp-slot-${index}`} />,
}));

const mockedUseForm = vi.mocked(useForm);

describe('TwoFactorChallenge Page', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    mockedUseForm.mockReturnValue({
      data: { code: '', recovery_code: '' },
      setData: mockSetData,
      post: mockPost,
      processing: false,
      errors: {},
    } as ReturnType<typeof useForm>);
  });

  describe('rendering', () => {
    it('renders the two-factor challenge page', () => {
      render(<TwoFactorChallenge />);

      expect(screen.getByRole('heading', { name: /two-factor authentication/i })).toBeInTheDocument();
    });

    it('uses AuthLayout', () => {
      render(<TwoFactorChallenge />);

      expect(screen.getByTestId('auth-layout')).toBeInTheDocument();
    });

    it('renders OTP input by default', () => {
      render(<TwoFactorChallenge />);

      expect(screen.getByTestId('input-otp')).toBeInTheDocument();
    });

    it('renders verify button', () => {
      render(<TwoFactorChallenge />);

      expect(screen.getByRole('button', { name: /verify/i })).toBeInTheDocument();
    });

    it('renders recovery code toggle', () => {
      render(<TwoFactorChallenge />);

      expect(screen.getByRole('button', { name: /use a recovery code instead/i })).toBeInTheDocument();
    });

    it('renders return to login link', () => {
      render(<TwoFactorChallenge />);

      expect(screen.getByText(/return to login/i)).toBeInTheDocument();
    });

    it('shows authenticator app instruction', () => {
      render(<TwoFactorChallenge />);

      expect(screen.getByText(/6-digit code from your authenticator app/i)).toBeInTheDocument();
    });
  });

  describe('recovery code mode', () => {
    it('switches to recovery code input when toggle clicked', async () => {
      render(<TwoFactorChallenge />);

      await user.click(screen.getByRole('button', { name: /use a recovery code instead/i }));

      expect(screen.getByLabelText(/recovery code/i)).toBeInTheDocument();
    });

    it('shows recovery code instruction text', async () => {
      render(<TwoFactorChallenge />);

      await user.click(screen.getByRole('button', { name: /use a recovery code instead/i }));

      expect(screen.getByText(/emergency recovery codes/i)).toBeInTheDocument();
    });

    it('shows toggle back to authentication code', async () => {
      render(<TwoFactorChallenge />);

      await user.click(screen.getByRole('button', { name: /use a recovery code instead/i }));

      expect(screen.getByRole('button', { name: /use authentication code instead/i })).toBeInTheDocument();
    });

    it('switches back to OTP mode', async () => {
      render(<TwoFactorChallenge />);

      await user.click(screen.getByRole('button', { name: /use a recovery code instead/i }));
      await user.click(screen.getByRole('button', { name: /use authentication code instead/i }));

      expect(screen.getByTestId('input-otp')).toBeInTheDocument();
    });
  });

  describe('form submission', () => {
    it('calls post on form submit', () => {
      render(<TwoFactorChallenge />);

      const form = screen.getByRole('button', { name: /verify/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPost).toHaveBeenCalled();
    });

    it('posts to two-factor.challenge route', () => {
      render(<TwoFactorChallenge />);

      const form = screen.getByRole('button', { name: /verify/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPost).toHaveBeenCalledWith(expect.stringContaining('two-factor'));
    });
  });

  describe('error display', () => {
    it('displays error message when code is invalid', () => {
      mockedUseForm.mockReturnValue({
        data: { code: '', recovery_code: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { code: 'The provided two-factor code is invalid.' },
      } as ReturnType<typeof useForm>);

      render(<TwoFactorChallenge />);

      expect(screen.getByText(/the provided two-factor code is invalid/i)).toBeInTheDocument();
    });
  });

  describe('processing state', () => {
    it('disables verify button when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { code: '', recovery_code: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<TwoFactorChallenge />);

      const submitButton = screen.getByRole('button', { name: /verify/i });
      expect(submitButton).toBeDisabled();
    });
  });
});
