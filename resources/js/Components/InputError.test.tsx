import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';

import InputError from './InputError';

describe('InputError', () => {
  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders error message when provided', () => {
      render(<InputError message="This field is required" />);

      expect(screen.getByText('This field is required')).toBeInTheDocument();
    });

    it('renders nothing when message is undefined', () => {
      const { container } = render(<InputError />);

      expect(container.firstChild).toBeNull();
    });

    it('renders nothing when message is empty string', () => {
      const { container } = render(<InputError message="" />);

      expect(container.firstChild).toBeNull();
    });

    it('renders as paragraph element', () => {
      render(<InputError message="Error" />);

      expect(screen.getByText('Error').tagName).toBe('P');
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has default error styling classes', () => {
      render(<InputError message="Error" />);

      const element = screen.getByText('Error');
      expect(element).toHaveClass('text-sm');
      expect(element).toHaveClass('text-destructive');
    });

    it('applies custom className', () => {
      render(<InputError message="Error" className="custom-class" />);

      expect(screen.getByText('Error')).toHaveClass('custom-class');
    });

    it('merges custom className with default classes', () => {
      render(<InputError message="Error" className="mt-4" />);

      const element = screen.getByText('Error');
      expect(element).toHaveClass('text-sm');
      expect(element).toHaveClass('text-destructive');
      expect(element).toHaveClass('mt-4');
    });

    it('allows className to override default styles', () => {
      render(<InputError message="Error" className="text-lg" />);

      const element = screen.getByText('Error');
      expect(element).toHaveClass('text-lg');
    });
  });

  // ============================================
  // Different error message tests
  // ============================================

  describe('different error messages', () => {
    it('displays email validation error', () => {
      render(<InputError message="Please enter a valid email address" />);

      expect(screen.getByText('Please enter a valid email address')).toBeInTheDocument();
    });

    it('displays password validation error', () => {
      render(<InputError message="Password must be at least 8 characters" />);

      expect(screen.getByText('Password must be at least 8 characters')).toBeInTheDocument();
    });

    it('displays required field error', () => {
      render(<InputError message="This field is required" />);

      expect(screen.getByText('This field is required')).toBeInTheDocument();
    });

    it('displays long error message', () => {
      const longMessage =
        'This is a very long error message that might wrap to multiple lines in a form';
      render(<InputError message={longMessage} />);

      expect(screen.getByText(longMessage)).toBeInTheDocument();
    });

    it('displays error with special characters', () => {
      render(<InputError message="Error: Field cannot contain < or >" />);

      expect(screen.getByText(/Error: Field cannot contain/)).toBeInTheDocument();
    });
  });

  // ============================================
  // Conditional rendering tests
  // ============================================

  describe('conditional rendering', () => {
    it('handles message changing from undefined to string', () => {
      const { rerender } = render(<InputError message={undefined} />);

      expect(screen.queryByRole('paragraph')).not.toBeInTheDocument();

      rerender(<InputError message="New error" />);

      expect(screen.getByText('New error')).toBeInTheDocument();
    });

    it('handles message changing from string to undefined', () => {
      const { rerender } = render(<InputError message="Error" />);

      expect(screen.getByText('Error')).toBeInTheDocument();

      rerender(<InputError message={undefined} />);

      expect(screen.queryByText('Error')).not.toBeInTheDocument();
    });

    it('updates when message changes', () => {
      const { rerender } = render(<InputError message="First error" />);

      expect(screen.getByText('First error')).toBeInTheDocument();

      rerender(<InputError message="Second error" />);

      expect(screen.queryByText('First error')).not.toBeInTheDocument();
      expect(screen.getByText('Second error')).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('error message is readable by screen readers', () => {
      render(<InputError message="Error message" />);

      const error = screen.getByText('Error message');
      expect(error).toBeInTheDocument();
      expect(error).toBeVisible();
    });
  });
});
