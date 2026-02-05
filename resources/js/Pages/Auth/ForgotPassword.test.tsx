import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm } from '@inertiajs/react';

import ForgotPassword from './ForgotPassword';

// Mock useForm from Inertia
const mockPost = vi.fn();
const mockSetData = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { email: '' },
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

// Mock AuthLayout
vi.mock('@/Layouts/AuthLayout', () => ({
  default: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="auth-layout">{children}</div>
  ),
}));

const mockedUseForm = vi.mocked(useForm);

describe('ForgotPassword Page', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    // Reset to default mock state
    mockedUseForm.mockReturnValue({
      data: { email: '' },
      setData: mockSetData,
      post: mockPost,
      processing: false,
      errors: {},
    } as ReturnType<typeof useForm>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the forgot password form', () => {
      render(<ForgotPassword />);

      expect(screen.getByText(/forgot your password/i)).toBeInTheDocument();
    });

    it('renders email input field', () => {
      render(<ForgotPassword />);

      expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
    });

    it('renders submit button with correct text', () => {
      render(<ForgotPassword />);

      expect(screen.getByRole('button', { name: /email password reset link/i })).toBeInTheDocument();
    });

    it('renders back to sign in link', () => {
      render(<ForgotPassword />);

      expect(screen.getByText(/remembered it/i)).toBeInTheDocument();
      expect(screen.getByText(/back to sign in/i)).toBeInTheDocument();
    });

    it('renders explanatory text', () => {
      render(<ForgotPassword />);

      expect(screen.getByText(/no problem/i)).toBeInTheDocument();
      expect(screen.getByText(/send you a password reset link/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Status message tests
  // ============================================

  describe('status message', () => {
    it('displays success alert when status prop is provided', () => {
      render(<ForgotPassword status="A password reset link has been sent." />);

      expect(screen.getByText(/email sent/i)).toBeInTheDocument();
      expect(screen.getByText(/a password reset link has been sent/i)).toBeInTheDocument();
    });

    it('does not show alert when no status', () => {
      render(<ForgotPassword />);

      expect(screen.queryByText(/email sent/i)).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('calls setData when email changes', async () => {
      render(<ForgotPassword />);

      const emailInput = screen.getByLabelText(/email address/i);
      await user.type(emailInput, 'test@example.com');

      expect(mockSetData).toHaveBeenCalledWith('email', expect.any(String));
    });

    it('calls post on form submit', () => {
      render(<ForgotPassword />);

      const form = screen.getByRole('button', { name: /email password reset link/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPost).toHaveBeenCalled();
    });

    it('prevents default form submission', async () => {
      render(<ForgotPassword />);

      const form = screen.getByRole('button', { name: /email password reset link/i }).closest('form');
      const submitEvent = new Event('submit', { bubbles: true, cancelable: true });

      form?.dispatchEvent(submitEvent);

      expect(mockPost).toHaveBeenCalled();
    });
  });

  // ============================================
  // Validation tests
  // ============================================

  describe('validation', () => {
    it('email input has required attribute', () => {
      render(<ForgotPassword />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toHaveAttribute('required');
    });

    it('email input has type email', () => {
      render(<ForgotPassword />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toHaveAttribute('type', 'email');
    });

    it('displays server email error', () => {
      mockedUseForm.mockReturnValue({
        data: { email: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { email: 'We could not find a user with that email.' },
      } as ReturnType<typeof useForm>);

      render(<ForgotPassword />);

      expect(screen.getByText(/we could not find a user with that email/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Processing state tests
  // ============================================

  describe('processing state', () => {
    it('disables submit button when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { email: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<ForgotPassword />);

      const submitButton = screen.getByRole('button', { name: /email password reset link/i });
      expect(submitButton).toBeDisabled();
    });

    it('button text remains same during processing', () => {
      mockedUseForm.mockReturnValue({
        data: { email: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<ForgotPassword />);

      expect(screen.getByRole('button', { name: /email password reset link/i })).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('email input has proper label', () => {
      render(<ForgotPassword />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toBeInTheDocument();
    });

    it('email input has autocomplete=email', () => {
      render(<ForgotPassword />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toHaveAttribute('autocomplete', 'email');
    });

    it('email input has autoFocus', () => {
      render(<ForgotPassword />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toHaveFocus();
    });
  });
});
