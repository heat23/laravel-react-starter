import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import { createRef } from 'react';

import TextInput from './TextInput';

describe('TextInput', () => {
  const user = userEvent.setup();

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders input element', () => {
      render(<TextInput />);

      expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('renders with placeholder', () => {
      render(<TextInput placeholder="Enter text" />);

      expect(screen.getByPlaceholderText('Enter text')).toBeInTheDocument();
    });

    it('renders with default value', () => {
      render(<TextInput defaultValue="Default text" />);

      expect(screen.getByDisplayValue('Default text')).toBeInTheDocument();
    });

    it('renders with controlled value', () => {
      render(<TextInput value="Controlled text" onChange={() => {}} />);

      expect(screen.getByDisplayValue('Controlled text')).toBeInTheDocument();
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has default styling classes', () => {
      render(<TextInput />);

      const input = screen.getByRole('textbox');
      expect(input).toHaveClass('flex');
      expect(input).toHaveClass('h-10');
      expect(input).toHaveClass('w-full');
      expect(input).toHaveClass('rounded-md');
    });

    it('applies custom className', () => {
      render(<TextInput className="custom-class" />);

      expect(screen.getByRole('textbox')).toHaveClass('custom-class');
    });

    it('merges custom className with default classes', () => {
      render(<TextInput className="mt-4" />);

      const input = screen.getByRole('textbox');
      expect(input).toHaveClass('rounded-md');
      expect(input).toHaveClass('mt-4');
    });
  });

  // ============================================
  // isFocused prop tests
  // ============================================

  describe('isFocused prop', () => {
    it('does not auto-focus by default', () => {
      render(<TextInput />);

      expect(screen.getByRole('textbox')).not.toHaveFocus();
    });

    it('auto-focuses when isFocused is true', async () => {
      render(<TextInput isFocused={true} />);

      await waitFor(() => {
        expect(screen.getByRole('textbox')).toHaveFocus();
      });
    });

    it('does not auto-focus when isFocused is false', () => {
      render(<TextInput isFocused={false} />);

      expect(screen.getByRole('textbox')).not.toHaveFocus();
    });

    it('focuses when isFocused changes to true', async () => {
      const { rerender } = render(<TextInput isFocused={false} />);

      expect(screen.getByRole('textbox')).not.toHaveFocus();

      rerender(<TextInput isFocused={true} />);

      await waitFor(() => {
        expect(screen.getByRole('textbox')).toHaveFocus();
      });
    });
  });

  // ============================================
  // Input types tests
  // ============================================

  describe('input types', () => {
    it('renders text type by default', () => {
      render(<TextInput />);

      // textbox role is used for type="text"
      expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('renders email type', () => {
      render(<TextInput type="email" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('type', 'email');
    });

    it('renders password type', () => {
      render(<TextInput type="password" />);

      // Password inputs don't have a role
      const input = document.querySelector('input[type="password"]');
      expect(input).toBeInTheDocument();
    });

    it('renders tel type', () => {
      render(<TextInput type="tel" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('type', 'tel');
    });

    it('renders url type', () => {
      render(<TextInput type="url" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('type', 'url');
    });
  });

  // ============================================
  // Interaction tests
  // ============================================

  describe('interactions', () => {
    it('accepts user input', async () => {
      render(<TextInput />);

      const input = screen.getByRole('textbox');
      await user.type(input, 'Hello World');

      expect(input).toHaveValue('Hello World');
    });

    it('calls onChange handler', async () => {
      const handleChange = vi.fn();
      render(<TextInput onChange={handleChange} />);

      await user.type(screen.getByRole('textbox'), 'a');

      expect(handleChange).toHaveBeenCalled();
    });

    it('calls onFocus handler', async () => {
      const handleFocus = vi.fn();
      render(<TextInput onFocus={handleFocus} />);

      await user.click(screen.getByRole('textbox'));

      expect(handleFocus).toHaveBeenCalled();
    });

    it('calls onBlur handler', async () => {
      const handleBlur = vi.fn();
      render(<TextInput onBlur={handleBlur} />);

      const input = screen.getByRole('textbox');
      await user.click(input);
      await user.tab();

      expect(handleBlur).toHaveBeenCalled();
    });

    it('handles paste event', async () => {
      render(<TextInput />);

      const input = screen.getByRole('textbox');
      await user.click(input);
      await user.paste('Pasted text');

      expect(input).toHaveValue('Pasted text');
    });
  });

  // ============================================
  // Disabled state tests
  // ============================================

  describe('disabled state', () => {
    it('renders as disabled', () => {
      render(<TextInput disabled />);

      expect(screen.getByRole('textbox')).toBeDisabled();
    });

    it('has disabled styling', () => {
      render(<TextInput disabled />);

      expect(screen.getByRole('textbox')).toHaveClass('disabled:cursor-not-allowed');
      expect(screen.getByRole('textbox')).toHaveClass('disabled:opacity-50');
    });

    it('does not accept input when disabled', async () => {
      render(<TextInput disabled />);

      const input = screen.getByRole('textbox');
      await user.type(input, 'test');

      expect(input).toHaveValue('');
    });
  });

  // ============================================
  // ReadOnly state tests
  // ============================================

  describe('readOnly state', () => {
    it('renders as readOnly', () => {
      render(<TextInput readOnly />);

      expect(screen.getByRole('textbox')).toHaveAttribute('readonly');
    });

    it('does not accept input when readOnly', async () => {
      render(<TextInput readOnly defaultValue="readonly" />);

      const input = screen.getByRole('textbox');
      await user.type(input, 'new text');

      expect(input).toHaveValue('readonly');
    });
  });

  // ============================================
  // Required state tests
  // ============================================

  describe('required state', () => {
    it('renders as required', () => {
      render(<TextInput required />);

      expect(screen.getByRole('textbox')).toBeRequired();
    });

    it('has required attribute', () => {
      render(<TextInput required />);

      expect(screen.getByRole('textbox')).toHaveAttribute('required');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to input element', () => {
      const ref = createRef<HTMLInputElement>();
      render(<TextInput ref={ref} />);

      expect(ref.current).toBeInstanceOf(HTMLInputElement);
    });

    it('allows programmatic focus via ref', () => {
      const ref = createRef<HTMLInputElement>();
      render(<TextInput ref={ref} />);

      ref.current?.focus();

      expect(ref.current).toHaveFocus();
    });

    it('allows programmatic blur via ref', () => {
      const ref = createRef<HTMLInputElement>();
      render(<TextInput ref={ref} />);

      ref.current?.focus();
      expect(ref.current).toHaveFocus();

      ref.current?.blur();
      expect(ref.current).not.toHaveFocus();
    });

    it('allows reading value via ref', async () => {
      const ref = createRef<HTMLInputElement>();
      render(<TextInput ref={ref} />);

      await user.type(screen.getByRole('textbox'), 'Test value');

      expect(ref.current?.value).toBe('Test value');
    });

    it('allows setting value via ref', () => {
      const ref = createRef<HTMLInputElement>();
      render(<TextInput ref={ref} />);

      if (ref.current) {
        ref.current.value = 'Set via ref';
      }

      expect(screen.getByRole('textbox')).toHaveValue('Set via ref');
    });
  });

  // ============================================
  // HTML attributes tests
  // ============================================

  describe('HTML attributes', () => {
    it('passes name attribute', () => {
      render(<TextInput name="username" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('name', 'username');
    });

    it('passes id attribute', () => {
      render(<TextInput id="user-input" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('id', 'user-input');
    });

    it('passes maxLength attribute', () => {
      render(<TextInput maxLength={50} />);

      expect(screen.getByRole('textbox')).toHaveAttribute('maxLength', '50');
    });

    it('passes minLength attribute', () => {
      render(<TextInput minLength={3} />);

      expect(screen.getByRole('textbox')).toHaveAttribute('minLength', '3');
    });

    it('passes autoComplete attribute', () => {
      render(<TextInput autoComplete="email" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('autoComplete', 'email');
    });

    it('passes pattern attribute', () => {
      render(<TextInput pattern="[0-9]*" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('pattern', '[0-9]*');
    });

    it('passes aria-label attribute', () => {
      render(<TextInput aria-label="Username input" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('aria-label', 'Username input');
    });

    it('passes aria-describedby attribute', () => {
      render(<TextInput aria-describedby="help-text" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('aria-describedby', 'help-text');
    });
  });

  // ============================================
  // displayName tests
  // ============================================

  describe('displayName', () => {
    it('has correct displayName', () => {
      expect(TextInput.displayName).toBe('TextInput');
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('is focusable', async () => {
      render(<TextInput />);

      const input = screen.getByRole('textbox');
      await user.tab();

      expect(input).toHaveFocus();
    });

    it('has accessible name when aria-label provided', () => {
      render(<TextInput aria-label="Email address" />);

      expect(screen.getByRole('textbox', { name: 'Email address' })).toBeInTheDocument();
    });
  });
});
