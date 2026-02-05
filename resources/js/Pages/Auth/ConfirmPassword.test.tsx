import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm } from '@inertiajs/react';

import ConfirmPassword from './ConfirmPassword';

// Mock useForm from Inertia
const mockPost = vi.fn();
const mockSetData = vi.fn();
const mockReset = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { password: '' },
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

describe('ConfirmPassword Page', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    // Reset to default mock state
    mockedUseForm.mockReturnValue({
      data: { password: '' },
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
    it('renders the confirm password form', () => {
      render(<ConfirmPassword />);

      expect(screen.getByRole('heading', { name: /confirm your password/i })).toBeInTheDocument();
    });

    it('renders security explanation', () => {
      render(<ConfirmPassword />);

      expect(screen.getByText(/this is a secure area/i)).toBeInTheDocument();
    });

    it('renders please confirm instruction', () => {
      render(<ConfirmPassword />);

      expect(screen.getByText(/please confirm your password/i)).toBeInTheDocument();
    });

    it('renders password input field', () => {
      render(<ConfirmPassword />);

      expect(screen.getByLabelText(/password/i)).toBeInTheDocument();
    });

    it('renders submit button with correct text', () => {
      render(<ConfirmPassword />);

      expect(screen.getByRole('button', { name: /confirm/i })).toBeInTheDocument();
    });

    it('renders forgot password link', () => {
      render(<ConfirmPassword />);

      expect(screen.getByText(/forgot it/i)).toBeInTheDocument();
      expect(screen.getByText(/reset your password/i)).toBeInTheDocument();
    });

    it('uses AuthLayout', () => {
      render(<ConfirmPassword />);

      expect(screen.getByTestId('auth-layout')).toBeInTheDocument();
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('calls setData when password changes', async () => {
      render(<ConfirmPassword />);

      const passwordInput = screen.getByLabelText(/password/i);
      await user.type(passwordInput, 'mypassword123');

      expect(mockSetData).toHaveBeenCalledWith('password', expect.any(String));
    });

    it('calls post on form submit', () => {
      render(<ConfirmPassword />);

      const form = screen.getByRole('button', { name: /confirm/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPost).toHaveBeenCalled();
    });

    it('posts to password.confirm route', () => {
      render(<ConfirmPassword />);

      const form = screen.getByRole('button', { name: /confirm/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPost).toHaveBeenCalledWith(
        expect.stringContaining('password'),
        expect.any(Object)
      );
    });

    it('provides onFinish callback that resets password field', () => {
      render(<ConfirmPassword />);

      const form = screen.getByRole('button', { name: /confirm/i }).closest('form')!;
      fireEvent.submit(form);

      // Get the onFinish callback from the post call
      const postOptions = mockPost.mock.calls[0][1];
      expect(postOptions).toHaveProperty('onFinish');

      // Simulate calling onFinish
      postOptions.onFinish();
      expect(mockReset).toHaveBeenCalledWith('password');
    });

    it('prevents default form submission', async () => {
      render(<ConfirmPassword />);

      const form = screen.getByRole('button', { name: /confirm/i }).closest('form');
      const submitEvent = new Event('submit', { bubbles: true, cancelable: true });

      form?.dispatchEvent(submitEvent);

      expect(mockPost).toHaveBeenCalled();
    });
  });

  // ============================================
  // Validation tests
  // ============================================

  describe('validation', () => {
    it('password input has required attribute', () => {
      render(<ConfirmPassword />);

      const passwordInput = screen.getByLabelText(/password/i);
      expect(passwordInput).toHaveAttribute('required');
    });

    it('password input has type password', () => {
      render(<ConfirmPassword />);

      const passwordInput = screen.getByLabelText(/password/i);
      expect(passwordInput).toHaveAttribute('type', 'password');
    });

    it('displays password error when provided', () => {
      mockedUseForm.mockReturnValue({
        data: { password: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { password: 'The provided password is incorrect.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ConfirmPassword />);

      expect(screen.getByText(/the provided password is incorrect/i)).toBeInTheDocument();
    });

    it('displays password required error', () => {
      mockedUseForm.mockReturnValue({
        data: { password: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { password: 'The password field is required.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ConfirmPassword />);

      expect(screen.getByText(/the password field is required/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Processing state tests
  // ============================================

  describe('processing state', () => {
    it('disables submit button when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { password: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ConfirmPassword />);

      const submitButton = screen.getByRole('button', { name: /confirm/i });
      expect(submitButton).toBeDisabled();
    });

    it('button text remains same during processing', () => {
      mockedUseForm.mockReturnValue({
        data: { password: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<ConfirmPassword />);

      expect(screen.getByRole('button', { name: /confirm/i })).toBeInTheDocument();
    });

    it('enables submit button when not processing', () => {
      render(<ConfirmPassword />);

      const submitButton = screen.getByRole('button', { name: /confirm/i });
      expect(submitButton).not.toBeDisabled();
    });
  });

  // ============================================
  // Navigation tests
  // ============================================

  describe('navigation', () => {
    it('reset password link points to password.request route', () => {
      render(<ConfirmPassword />);

      const resetLink = screen.getByText(/reset your password/i);
      expect(resetLink).toHaveAttribute('href', expect.stringContaining('password'));
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('password input has proper label', () => {
      render(<ConfirmPassword />);

      const passwordInput = screen.getByLabelText(/password/i);
      expect(passwordInput).toBeInTheDocument();
    });

    it('password input has autocomplete=current-password', () => {
      render(<ConfirmPassword />);

      const passwordInput = screen.getByLabelText(/password/i);
      expect(passwordInput).toHaveAttribute('autocomplete', 'current-password');
    });

    it('password input has autoFocus', () => {
      render(<ConfirmPassword />);

      const passwordInput = screen.getByLabelText(/password/i);
      expect(passwordInput).toHaveFocus();
    });

    it('has accessible heading structure', () => {
      render(<ConfirmPassword />);

      const heading = screen.getByRole('heading', { level: 2 });
      expect(heading).toHaveTextContent(/confirm your password/i);
    });
  });

  // ============================================
  // Security context tests
  // ============================================

  describe('security context', () => {
    it('explains this is a secure area', () => {
      render(<ConfirmPassword />);

      expect(screen.getByText(/secure area/i)).toBeInTheDocument();
    });

    it('clarifies confirmation is required before continuing', () => {
      render(<ConfirmPassword />);

      expect(screen.getByText(/before continuing/i)).toBeInTheDocument();
    });
  });
});
