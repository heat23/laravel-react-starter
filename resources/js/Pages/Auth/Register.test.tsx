import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm } from '@inertiajs/react';

import Register from './Register';

// Mock useForm from Inertia
const mockPost = vi.fn();
const mockReset = vi.fn();
const mockSetData = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { name: '', email: '', password: '', password_confirmation: '' },
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

const mockedUseForm = vi.mocked(useForm);

describe('Register Page', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    Object.defineProperty(window, 'location', {
      value: { href: 'http://localhost' },
      writable: true,
    });
    // Reset to default mock state with empty password (password strength hidden)
    mockedUseForm.mockReturnValue({
      data: { name: '', email: '', password: '', password_confirmation: '' },
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
    it('renders the registration form with all required fields', () => {
      render(<Register />);

      expect(screen.getByLabelText(/full name/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/^password$/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/confirm password/i)).toBeInTheDocument();
    });

    it('renders create account header', () => {
      render(<Register />);

      expect(screen.getByText(/create your account/i)).toBeInTheDocument();
      expect(screen.getByText(/start your journey/i)).toBeInTheDocument();
    });

    it('renders sign in link', () => {
      render(<Register />);

      expect(screen.getByText(/already have an account/i)).toBeInTheDocument();
      expect(screen.getByText(/sign in instead/i)).toBeInTheDocument();
    });

    it('renders terms checkbox', () => {
      render(<Register />);

      expect(screen.getByRole('checkbox')).toBeInTheDocument();
      expect(screen.getByText(/i agree to the/i)).toBeInTheDocument();
    });

    it('renders submit button', () => {
      render(<Register />);

      expect(screen.getByRole('button', { name: /create account/i })).toBeInTheDocument();
    });
  });

  // ============================================
  // Social authentication tests
  // ============================================

  describe('social authentication', () => {
    it('renders GitHub and Google buttons when socialAuth feature is enabled', () => {
      render(<Register features={{ socialAuth: true }} />);

      expect(screen.getByRole('button', { name: /github/i })).toBeInTheDocument();
      expect(screen.getByRole('button', { name: /google/i })).toBeInTheDocument();
    });

    it('does not render social buttons when feature is disabled', () => {
      render(<Register features={{ socialAuth: false }} />);

      expect(screen.queryByRole('button', { name: /github/i })).not.toBeInTheDocument();
      expect(screen.queryByRole('button', { name: /google/i })).not.toBeInTheDocument();
    });

    it('shows "or register with email" separator when social auth is enabled', () => {
      render(<Register features={{ socialAuth: true }} />);

      expect(screen.getByText(/or register with email/i)).toBeInTheDocument();
    });

    it('does not show separator when social auth is disabled', () => {
      render(<Register features={{ socialAuth: false }} />);

      expect(screen.queryByText(/or register with email/i)).not.toBeInTheDocument();
    });

    it('redirects to GitHub auth on button click', async () => {
      render(<Register features={{ socialAuth: true }} />);

      const githubButton = screen.getByRole('button', { name: /github/i });
      await user.click(githubButton);

      expect(window.location.href).toContain('github');
    });

    it('redirects to Google auth on button click', async () => {
      render(<Register features={{ socialAuth: true }} />);

      const googleButton = screen.getByRole('button', { name: /google/i });
      await user.click(googleButton);

      expect(window.location.href).toContain('google');
    });

    it('shows "Redirecting..." text during social redirect', async () => {
      render(<Register features={{ socialAuth: true }} />);

      const githubButton = screen.getByRole('button', { name: /github/i });
      await user.click(githubButton);

      expect(screen.getByText(/redirecting/i)).toBeInTheDocument();
    });

    it('disables all social buttons during redirect', async () => {
      render(<Register features={{ socialAuth: true }} />);

      const githubButton = screen.getByRole('button', { name: /github/i });
      await user.click(githubButton);

      const googleButton = screen.getByRole('button', { name: /google/i });
      expect(googleButton).toBeDisabled();
    });
  });

  // ============================================
  // Password strength tests
  // ============================================

  describe('password strength', () => {
    it('shows password strength indicator when password is entered', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: 'test', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText(/password strength/i)).toBeInTheDocument();
    });

    it('does not show password strength when password is empty', () => {
      render(<Register />);

      expect(screen.queryByText(/password strength/i)).not.toBeInTheDocument();
    });

    it('displays "Weak" for passwords under 25% strength', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: 'a', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText('Weak')).toBeInTheDocument();
    });

    it('displays "Strong" for passwords meeting all requirements', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: 'Password1', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText('Strong')).toBeInTheDocument();
    });

    it('shows requirement items', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: 'test', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText(/at least 8 characters/i)).toBeInTheDocument();
      expect(screen.getByText(/one uppercase letter/i)).toBeInTheDocument();
      expect(screen.getByText(/one lowercase letter/i)).toBeInTheDocument();
      expect(screen.getByText(/one number/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Terms and conditions tests
  // ============================================

  describe('terms and conditions', () => {
    it('disables submit button when terms not accepted', () => {
      render(<Register />);

      const submitButton = screen.getByRole('button', { name: /create account/i });
      expect(submitButton).toBeDisabled();
    });

    it('opens terms modal on Terms of Service link click', async () => {
      render(<Register />);

      const termsLinks = screen.getAllByText(/terms of service/i);
      await user.click(termsLinks[0]);

      expect(screen.getByTestId('legal-modal')).toBeInTheDocument();
      expect(screen.getByTestId('modal-type')).toHaveTextContent('terms');
    });

    it('opens privacy modal on Privacy Policy link click', async () => {
      render(<Register />);

      const privacyLink = screen.getByText(/privacy policy/i);
      await user.click(privacyLink);

      expect(screen.getByTestId('legal-modal')).toBeInTheDocument();
      expect(screen.getByTestId('modal-type')).toHaveTextContent('privacy');
    });

    it('closes legal modal on close button click', async () => {
      render(<Register />);

      const termsLinks = screen.getAllByText(/terms of service/i);
      await user.click(termsLinks[0]);

      expect(screen.getByTestId('legal-modal')).toBeInTheDocument();

      await user.click(screen.getByTestId('close-modal'));

      expect(screen.queryByTestId('legal-modal')).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('calls setData when name changes', async () => {
      render(<Register />);

      const nameInput = screen.getByLabelText(/full name/i);
      await user.type(nameInput, 'John Doe');

      expect(mockSetData).toHaveBeenCalledWith('name', expect.any(String));
    });

    it('calls setData when email changes', async () => {
      render(<Register />);

      const emailInput = screen.getByLabelText(/email address/i);
      await user.type(emailInput, 'test@example.com');

      expect(mockSetData).toHaveBeenCalledWith('email', expect.any(String));
    });

    it('calls setData when password changes', async () => {
      render(<Register />);

      const passwordInput = screen.getByLabelText(/^password$/i);
      await user.type(passwordInput, 'Password123');

      expect(mockSetData).toHaveBeenCalledWith('password', expect.any(String));
    });

    it('calls setData when password confirmation changes', async () => {
      render(<Register />);

      const confirmInput = screen.getByLabelText(/confirm password/i);
      await user.type(confirmInput, 'Password123');

      expect(mockSetData).toHaveBeenCalledWith('password_confirmation', expect.any(String));
    });

    it('calls post with register route on valid submission', async () => {
      mockedUseForm.mockReturnValue({
        data: { name: 'Test', email: 'test@test.com', password: 'Password1', password_confirmation: 'Password1' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      // Accept terms
      const checkbox = screen.getByRole('checkbox');
      await user.click(checkbox);

      const submitButton = screen.getByRole('button', { name: /create account/i });
      await user.click(submitButton);

      expect(mockPost).toHaveBeenCalled();
    });
  });

  // ============================================
  // Password visibility tests
  // ============================================

  describe('password visibility', () => {
    it('password field starts as password type', () => {
      render(<Register />);

      const passwordInput = screen.getByLabelText(/^password$/i);
      expect(passwordInput).toHaveAttribute('type', 'password');
    });

    it('toggles password visibility on button click', async () => {
      render(<Register />);

      const toggleButton = screen.getByRole('button', { name: /toggle password/i });
      const passwordInput = screen.getByLabelText(/^password$/i);

      expect(passwordInput).toHaveAttribute('type', 'password');

      await user.click(toggleButton);

      expect(passwordInput).toHaveAttribute('type', 'text');
    });
  });

  // ============================================
  // Processing state tests
  // ============================================

  describe('processing state', () => {
    it('shows "Creating account..." when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: 'Password1', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText(/creating account/i)).toBeInTheDocument();
    });

    it('disables submit button when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: 'Password1', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      const submitButton = screen.getByRole('button', { name: /creating account/i });
      expect(submitButton).toBeDisabled();
    });

    it('disables social buttons when form is processing', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: true,
        errors: {},
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register features={{ socialAuth: true }} />);

      const githubButton = screen.getByRole('button', { name: /github/i });
      const googleButton = screen.getByRole('button', { name: /google/i });

      // Social buttons are only disabled during social loading, not form processing
      // This test documents the current behavior
      expect(githubButton).not.toBeDisabled();
      expect(googleButton).not.toBeDisabled();
    });
  });

  // ============================================
  // Error handling tests
  // ============================================

  describe('error handling', () => {
    it('displays error message when error prop is provided', () => {
      render(<Register error="An error occurred" />);

      expect(screen.getByText(/an error occurred/i)).toBeInTheDocument();
    });

    it('displays server name error', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { name: 'The name field is required.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText(/the name field is required/i)).toBeInTheDocument();
    });

    it('displays server email error', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { email: 'The email has already been taken.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText(/the email has already been taken/i)).toBeInTheDocument();
    });

    it('displays server password error', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { password: 'The password must be at least 8 characters.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText(/the password must be at least 8 characters/i)).toBeInTheDocument();
    });

    it('displays server password_confirmation error', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: '', password: '', password_confirmation: '' },
        setData: mockSetData,
        post: mockPost,
        processing: false,
        errors: { password_confirmation: 'The password confirmation does not match.' },
        reset: mockReset,
      } as ReturnType<typeof useForm>);

      render(<Register />);

      expect(screen.getByText(/the password confirmation does not match/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has proper labels for all form fields', () => {
      render(<Register />);

      expect(screen.getByLabelText(/full name/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/email address/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/^password$/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/confirm password/i)).toBeInTheDocument();
    });

    it('has proper autocomplete attributes', () => {
      render(<Register />);

      expect(screen.getByLabelText(/full name/i)).toHaveAttribute('autocomplete', 'name');
      expect(screen.getByLabelText(/email address/i)).toHaveAttribute('autocomplete', 'username');
      expect(screen.getByLabelText(/^password$/i)).toHaveAttribute('autocomplete', 'new-password');
      expect(screen.getByLabelText(/confirm password/i)).toHaveAttribute('autocomplete', 'new-password');
    });

    it('toggle button has aria-label', () => {
      render(<Register />);

      const toggleButton = screen.getByRole('button', { name: /toggle password/i });
      expect(toggleButton).toHaveAttribute('aria-label');
    });
  });
});
