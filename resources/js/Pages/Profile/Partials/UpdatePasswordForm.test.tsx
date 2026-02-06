import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm } from '@inertiajs/react';

import UpdatePasswordForm from './UpdatePasswordForm';

// Mock useForm from Inertia
const mockPut = vi.fn();
const mockSetData = vi.fn();
const mockReset = vi.fn();
const mockSetError = vi.fn();
const mockClearErrors = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { current_password: '', password: '', password_confirmation: '' },
      setData: mockSetData,
      put: mockPut,
      reset: mockReset,
      setError: mockSetError,
      clearErrors: mockClearErrors,
      processing: false,
      errors: {},
      recentlySuccessful: false,
    })),
  };
});

const mockedUseForm = vi.mocked(useForm);

describe('UpdatePasswordForm', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    // Reset to default mock state
    mockedUseForm.mockReturnValue({
      data: { current_password: '', password: '', password_confirmation: '' },
      setData: mockSetData,
      put: mockPut,
      reset: mockReset,
      setError: mockSetError,
      clearErrors: mockClearErrors,
      processing: false,
      errors: {},
      recentlySuccessful: false,
    } as ReturnType<typeof useForm>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the form', () => {
      render(<UpdatePasswordForm />);

      expect(screen.getByLabelText(/current password/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/new password/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/confirm password/i)).toBeInTheDocument();
    });

    it('renders update password button', () => {
      render(<UpdatePasswordForm />);

      expect(screen.getByRole('button', { name: /update password/i })).toBeInTheDocument();
    });

    it('applies custom className', () => {
      const { container } = render(<UpdatePasswordForm className="custom-class" />);

      expect(container.querySelector('.custom-class')).toBeInTheDocument();
    });

    it('all password fields are of type password', () => {
      render(<UpdatePasswordForm />);

      expect(screen.getByLabelText(/current password/i)).toHaveAttribute('type', 'password');
      expect(screen.getByLabelText(/new password/i)).toHaveAttribute('type', 'password');
      expect(screen.getByLabelText(/confirm password/i)).toHaveAttribute('type', 'password');
    });
  });

  // ============================================
  // Form input tests
  // ============================================

  describe('form input', () => {
    it('calls setData when current password changes', async () => {
      render(<UpdatePasswordForm />);

      const currentPasswordInput = screen.getByLabelText(/current password/i);
      await user.type(currentPasswordInput, 'oldpassword');

      expect(mockSetData).toHaveBeenCalledWith('current_password', expect.any(String));
    });

    it('calls setData when new password changes', async () => {
      render(<UpdatePasswordForm />);

      const newPasswordInput = screen.getByLabelText(/new password/i);
      await user.type(newPasswordInput, 'newpassword123');

      expect(mockSetData).toHaveBeenCalledWith('password', expect.any(String));
    });

    it('calls setData when confirm password changes', async () => {
      render(<UpdatePasswordForm />);

      const confirmPasswordInput = screen.getByLabelText(/confirm password/i);
      await user.type(confirmPasswordInput, 'newpassword123');

      expect(mockSetData).toHaveBeenCalledWith('password_confirmation', expect.any(String));
    });
  });

  // ============================================
  // Button state tests
  // ============================================

  describe('button state', () => {
    it('disables update button when all fields are empty', () => {
      render(<UpdatePasswordForm />);

      const submitButton = screen.getByRole('button', { name: /update password/i });
      expect(submitButton).toBeDisabled();
    });

    it('disables update button when only current password is filled', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: '', password_confirmation: '' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const submitButton = screen.getByRole('button', { name: /update password/i });
      expect(submitButton).toBeDisabled();
    });

    it('disables update button when password confirmation is missing', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: '' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const submitButton = screen.getByRole('button', { name: /update password/i });
      expect(submitButton).toBeDisabled();
    });

    it('enables update button when all fields are filled', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: 'newpass' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const submitButton = screen.getByRole('button', { name: /update password/i });
      expect(submitButton).not.toBeDisabled();
    });

    it('disables update button when processing', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: 'newpass' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: true,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const submitButton = screen.getByRole('button', { name: /updating/i });
      expect(submitButton).toBeDisabled();
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('clears errors before validation on submit', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: 'newpass' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const form = screen.getByRole('button', { name: /update password/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockClearErrors).toHaveBeenCalled();
    });

    it('sets client error when current password is missing', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: '', password: 'newpass', password_confirmation: 'newpass' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const form = screen.getByRole('button', { name: /update password/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockSetError).toHaveBeenCalledWith(
        expect.objectContaining({
          current_password: 'Current password is required.',
        })
      );
      expect(mockPut).not.toHaveBeenCalled();
    });

    it('sets client error when new password is missing', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: '', password_confirmation: 'newpass' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const form = screen.getByRole('button', { name: /update password/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockSetError).toHaveBeenCalledWith(
        expect.objectContaining({
          password: 'New password is required.',
        })
      );
      expect(mockPut).not.toHaveBeenCalled();
    });

    it('sets client error when confirmation is missing', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: '' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const form = screen.getByRole('button', { name: /update password/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockSetError).toHaveBeenCalledWith(
        expect.objectContaining({
          password_confirmation: 'Please confirm your new password.',
        })
      );
      expect(mockPut).not.toHaveBeenCalled();
    });

    it('calls put when all fields are valid', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: 'newpass' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const form = screen.getByRole('button', { name: /update password/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPut).toHaveBeenCalled();
    });

    it('passes preserveScroll option to put', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: 'newpass' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      const form = screen.getByRole('button', { name: /update password/i }).closest('form')!;
      fireEvent.submit(form);

      expect(mockPut).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          preserveScroll: true,
        })
      );
    });
  });

  // ============================================
  // Server error tests
  // ============================================

  describe('server errors', () => {
    it('displays current password error', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: 'newpass' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: { current_password: 'The provided password is incorrect.' },
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      expect(screen.getByText(/the provided password is incorrect/i)).toBeInTheDocument();
    });

    it('displays new password error', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'short', password_confirmation: 'short' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: { password: 'The password must be at least 8 characters.' },
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      expect(screen.getByText(/the password must be at least 8 characters/i)).toBeInTheDocument();
    });

    it('displays confirmation error', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: 'oldpass', password: 'newpass', password_confirmation: 'different' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: { password_confirmation: 'The password confirmation does not match.' },
        recentlySuccessful: false,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      expect(screen.getByText(/the password confirmation does not match/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Success feedback tests
  // ============================================

  describe('success feedback', () => {
    it('shows success message when recentlySuccessful is true', () => {
      mockedUseForm.mockReturnValue({
        data: { current_password: '', password: '', password_confirmation: '' },
        setData: mockSetData,
        put: mockPut,
        reset: mockReset,
        setError: mockSetError,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
        recentlySuccessful: true,
      } as ReturnType<typeof useForm>);

      render(<UpdatePasswordForm />);

      expect(screen.getByText(/changes saved successfully/i)).toBeInTheDocument();
    });

    it('does not show success message when recentlySuccessful is false', () => {
      render(<UpdatePasswordForm />);

      expect(screen.queryByText(/changes saved successfully/i)).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('current password input has proper label', () => {
      render(<UpdatePasswordForm />);

      const input = screen.getByLabelText(/current password/i);
      expect(input).toBeInTheDocument();
    });

    it('new password input has proper label', () => {
      render(<UpdatePasswordForm />);

      const input = screen.getByLabelText(/new password/i);
      expect(input).toBeInTheDocument();
    });

    it('confirm password input has proper label', () => {
      render(<UpdatePasswordForm />);

      const input = screen.getByLabelText(/confirm password/i);
      expect(input).toBeInTheDocument();
    });

    it('current password has autocomplete=current-password', () => {
      render(<UpdatePasswordForm />);

      const input = screen.getByLabelText(/current password/i);
      expect(input).toHaveAttribute('autocomplete', 'current-password');
    });

    it('new password has autocomplete=new-password', () => {
      render(<UpdatePasswordForm />);

      const input = screen.getByLabelText(/new password/i);
      expect(input).toHaveAttribute('autocomplete', 'new-password');
    });

    it('confirm password has autocomplete=new-password', () => {
      render(<UpdatePasswordForm />);

      const input = screen.getByLabelText(/confirm password/i);
      expect(input).toHaveAttribute('autocomplete', 'new-password');
    });

    it('all password fields have required attribute', () => {
      render(<UpdatePasswordForm />);

      expect(screen.getByLabelText(/current password/i)).toHaveAttribute('required');
      expect(screen.getByLabelText(/new password/i)).toHaveAttribute('required');
      expect(screen.getByLabelText(/confirm password/i)).toHaveAttribute('required');
    });
  });
});
