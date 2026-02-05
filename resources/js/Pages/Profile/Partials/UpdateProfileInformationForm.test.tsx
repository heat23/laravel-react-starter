import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm, usePage } from '@inertiajs/react';

import UpdateProfileInformationForm from './UpdateProfileInformationForm';

// Mock useForm from Inertia
const mockPatch = vi.fn();
const mockSetData = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { name: 'John Doe', email: 'john@example.com' },
      setData: mockSetData,
      patch: mockPatch,
      processing: false,
      errors: {},
      recentlySuccessful: false,
    })),
    usePage: vi.fn(() => ({
      props: {
        auth: {
          user: {
            name: 'John Doe',
            email: 'john@example.com',
            email_verified_at: '2024-01-01T00:00:00.000000Z',
          },
        },
      },
    })),
    Link: ({ children, href, method, as }: { children: React.ReactNode; href: string; method?: string; as?: string }) => {
      if (as === 'button') {
        return <button data-href={href} data-method={method}>{children}</button>;
      }
      return <a href={href}>{children}</a>;
    },
  };
});

const mockedUseForm = vi.mocked(useForm);
const mockedUsePage = vi.mocked(usePage);

describe('UpdateProfileInformationForm', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    // Reset to default mock state
    mockedUseForm.mockReturnValue({
      data: { name: 'John Doe', email: 'john@example.com' },
      setData: mockSetData,
      patch: mockPatch,
      processing: false,
      errors: {},
      recentlySuccessful: false,
    } as ReturnType<typeof useForm>);
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            name: 'John Doe',
            email: 'john@example.com',
            email_verified_at: '2024-01-01T00:00:00.000000Z',
          },
        },
      },
    } as ReturnType<typeof usePage>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the form', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      expect(screen.getByLabelText(/name/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/email/i)).toBeInTheDocument();
    });

    it('renders name input with current value', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const nameInput = screen.getByLabelText(/name/i);
      expect(nameInput).toHaveValue('John Doe');
    });

    it('renders email input with current value', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const emailInput = screen.getByLabelText(/email/i);
      expect(emailInput).toHaveValue('john@example.com');
    });

    it('renders save button', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      expect(screen.getByRole('button', { name: /save profile/i })).toBeInTheDocument();
    });

    it('applies custom className', () => {
      const { container } = render(
        <UpdateProfileInformationForm mustVerifyEmail={false} className="custom-class" />
      );

      expect(container.querySelector('.custom-class')).toBeInTheDocument();
    });
  });

  // ============================================
  // Email verification tests
  // ============================================

  describe('email verification', () => {
    it('shows unverified message when mustVerifyEmail is true and email not verified', () => {
      mockedUsePage.mockReturnValue({
        props: {
          auth: {
            user: {
              name: 'John Doe',
              email: 'john@example.com',
              email_verified_at: null,
            },
          },
        },
      } as ReturnType<typeof usePage>);

      render(<UpdateProfileInformationForm mustVerifyEmail={true} />);

      expect(screen.getByText(/your email address is unverified/i)).toBeInTheDocument();
    });

    it('shows resend verification link when email not verified', () => {
      mockedUsePage.mockReturnValue({
        props: {
          auth: {
            user: {
              name: 'John Doe',
              email: 'john@example.com',
              email_verified_at: null,
            },
          },
        },
      } as ReturnType<typeof usePage>);

      render(<UpdateProfileInformationForm mustVerifyEmail={true} />);

      expect(screen.getByText(/click here to re-send the verification email/i)).toBeInTheDocument();
    });

    it('does not show unverified message when email is verified', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={true} />);

      expect(screen.queryByText(/your email address is unverified/i)).not.toBeInTheDocument();
    });

    it('does not show unverified message when mustVerifyEmail is false', () => {
      mockedUsePage.mockReturnValue({
        props: {
          auth: {
            user: {
              name: 'John Doe',
              email: 'john@example.com',
              email_verified_at: null,
            },
          },
        },
      } as ReturnType<typeof usePage>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      expect(screen.queryByText(/your email address is unverified/i)).not.toBeInTheDocument();
    });

    it('shows verification link sent message when status is set', () => {
      mockedUsePage.mockReturnValue({
        props: {
          auth: {
            user: {
              name: 'John Doe',
              email: 'john@example.com',
              email_verified_at: null,
            },
          },
        },
      } as ReturnType<typeof usePage>);

      render(<UpdateProfileInformationForm mustVerifyEmail={true} status="verification-link-sent" />);

      expect(screen.getByText(/a new verification link has been sent/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Form input tests
  // ============================================

  describe('form input', () => {
    it('calls setData when name changes', async () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const nameInput = screen.getByLabelText(/name/i);
      await user.clear(nameInput);
      await user.type(nameInput, 'Jane Doe');

      expect(mockSetData).toHaveBeenCalledWith('name', expect.any(String));
    });

    it('calls setData when email changes', async () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const emailInput = screen.getByLabelText(/email/i);
      await user.clear(emailInput);
      await user.type(emailInput, 'jane@example.com');

      expect(mockSetData).toHaveBeenCalledWith('email', expect.any(String));
    });
  });

  // ============================================
  // Client-side validation tests
  // ============================================

  describe('client-side validation', () => {
    it('shows error when name is empty on blur', async () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: 'john@example.com' },
        setData: mockSetData,
        patch: mockPatch,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const nameInput = screen.getByLabelText(/name/i);
      fireEvent.blur(nameInput);

      // Client-side validation happens on blur
      expect(screen.getByText(/name is required/i)).toBeInTheDocument();
    });

    it('shows error when email is invalid on blur', async () => {
      mockedUseForm.mockReturnValue({
        data: { name: 'John', email: 'invalid-email' },
        setData: mockSetData,
        patch: mockPatch,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const emailInput = screen.getByLabelText(/email/i);
      fireEvent.blur(emailInput);

      expect(screen.getByText(/please enter a valid email address/i)).toBeInTheDocument();
    });

    it('validates name max length', async () => {
      const longName = 'a'.repeat(256);
      mockedUseForm.mockReturnValue({
        data: { name: longName, email: 'john@example.com' },
        setData: mockSetData,
        patch: mockPatch,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const nameInput = screen.getByLabelText(/name/i);
      fireEvent.blur(nameInput);

      expect(screen.getByText(/name is too long/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('calls patch on form submit with valid data', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const form = screen.getByRole('button', { name: /save profile/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPatch).toHaveBeenCalled();
    });

    it('does not call patch when validation fails', () => {
      mockedUseForm.mockReturnValue({
        data: { name: '', email: 'john@example.com' },
        setData: mockSetData,
        patch: mockPatch,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const form = screen.getByRole('button', { name: /save profile/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPatch).not.toHaveBeenCalled();
    });
  });

  // ============================================
  // Server error tests
  // ============================================

  describe('server errors', () => {
    it('displays server name error', () => {
      mockedUseForm.mockReturnValue({
        data: { name: 'John', email: 'john@example.com' },
        setData: mockSetData,
        patch: mockPatch,
        processing: false,
        errors: { name: 'The name field is required.' },
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      expect(screen.getByText(/the name field is required/i)).toBeInTheDocument();
    });

    it('displays server email error', () => {
      mockedUseForm.mockReturnValue({
        data: { name: 'John', email: 'john@example.com' },
        setData: mockSetData,
        patch: mockPatch,
        processing: false,
        errors: { email: 'The email has already been taken.' },
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      expect(screen.getByText(/the email has already been taken/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Processing state tests
  // ============================================

  describe('processing state', () => {
    it('disables save button when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { name: 'John', email: 'john@example.com' },
        setData: mockSetData,
        patch: mockPatch,
        processing: true,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const submitButton = screen.getByRole('button', { name: /save profile/i });
      expect(submitButton).toBeDisabled();
    });
  });

  // ============================================
  // Success feedback tests
  // ============================================

  describe('success feedback', () => {
    it('shows success message when recentlySuccessful is true', () => {
      mockedUseForm.mockReturnValue({
        data: { name: 'John', email: 'john@example.com' },
        setData: mockSetData,
        patch: mockPatch,
        processing: false,
        errors: {},
        recentlySuccessful: true,
      } as ReturnType<typeof useForm>);

      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      expect(screen.getByText(/changes saved successfully/i)).toBeInTheDocument();
    });

    it('does not show success message when recentlySuccessful is false', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      expect(screen.queryByText(/changes saved successfully/i)).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('name input has proper label', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const nameInput = screen.getByLabelText(/name/i);
      expect(nameInput).toBeInTheDocument();
    });

    it('email input has proper label', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const emailInput = screen.getByLabelText(/email/i);
      expect(emailInput).toBeInTheDocument();
    });

    it('name input has autocomplete=name', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const nameInput = screen.getByLabelText(/name/i);
      expect(nameInput).toHaveAttribute('autocomplete', 'name');
    });

    it('email input has autocomplete=username', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const emailInput = screen.getByLabelText(/email/i);
      expect(emailInput).toHaveAttribute('autocomplete', 'username');
    });

    it('name input has required attribute', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const nameInput = screen.getByLabelText(/name/i);
      expect(nameInput).toHaveAttribute('required');
    });

    it('email input has required attribute', () => {
      render(<UpdateProfileInformationForm mustVerifyEmail={false} />);

      const emailInput = screen.getByLabelText(/email/i);
      expect(emailInput).toHaveAttribute('required');
    });
  });
});
