import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm } from '@inertiajs/react';

import ResetPassword from './ResetPassword';

// Mock useForm from Inertia
const mockPost = vi.fn();
const mockSetData = vi.fn();
const mockReset = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { token: 'test-token', email: 'test@example.com', password: '', password_confirmation: '' },
      setData: mockSetData,
      post: mockPost,
      processing: false,
      errors: {},
      reset: mockReset,
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

const defaultProps = {
  token: 'test-reset-token',
  email: 'user@example.com',
};

describe('ResetPassword Page', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    // Reset to default mock state
    mockedUseForm.mockReturnValue({
      data: { token: 'test-token', email: 'test@example.com', password: '', password_confirmation: '' },
      setData: mockSetData,
      post: mockPost,
      processing: false,
      errors: {},
      reset: mockReset,
    } as ReturnType<typeof useForm>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the reset password form', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByText(/reset your password/i)).toBeInTheDocument();
    });

    it('renders page description', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByText(/create a new password for your account/i)).toBeInTheDocument();
    });

    it('renders email input field', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
    });

    it('renders password input field', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByLabelText(/^password$/i)).toBeInTheDocument();
    });

    it('renders password confirmation input field', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByLabelText(/confirm password/i)).toBeInTheDocument();
    });

    it('renders submit button with correct text', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByRole('button', { name: /reset password/i })).toBeInTheDocument();
    });

    it('renders back to sign in link', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByText(/remembered it/i)).toBeInTheDocument();
      expect(screen.getByText(/back to sign in/i)).toBeInTheDocument();
    });

    it('uses AuthLayout', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByTestId('auth-layout')).toBeInTheDocument();
    });
  });

  // ============================================
  // Initial state tests
  // ============================================

  describe('initial state', () => {
    it('initializes form with provided token', () => {
      render(<ResetPassword {...defaultProps} />);

      // The useForm is initialized with the token prop
      expect(useForm).toHaveBeenCalledWith(
        expect.objectContaining({
          token: defaultProps.token,
        })
      );
    });

    it('initializes form with provided email', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(useForm).toHaveBeenCalledWith(
        expect.objectContaining({
          email: defaultProps.email,
        })
      );
    });

    it('initializes form with empty password', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(useForm).toHaveBeenCalledWith(
        expect.objectContaining({
          password: '',
        })
      );
    });

    it('initializes form with empty password confirmation', () => {
      render(<ResetPassword {...defaultProps} />);

      expect(useForm).toHaveBeenCalledWith(
        expect.objectContaining({
          password_confirmation: '',
        })
      );
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('calls setData when email changes', async () => {
      render(<ResetPassword {...defaultProps} />);

      const emailInput = screen.getByLabelText(/email address/i);
      await user.clear(emailInput);
      await user.type(emailInput, 'new@example.com');

      expect(mockSetData).toHaveBeenCalledWith('email', expect.any(String));
    });

    it('calls setData when password changes', async () => {
      render(<ResetPassword {...defaultProps} />);

      const passwordInput = screen.getByLabelText(/^password$/i);
      await user.type(passwordInput, 'newpassword123');

      expect(mockSetData).toHaveBeenCalledWith('password', expect.any(String));
    });

    it('calls setData when password confirmation changes', async () => {
      render(<ResetPassword {...defaultProps} />);

      const confirmInput = screen.getByLabelText(/confirm password/i);
      await user.type(confirmInput, 'newpassword123');

      expect(mockSetData).toHaveBeenCalledWith('password_confirmation', expect.any(String));
    });

    it('calls post on form submit', () => {
      render(<ResetPassword {...defaultProps} />);

      const form = screen.getByRole('button', { name: /reset password/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPost).toHaveBeenCalled();
    });

    it('posts to password.store route', () => {
      render(<ResetPassword {...defaultProps} />);

      const form = screen.getByRole('button', { name: /reset password/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPost).toHaveBeenCalledWith(
        expect.stringContaining('password'),
        expect.any(Object)
      );
    });

    it('provides onFinish callback that resets password fields', () => {
      render(<ResetPassword {...defaultProps} />);

      const form = screen.getByRole('button', { name: /reset password/i }).closest('form')!;
      fireEvent.submit(form);

      // Get the onFinish callback from the post call
      const postOptions = mockPost.mock.calls[0][1];
      expect(postOptions).toHaveProperty('onFinish');

      // Simulate calling onFinish
      postOptions.onFinish();
      expect(mockReset).toHaveBeenCalledWith('password', 'password_confirmation');
    });
  });

  // ============================================
  // Validation tests
  // ============================================

  describe('validation', () => {
    it('email input has required attribute', () => {
      render(<ResetPassword {...defaultProps} />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toHaveAttribute('required');
    });

    it('email input has type email', () => {
      render(<ResetPassword {...defaultProps} />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toHaveAttribute('type', 'email');
    });

    it('password input has required attribute', () => {
      render(<ResetPassword {...defaultProps} />);

      const passwordInput = screen.getByLabelText(/^password$/i);
      expect(passwordInput).toHaveAttribute('required');
    });

    it('password input has type password', () => {
      render(<ResetPassword {...defaultProps} />);

      const passwordInput = screen.getByLabelText(/^password$/i);
      expect(passwordInput).toHaveAttribute('type', 'password');
    });

    it('password confirmation has required attribute', () => {
      render(<ResetPassword {...defaultProps} />);

      const confirmInput = screen.getByLabelText(/confirm password/i);
      expect(confirmInput).toHaveAttribute('required');
    });

    it('password confirmation has type password', () => {
      render(<ResetPassword {...defaultProps} />);

      const confirmInput = screen.getByLabelText(/confirm password/i);
      expect(confirmInput).toHaveAttribute('type', 'password');
    });
  });

  // ============================================
  // Error handling tests
  // ============================================

  describe('error handling', () => {
    it('displays email error', () => {
      mockedUseForm.mockReturnValue({
        data: { token: 'test', email: '', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { email: 'The email field is required.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByText(/the email field is required/i)).toBeInTheDocument();
    });

    it('displays password error', () => {
      mockedUseForm.mockReturnValue({
        data: { token: 'test', email: 'test@example.com', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { password: 'The password must be at least 8 characters.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByText(/the password must be at least 8 characters/i)).toBeInTheDocument();
    });

    it('displays password confirmation error', () => {
      mockedUseForm.mockReturnValue({
        data: { token: 'test', email: 'test@example.com', password: 'password123', password_confirmation: 'different' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { password_confirmation: 'The password confirmation does not match.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByText(/the password confirmation does not match/i)).toBeInTheDocument();
    });

    it('displays invalid token error via email field', () => {
      mockedUseForm.mockReturnValue({
        data: { token: 'invalid', email: 'test@example.com', password: 'password123', password_confirmation: 'password123' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { email: 'This password reset token is invalid.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByText(/this password reset token is invalid/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Processing state tests
  // ============================================

  describe('processing state', () => {
    it('disables submit button when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { token: 'test', email: 'test@example.com', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ResetPassword {...defaultProps} />);

      const submitButton = screen.getByRole('button', { name: /reset password/i });
      expect(submitButton).toBeDisabled();
    });

    it('button text remains same during processing', () => {
      mockedUseForm.mockReturnValue({
        data: { token: 'test', email: 'test@example.com', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ResetPassword {...defaultProps} />);

      expect(screen.getByRole('button', { name: /reset password/i })).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('email input has proper label', () => {
      render(<ResetPassword {...defaultProps} />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toBeInTheDocument();
    });

    it('password input has proper label', () => {
      render(<ResetPassword {...defaultProps} />);

      const passwordInput = screen.getByLabelText(/^password$/i);
      expect(passwordInput).toBeInTheDocument();
    });

    it('password confirmation has proper label', () => {
      render(<ResetPassword {...defaultProps} />);

      const confirmInput = screen.getByLabelText(/confirm password/i);
      expect(confirmInput).toBeInTheDocument();
    });

    it('email input has autocomplete=username', () => {
      render(<ResetPassword {...defaultProps} />);

      const emailInput = screen.getByLabelText(/email address/i);
      expect(emailInput).toHaveAttribute('autocomplete', 'username');
    });

    it('password input has autocomplete=new-password', () => {
      render(<ResetPassword {...defaultProps} />);

      const passwordInput = screen.getByLabelText(/^password$/i);
      expect(passwordInput).toHaveAttribute('autocomplete', 'new-password');
    });

    it('password confirmation has autocomplete=new-password', () => {
      render(<ResetPassword {...defaultProps} />);

      const confirmInput = screen.getByLabelText(/confirm password/i);
      expect(confirmInput).toHaveAttribute('autocomplete', 'new-password');
    });

    it('password input has autoFocus', () => {
      render(<ResetPassword {...defaultProps} />);

      const passwordInput = screen.getByLabelText(/^password$/i);
      expect(passwordInput).toHaveFocus();
    });
  });
});
