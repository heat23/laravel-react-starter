import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { useForm } from '@inertiajs/react';

import DeleteUserForm from './DeleteUserForm';

// Mock useForm from Inertia
const mockDelete = vi.fn();
const mockSetData = vi.fn();
const mockReset = vi.fn();
const mockClearErrors = vi.fn();

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    useForm: vi.fn(() => ({
      data: { password: '' },
      setData: mockSetData,
      delete: mockDelete,
      reset: mockReset,
      clearErrors: mockClearErrors,
      processing: false,
      errors: {},
    })),
  };
});

const mockedUseForm = vi.mocked(useForm);

describe('DeleteUserForm', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    // Reset to default mock state
    mockedUseForm.mockReturnValue({
      data: { password: '' },
      setData: mockSetData,
      delete: mockDelete,
      reset: mockReset,
      clearErrors: mockClearErrors,
      processing: false,
      errors: {},
    } as ReturnType<typeof useForm>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders delete account trigger button', () => {
      render(<DeleteUserForm />);

      expect(screen.getByRole('button', { name: /delete account/i })).toBeInTheDocument();
    });

    it('trigger button has destructive styling', () => {
      render(<DeleteUserForm />);

      const button = screen.getByRole('button', { name: /delete account/i });
      expect(button).toHaveClass('text-destructive');
    });

    it('applies custom className', () => {
      const { container } = render(<DeleteUserForm className="custom-class" />);

      expect(container.querySelector('.custom-class')).toBeInTheDocument();
    });

    it('does not show dialog initially', () => {
      render(<DeleteUserForm />);

      expect(screen.queryByText(/are you sure you want to delete your account/i)).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Dialog tests
  // ============================================

  describe('dialog', () => {
    it('opens dialog when trigger button is clicked', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      expect(screen.getByText(/are you sure you want to delete your account/i)).toBeInTheDocument();
    });

    it('shows warning about permanent deletion', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      expect(screen.getByText(/this action cannot be undone/i)).toBeInTheDocument();
    });

    it('shows list of data that will be deleted', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      expect(screen.getByText(/all your projects and packages/i)).toBeInTheDocument();
      expect(screen.getByText(/all scan history and results/i)).toBeInTheDocument();
      expect(screen.getByText(/subscription and billing information/i)).toBeInTheDocument();
      expect(screen.getByText(/api tokens and integrations/i)).toBeInTheDocument();
    });

    it('shows password confirmation field', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      expect(screen.getByLabelText(/enter your password to confirm/i)).toBeInTheDocument();
    });

    it('shows cancel button in dialog', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      expect(screen.getByRole('button', { name: /cancel/i })).toBeInTheDocument();
    });

    it('shows delete confirmation button in dialog', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      // Look for the submit button inside the dialog
      const submitButtons = screen.getAllByRole('button').filter(
        btn => btn.getAttribute('type') === 'submit'
      );
      expect(submitButtons.length).toBeGreaterThanOrEqual(1);
    });
  });

  // ============================================
  // Dialog close tests
  // ============================================

  describe('dialog close behavior', () => {
    it('closes dialog when cancel is clicked', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const cancelButton = screen.getByRole('button', { name: /cancel/i });
      await user.click(cancelButton);

      await waitFor(() => {
        expect(screen.queryByText(/are you sure you want to delete your account/i)).not.toBeInTheDocument();
      });
    });

    it('clears errors when dialog is closed', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const cancelButton = screen.getByRole('button', { name: /cancel/i });
      await user.click(cancelButton);

      expect(mockClearErrors).toHaveBeenCalled();
    });

    it('resets form when dialog is closed', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const cancelButton = screen.getByRole('button', { name: /cancel/i });
      await user.click(cancelButton);

      expect(mockReset).toHaveBeenCalled();
    });
  });

  // ============================================
  // Password input tests
  // ============================================

  describe('password input', () => {
    it('calls setData when password changes', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const passwordInput = screen.getByLabelText(/enter your password to confirm/i);
      await user.type(passwordInput, 'mypassword');

      expect(mockSetData).toHaveBeenCalledWith('password', expect.any(String));
    });

    it('password input has type password', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const passwordInput = screen.getByLabelText(/enter your password to confirm/i);
      expect(passwordInput).toHaveAttribute('type', 'password');
    });

    it('password input has placeholder', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const passwordInput = screen.getByLabelText(/enter your password to confirm/i);
      expect(passwordInput).toHaveAttribute('placeholder', 'Your password');
    });
  });

  // ============================================
  // Delete button state tests
  // ============================================

  describe('delete button state', () => {
    it('disables delete button when password is empty', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      // Need to find the submit button
      const submitButtons = screen.getAllByRole('button').filter(
        btn => btn.getAttribute('type') === 'submit'
      );
      expect(submitButtons[0]).toBeDisabled();
    });

    it('enables delete button when password is entered', async () => {
      mockedUseForm.mockReturnValue({
        data: { password: 'mypassword' },
        setData: mockSetData,
        delete: mockDelete,
        reset: mockReset,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const submitButtons = screen.getAllByRole('button').filter(
        btn => btn.getAttribute('type') === 'submit'
      );
      expect(submitButtons[0]).not.toBeDisabled();
    });

    it('disables delete button when processing', async () => {
      mockedUseForm.mockReturnValue({
        data: { password: 'mypassword' },
        setData: mockSetData,
        delete: mockDelete,
        reset: mockReset,
        clearErrors: mockClearErrors,
        processing: true,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const submitButtons = screen.getAllByRole('button').filter(
        btn => btn.getAttribute('type') === 'submit'
      );
      expect(submitButtons[0]).toBeDisabled();
    });

    it('disables cancel button when processing', async () => {
      mockedUseForm.mockReturnValue({
        data: { password: 'mypassword' },
        setData: mockSetData,
        delete: mockDelete,
        reset: mockReset,
        clearErrors: mockClearErrors,
        processing: true,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const cancelButton = screen.getByRole('button', { name: /cancel/i });
      expect(cancelButton).toBeDisabled();
    });
  });

  // ============================================
  // Processing state tests
  // ============================================

  describe('processing state', () => {
    it('shows loading text when processing', async () => {
      mockedUseForm.mockReturnValue({
        data: { password: 'mypassword' },
        setData: mockSetData,
        delete: mockDelete,
        reset: mockReset,
        clearErrors: mockClearErrors,
        processing: true,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      expect(screen.getByText(/deleting/i)).toBeInTheDocument();
    });

    it('shows spinner when processing', async () => {
      mockedUseForm.mockReturnValue({
        data: { password: 'mypassword' },
        setData: mockSetData,
        delete: mockDelete,
        reset: mockReset,
        clearErrors: mockClearErrors,
        processing: true,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const spinner = document.querySelector('.animate-spin');
      expect(spinner).toBeInTheDocument();
    });
  });

  // ============================================
  // Form submission tests
  // ============================================

  describe('form submission', () => {
    it('calls delete on form submit', async () => {
      mockedUseForm.mockReturnValue({
        data: { password: 'mypassword' },
        setData: mockSetData,
        delete: mockDelete,
        reset: mockReset,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      // Find the form and submit
      const form = document.querySelector('form');
      fireEvent.submit(form!);

      expect(mockDelete).toHaveBeenCalled();
    });

    it('passes preserveScroll option to delete', async () => {
      mockedUseForm.mockReturnValue({
        data: { password: 'mypassword' },
        setData: mockSetData,
        delete: mockDelete,
        reset: mockReset,
        clearErrors: mockClearErrors,
        processing: false,
        errors: {},
      } as ReturnType<typeof useForm>);

      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const form = document.querySelector('form');
      fireEvent.submit(form!);

      expect(mockDelete).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          preserveScroll: true,
        })
      );
    });
  });

  // ============================================
  // Error handling tests
  // ============================================

  describe('error handling', () => {
    it('displays password error', async () => {
      mockedUseForm.mockReturnValue({
        data: { password: 'wrongpassword' },
        setData: mockSetData,
        delete: mockDelete,
        reset: mockReset,
        clearErrors: mockClearErrors,
        processing: false,
        errors: { password: 'The provided password is incorrect.' },
      } as ReturnType<typeof useForm>);

      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      expect(screen.getByText(/the provided password is incorrect/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('dialog has proper title', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const dialog = screen.getByRole('alertdialog');
      expect(dialog).toBeInTheDocument();
    });

    it('password input has proper label', async () => {
      render(<DeleteUserForm />);

      const triggerButton = screen.getByRole('button', { name: /delete account/i });
      await user.click(triggerButton);

      const passwordInput = screen.getByLabelText(/enter your password to confirm/i);
      expect(passwordInput).toBeInTheDocument();
    });
  });
});
