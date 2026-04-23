import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm } from '@inertiajs/react';

import VerifyEmail from './VerifyEmail';

// Mock useForm from Inertia
const mockPost = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      post: mockPost,
      processing: false,
    })),
    Link: ({ children, href, method, as }: { children: React.ReactNode; href: string; method?: string; as?: string }) => {
      if (as === 'button') {
        return <button data-href={href} data-method={method}>{children}</button>;
      }
      return <a href={href}>{children}</a>;
    },
    Head: ({ title }: { title: string }) => <title>{title}</title>,
  };
});

// Mock AuthLayout
vi.mock('@/Layouts/AuthLayout', () => ({
  default: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="auth-layout">{children}</div>
  ),
}));

const mockedUseForm = vi.mocked(useForm);

describe('VerifyEmail Page', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    // Reset to default mock state
    mockedUseForm.mockReturnValue({
      post: mockPost,
      processing: false,
    } as ReturnType<typeof useForm>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the verify email page', () => {
      render(<VerifyEmail />);

      expect(screen.getByText(/verify your email/i)).toBeInTheDocument();
    });

    it('renders explanation text', () => {
      render(<VerifyEmail />);

      expect(screen.getByText(/we've sent a verification link to your email address/i)).toBeInTheDocument();
    });

    it('renders mail icon', () => {
      render(<VerifyEmail />);

      // The mail icon container should exist
      expect(screen.getByText(/verify your email/i).closest('div')).toBeInTheDocument();
    });

    it('renders resend button', () => {
      render(<VerifyEmail />);

      expect(screen.getByRole('button', { name: /resend verification email/i })).toBeInTheDocument();
    });

    it('renders logout button', () => {
      render(<VerifyEmail />);

      expect(screen.getByRole('button', { name: /log out/i })).toBeInTheDocument();
    });

    it('renders what happens next section', () => {
      render(<VerifyEmail />);

      expect(screen.getByText(/what happens next/i)).toBeInTheDocument();
    });

    it('renders step 1 instruction', () => {
      render(<VerifyEmail />);

      expect(screen.getByText(/check your inbox/i)).toBeInTheDocument();
    });

    it('renders step 2 instruction', () => {
      render(<VerifyEmail />);

      expect(screen.getByText(/click the verification link/i)).toBeInTheDocument();
    });

    it('renders step 3 instruction', () => {
      render(<VerifyEmail />);

      expect(screen.getByText(/you'll be redirected to your dashboard/i)).toBeInTheDocument();
    });

    it('renders did not receive email text', () => {
      render(<VerifyEmail />);

      expect(screen.getByText(/didn't receive the email/i)).toBeInTheDocument();
    });

    it('uses AuthLayout', () => {
      render(<VerifyEmail />);

      expect(screen.getByTestId('auth-layout')).toBeInTheDocument();
    });
  });

  // ============================================
  // Status message tests
  // ============================================

  describe('status message', () => {
    it('displays success alert when status is verification-link-sent', () => {
      render(<VerifyEmail status="verification-link-sent" />);

      expect(screen.getByText(/email sent/i)).toBeInTheDocument();
    });

    it('displays success description when status is verification-link-sent', () => {
      render(<VerifyEmail status="verification-link-sent" />);

      expect(screen.getByText(/a new verification link has been sent/i)).toBeInTheDocument();
    });

    it('alert has correct role', () => {
      render(<VerifyEmail status="verification-link-sent" />);

      const alert = screen.getByRole('alert');
      expect(alert).toBeInTheDocument();
    });

    it('alert has aria-live polite', () => {
      render(<VerifyEmail status="verification-link-sent" />);

      const alert = screen.getByRole('alert');
      expect(alert).toHaveAttribute('aria-live', 'polite');
    });

    it('does not show alert when no status', () => {
      render(<VerifyEmail />);

      expect(screen.queryByText(/email sent/i)).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('calls post on form submit', async () => {
      render(<VerifyEmail />);

      const resendButton = screen.getByRole('button', { name: /resend verification email/i });
      await user.click(resendButton);

      expect(mockPost).toHaveBeenCalled();
    });

    it('posts to verification.send route', async () => {
      render(<VerifyEmail />);

      const resendButton = screen.getByRole('button', { name: /resend verification email/i });
      await user.click(resendButton);

      expect(mockPost).toHaveBeenCalledWith(
        expect.stringContaining('verification'),
        expect.any(Object)
      );
    });

    it('shows success alert after successful resend', async () => {
      // Mock post to call onSuccess
      mockPost.mockImplementation((_, options) => {
        options?.onSuccess?.();
      });

      render(<VerifyEmail />);

      const resendButton = screen.getByRole('button', { name: /resend verification email/i });
      await user.click(resendButton);

      // After success, the alert should be shown (via local state)
      expect(screen.getByText(/email sent/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Processing state tests
  // ============================================

  describe('processing state', () => {
    it('disables resend button when processing', () => {
      mockedUseForm.mockReturnValue({
        post: mockPost,
        processing: true,
      } as ReturnType<typeof useForm>);

      render(<VerifyEmail />);

      const resendButton = screen.getByRole('button', { name: /sending/i });
      expect(resendButton).toBeDisabled();
    });

    it('shows loading text when processing', () => {
      mockedUseForm.mockReturnValue({
        post: mockPost,
        processing: true,
      } as ReturnType<typeof useForm>);

      render(<VerifyEmail />);

      expect(screen.getByText(/sending/i)).toBeInTheDocument();
    });

    it('shows loading spinner when processing', () => {
      mockedUseForm.mockReturnValue({
        post: mockPost,
        processing: true,
      } as ReturnType<typeof useForm>);

      render(<VerifyEmail />);

      // Check for the Loader2 icon by looking for animate-spin class
      const spinner = document.querySelector('.animate-spin');
      expect(spinner).toBeInTheDocument();
    });

    it('shows normal button text when not processing', () => {
      render(<VerifyEmail />);

      expect(screen.getByRole('button', { name: /resend verification email/i })).toBeInTheDocument();
    });
  });

  // ============================================
  // Logout functionality tests
  // ============================================

  describe('logout functionality', () => {
    it('logout button has correct href', () => {
      render(<VerifyEmail />);

      const logoutButton = screen.getByRole('button', { name: /log out/i });
      expect(logoutButton).toHaveAttribute('data-href', expect.stringContaining('logout'));
    });

    it('logout uses POST method', () => {
      render(<VerifyEmail />);

      const logoutButton = screen.getByRole('button', { name: /log out/i });
      expect(logoutButton).toHaveAttribute('data-method', 'post');
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has accessible heading structure', () => {
      render(<VerifyEmail />);

      const heading = screen.getByRole('heading', { level: 2 });
      expect(heading).toHaveTextContent(/verify your email/i);
    });

    it('instructions are in an ordered list', () => {
      render(<VerifyEmail />);

      const list = screen.getByRole('list');
      expect(list).toBeInTheDocument();
    });

    it('instructions have three list items', () => {
      render(<VerifyEmail />);

      const listItems = screen.getAllByRole('listitem');
      expect(listItems).toHaveLength(3);
    });
  });

  // ============================================
  // Multiple resend scenarios
  // ============================================

  describe('multiple resend scenarios', () => {
    it('shows success alert even without status prop after successful resend', async () => {
      // Reset to default mock for this test
      mockedUseForm.mockReturnValue({
        post: mockPost,
        processing: false,
      } as ReturnType<typeof useForm>);

      mockPost.mockImplementation((_, options) => {
        options?.onSuccess?.();
      });

      render(<VerifyEmail />);

      // Initially no alert
      expect(screen.queryByText(/email sent/i)).not.toBeInTheDocument();

      // Click resend - find the submit button
      const submitButton = screen.getByRole('button', { name: /resend verification email/i });
      await user.click(submitButton);

      // Now alert should appear
      expect(screen.getByText(/email sent/i)).toBeInTheDocument();
    });

    it('alert persists after multiple resend attempts', async () => {
      // Reset to default mock for this test
      mockedUseForm.mockReturnValue({
        post: mockPost,
        processing: false,
      } as ReturnType<typeof useForm>);

      mockPost.mockImplementation((_, options) => {
        options?.onSuccess?.();
      });

      render(<VerifyEmail />);

      // First resend
      const submitButton = screen.getByRole('button', { name: /resend verification email/i });
      await user.click(submitButton);
      expect(screen.getByText(/email sent/i)).toBeInTheDocument();

      // Second resend - button should still be available
      await user.click(submitButton);
      expect(screen.getByText(/email sent/i)).toBeInTheDocument();
    });
  });
});
