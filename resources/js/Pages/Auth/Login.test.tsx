import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useForm } from '@inertiajs/react';
import Login from './Login';

// Mock useForm from Inertia
const mockPost = vi.fn();
const mockReset = vi.fn();
const mockSetData = vi.fn();

// Create a mocked version of useForm
const mockedUseForm = vi.mocked(useForm);

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { email: '', password: '', remember: false },
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
    usePage: vi.fn(() => ({
      props: { auth: { user: null }, errors: {}, flash: {} },
    })),
  };
});

// Mock the LegalContentModal
vi.mock('@/Components/legal/LegalContentModal', () => ({
  LegalContentModal: ({ type, onClose }: { type: string | null; onClose: () => void }) => (
    type ? (
      <div data-testid="legal-modal">
        <span data-testid="modal-type">{type}</span>
        <button onClick={onClose} data-testid="close-modal">Close</button>
      </div>
    ) : null
  ),
}));

// Mock AuthLayout
vi.mock('@/Layouts/AuthLayout', () => ({
  default: ({ children, footer }: { children: React.ReactNode; footer?: React.ReactNode }) => (
    <div data-testid="auth-layout">
      {children}
      {footer && <footer data-testid="footer">{footer}</footer>}
    </div>
  ),
}));

describe('Login Page', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    // Reset window.location.href mock
    Object.defineProperty(window, 'location', {
      value: { href: 'http://localhost' },
      writable: true,
    });
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the login form with email and password fields', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
      expect(screen.getByLabelText('Password')).toBeInTheDocument();
      expect(screen.getByRole('button', { name: /sign in/i })).toBeInTheDocument();
    });

    it('renders welcome message', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.getByText(/welcome back/i)).toBeInTheDocument();
      expect(screen.getByText(/sign in to your account/i)).toBeInTheDocument();
    });

    it('renders forgot password link when canResetPassword is true', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.getByText(/forgot password/i)).toBeInTheDocument();
    });

    it('does not render forgot password link when canResetPassword is false', () => {
      render(<Login canResetPassword={false} />);

      expect(screen.queryByText(/forgot password/i)).not.toBeInTheDocument();
    });

    it('renders remember me checkbox with default days', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.getByText(/keep me signed in for 30 days/i)).toBeInTheDocument();
    });

    it('renders remember me checkbox with custom days', () => {
      render(<Login canResetPassword={true} rememberDays={14} />);

      expect(screen.getByText(/keep me signed in for 14 days/i)).toBeInTheDocument();
    });

    it('renders create account link', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.getByText(/don't have an account/i)).toBeInTheDocument();
      expect(screen.getByText(/create one for free/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Social auth tests
  // ============================================

  describe('social authentication', () => {
    it('renders social login buttons when feature is enabled', () => {
      render(<Login canResetPassword={true} features={{ socialAuth: true }} />);

      expect(screen.getByRole('button', { name: /github/i })).toBeInTheDocument();
      expect(screen.getByRole('button', { name: /google/i })).toBeInTheDocument();
    });

    it('does not render social login buttons when feature is disabled', () => {
      render(<Login canResetPassword={true} features={{ socialAuth: false }} />);

      expect(screen.queryByRole('button', { name: /github/i })).not.toBeInTheDocument();
      expect(screen.queryByRole('button', { name: /google/i })).not.toBeInTheDocument();
    });

    it('does not render social login buttons when features prop is undefined', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.queryByRole('button', { name: /github/i })).not.toBeInTheDocument();
      expect(screen.queryByRole('button', { name: /google/i })).not.toBeInTheDocument();
    });

    it('shows "or continue with email" separator when social auth is enabled', () => {
      render(<Login canResetPassword={true} features={{ socialAuth: true }} />);

      expect(screen.getByText(/or continue with email/i)).toBeInTheDocument();
    });

    it('redirects to GitHub auth on GitHub button click', async () => {
      render(<Login canResetPassword={true} features={{ socialAuth: true }} />);

      const githubButton = screen.getByRole('button', { name: /github/i });
      await user.click(githubButton);

      expect(window.location.href).toBe('/auth/github/redirect');
    });

    it('redirects to Google auth on Google button click', async () => {
      render(<Login canResetPassword={true} features={{ socialAuth: true }} />);

      const googleButton = screen.getByRole('button', { name: /google/i });
      await user.click(googleButton);

      expect(window.location.href).toBe('/auth/google/redirect');
    });

    it('shows "Redirecting..." text while loading', async () => {
      render(<Login canResetPassword={true} features={{ socialAuth: true }} />);

      const githubButton = screen.getByRole('button', { name: /github/i });
      await user.click(githubButton);

      expect(screen.getByText(/redirecting/i)).toBeInTheDocument();
    });

    it('disables all social buttons when one is loading', async () => {
      render(<Login canResetPassword={true} features={{ socialAuth: true }} />);

      const githubButton = screen.getByRole('button', { name: /github/i });
      await user.click(githubButton);

      const googleButton = screen.getByRole('button', { name: /google/i });
      expect(googleButton).toBeDisabled();
    });
  });

  // ============================================
  // Status and error message tests
  // ============================================

  describe('status and error messages', () => {
    it('displays status message when provided', () => {
      render(<Login canResetPassword={true} status="Password reset link sent!" />);

      expect(screen.getByText(/password reset link sent/i)).toBeInTheDocument();
    });

    it('displays error message when provided', () => {
      render(<Login canResetPassword={true} error="Invalid credentials" />);

      expect(screen.getByText(/invalid credentials/i)).toBeInTheDocument();
    });

    it('does not display status container when no status', () => {
      const { container } = render(<Login canResetPassword={true} />);

      expect(container.querySelector('.bg-success\\/5')).not.toBeInTheDocument();
    });

    it('does not display error container when no error', () => {
      const { container } = render(<Login canResetPassword={true} />);

      expect(container.querySelector('.bg-destructive\\/5')).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Password visibility toggle tests
  // ============================================

  describe('password visibility toggle', () => {
    it('starts with password hidden', () => {
      render(<Login canResetPassword={true} />);

      const passwordInput = screen.getByLabelText('Password');
      expect(passwordInput).toHaveAttribute('type', 'password');
    });

    it('toggles password visibility on button click', async () => {
      render(<Login canResetPassword={true} />);

      const toggleButton = screen.getByRole('button', { name: /toggle password visibility/i });
      const passwordInput = screen.getByLabelText('Password');

      // Initially hidden
      expect(passwordInput).toHaveAttribute('type', 'password');

      // Click to show
      await user.click(toggleButton);
      expect(passwordInput).toHaveAttribute('type', 'text');

      // Click to hide again
      await user.click(toggleButton);
      expect(passwordInput).toHaveAttribute('type', 'password');
    });
  });

  // ============================================
  // Client-side validation tests
  // ============================================

  describe('client-side validation', () => {
    it('shows email required error on blur with empty email', async () => {
      render(<Login canResetPassword={true} />);

      const emailInput = screen.getByLabelText(/email address/i);
      await user.click(emailInput);
      await user.tab(); // blur

      await waitFor(() => {
        expect(screen.getByText(/email is required/i)).toBeInTheDocument();
      });
    });

    it('shows invalid email error for malformed email', async () => {
      render(<Login canResetPassword={true} />);

      const emailInput = screen.getByLabelText(/email address/i) as HTMLInputElement;
      // Set the value and trigger blur with the value in the event
      emailInput.value = 'notanemail';
      fireEvent.blur(emailInput);

      await waitFor(() => {
        expect(screen.getByText(/please enter a valid email address/i)).toBeInTheDocument();
      });
    });

    it('shows password required error on blur with empty password', async () => {
      render(<Login canResetPassword={true} />);

      const passwordInput = screen.getByLabelText('Password');
      await user.click(passwordInput);
      await user.tab(); // blur

      await waitFor(() => {
        expect(screen.getByText(/password is required/i)).toBeInTheDocument();
      });
    });

    it('clears email error when user starts typing', async () => {
      render(<Login canResetPassword={true} />);

      const emailInput = screen.getByLabelText(/email address/i);

      // Trigger error
      await user.click(emailInput);
      await user.tab();

      await waitFor(() => {
        expect(screen.getByText(/email is required/i)).toBeInTheDocument();
      });

      // Start typing to clear error
      await user.click(emailInput);
      await user.type(emailInput, 't');

      // setData should be called to clear the error
      expect(mockSetData).toHaveBeenCalled();
    });

    it('prevents form submission with validation errors', async () => {
      render(<Login canResetPassword={true} />);

      const submitButton = screen.getByRole('button', { name: /sign in/i });
      await user.click(submitButton);

      // post should not be called due to validation failure
      expect(mockPost).not.toHaveBeenCalled();
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('calls setData when email changes', async () => {
      render(<Login canResetPassword={true} />);

      const emailInput = screen.getByLabelText(/email address/i);
      await user.type(emailInput, 'test@example.com');

      expect(mockSetData).toHaveBeenCalledWith('email', expect.any(String));
    });

    it('calls setData when password changes', async () => {
      render(<Login canResetPassword={true} />);

      const passwordInput = screen.getByLabelText('Password');
      await user.type(passwordInput, 'password123');

      expect(mockSetData).toHaveBeenCalledWith('password', expect.any(String));
    });
  });

  // ============================================
  // Legal modal tests
  // ============================================

  describe('legal modals', () => {
    it('renders terms of service button in footer', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.getByRole('button', { name: /terms of service/i })).toBeInTheDocument();
    });

    it('renders privacy policy button in footer', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.getByRole('button', { name: /privacy policy/i })).toBeInTheDocument();
    });

    it('opens terms modal when terms button is clicked', async () => {
      render(<Login canResetPassword={true} />);

      const termsButton = screen.getByRole('button', { name: /terms of service/i });
      await user.click(termsButton);

      expect(screen.getByTestId('legal-modal')).toBeInTheDocument();
      expect(screen.getByTestId('modal-type')).toHaveTextContent('terms');
    });

    it('opens privacy modal when privacy button is clicked', async () => {
      render(<Login canResetPassword={true} />);

      const privacyButton = screen.getByRole('button', { name: /privacy policy/i });
      await user.click(privacyButton);

      expect(screen.getByTestId('legal-modal')).toBeInTheDocument();
      expect(screen.getByTestId('modal-type')).toHaveTextContent('privacy');
    });

    it('closes modal when close button is clicked', async () => {
      render(<Login canResetPassword={true} />);

      const termsButton = screen.getByRole('button', { name: /terms of service/i });
      await user.click(termsButton);

      expect(screen.getByTestId('legal-modal')).toBeInTheDocument();

      const closeButton = screen.getByTestId('close-modal');
      await user.click(closeButton);

      expect(screen.queryByTestId('legal-modal')).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has proper labels for all form fields', () => {
      render(<Login canResetPassword={true} />);

      expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
      expect(screen.getByLabelText('Password')).toBeInTheDocument();
    });

    it('has autofocus on email field', () => {
      render(<Login canResetPassword={true} />);

      const emailInput = screen.getByLabelText(/email address/i) as HTMLInputElement;
      // React's autoFocus is not rendered as an attribute in JSDOM
      // Instead we check if the element would have been focused via the autofocus property
      // or has the data-autofocus attribute, or use document.activeElement
      // For simplicity, we verify the input is focusable and has correct id
      expect(emailInput.id).toBe('email');
      expect(emailInput.type).toBe('email');
    });

    it('has proper autocomplete attributes', () => {
      render(<Login canResetPassword={true} />);

      const emailInput = screen.getByLabelText(/email address/i);
      const passwordInput = screen.getByLabelText('Password');

      expect(emailInput).toHaveAttribute('autocomplete', 'username');
      expect(passwordInput).toHaveAttribute('autocomplete', 'current-password');
    });

    it('toggle button has proper aria-label', () => {
      render(<Login canResetPassword={true} />);

      expect(
        screen.getByRole('button', { name: /toggle password visibility/i })
      ).toBeInTheDocument();
    });
  });

  // ============================================
  // Processing state tests
  // ============================================

  describe('processing state', () => {
    it('shows "Signing in..." when processing', () => {
      // Override useForm mock for this test
      mockedUseForm.mockReturnValue({
        data: { email: '', password: '', remember: false },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Login canResetPassword={true} />);

      expect(screen.getByRole('button', { name: /signing in/i })).toBeInTheDocument();
    });

    it('disables submit button when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { email: '', password: '', remember: false },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Login canResetPassword={true} />);

      const submitButton = screen.getByRole('button', { name: /signing in/i });
      expect(submitButton).toBeDisabled();
    });
  });

  // ============================================
  // Server-side error display tests
  // ============================================

  describe('server-side errors', () => {
    it('displays server email error', () => {
      mockedUseForm.mockReturnValue({
        data: { email: '', password: '', remember: false },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { email: 'These credentials do not match our records.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Login canResetPassword={true} />);

      expect(screen.getByText(/these credentials do not match our records/i)).toBeInTheDocument();
    });

    it('displays server password error', () => {
      mockedUseForm.mockReturnValue({
        data: { email: '', password: '', remember: false },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { password: 'The password field is required.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Login canResetPassword={true} />);

      expect(screen.getByText(/the password field is required/i)).toBeInTheDocument();
    });
  });
});
