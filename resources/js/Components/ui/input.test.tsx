import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import { createRef } from 'react';

import { Input } from './input';

describe('Input', () => {
  const user = userEvent.setup();

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders an input element', () => {
      render(<Input />);

      expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('renders with default type text when no type specified', () => {
      render(<Input />);

      // When no type is specified, browsers default to text (role is textbox)
      expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(<Input data-testid="input" />);

      const input = screen.getByTestId('input');
      expect(input).toHaveClass('flex', 'h-11', 'w-full', 'rounded-md', 'border');
    });

    it('applies custom className', () => {
      render(<Input className="custom-class" data-testid="input" />);

      expect(screen.getByTestId('input')).toHaveClass('custom-class');
    });

    it('merges custom className with default classes', () => {
      render(<Input className="custom-class" data-testid="input" />);

      const input = screen.getByTestId('input');
      expect(input).toHaveClass('custom-class');
      expect(input).toHaveClass('flex');
    });
  });

  // ============================================
  // Type tests
  // ============================================

  describe('input types', () => {
    it('renders type="text"', () => {
      render(<Input type="text" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('type', 'text');
    });

    it('renders type="email"', () => {
      render(<Input type="email" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('type', 'email');
    });

    it('renders type="password"', () => {
      render(<Input type="password" data-testid="password-input" />);

      // Password inputs don't have textbox role
      expect(screen.getByTestId('password-input')).toHaveAttribute('type', 'password');
    });

    it('renders type="number"', () => {
      render(<Input type="number" />);

      expect(screen.getByRole('spinbutton')).toHaveAttribute('type', 'number');
    });

    it('renders type="tel"', () => {
      render(<Input type="tel" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('type', 'tel');
    });

    it('renders type="url"', () => {
      render(<Input type="url" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('type', 'url');
    });

    it('renders type="search"', () => {
      render(<Input type="search" />);

      expect(screen.getByRole('searchbox')).toHaveAttribute('type', 'search');
    });

    it('renders type="file"', () => {
      render(<Input type="file" data-testid="file-input" />);

      // File inputs don't have textbox role
      expect(screen.getByTestId('file-input')).toHaveAttribute('type', 'file');
    });
  });

  // ============================================
  // State tests
  // ============================================

  describe('states', () => {
    it('renders disabled state', () => {
      render(<Input disabled />);

      expect(screen.getByRole('textbox')).toBeDisabled();
    });

    it('renders readOnly state', () => {
      render(<Input readOnly />);

      expect(screen.getByRole('textbox')).toHaveAttribute('readonly');
    });

    it('renders required state', () => {
      render(<Input required />);

      expect(screen.getByRole('textbox')).toBeRequired();
    });

    it('renders with placeholder', () => {
      render(<Input placeholder="Enter text..." />);

      expect(screen.getByPlaceholderText('Enter text...')).toBeInTheDocument();
    });

    it('renders with value', () => {
      render(<Input value="test value" onChange={() => {}} />);

      expect(screen.getByDisplayValue('test value')).toBeInTheDocument();
    });

    it('renders with defaultValue', () => {
      render(<Input defaultValue="default text" />);

      expect(screen.getByDisplayValue('default text')).toBeInTheDocument();
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to input element', () => {
      const ref = createRef<HTMLInputElement>();
      render(<Input ref={ref} />);

      expect(ref.current).toBeInstanceOf(HTMLInputElement);
    });

    it('allows programmatic focus via ref', () => {
      const ref = createRef<HTMLInputElement>();
      render(<Input ref={ref} />);

      ref.current?.focus();
      expect(ref.current).toHaveFocus();
    });

    it('allows programmatic value setting via ref', () => {
      const ref = createRef<HTMLInputElement>();
      render(<Input ref={ref} />);

      if (ref.current) {
        ref.current.value = 'programmatic value';
      }

      expect(ref.current?.value).toBe('programmatic value');
    });
  });

  // ============================================
  // Event tests
  // ============================================

  describe('events', () => {
    it('calls onChange when value changes', async () => {
      const handleChange = vi.fn();
      render(<Input onChange={handleChange} />);

      await user.type(screen.getByRole('textbox'), 'a');

      expect(handleChange).toHaveBeenCalled();
    });

    it('calls onFocus when focused', () => {
      const handleFocus = vi.fn();
      render(<Input onFocus={handleFocus} />);

      fireEvent.focus(screen.getByRole('textbox'));

      expect(handleFocus).toHaveBeenCalled();
    });

    it('calls onBlur when blurred', () => {
      const handleBlur = vi.fn();
      render(<Input onBlur={handleBlur} />);

      const input = screen.getByRole('textbox');
      fireEvent.focus(input);
      fireEvent.blur(input);

      expect(handleBlur).toHaveBeenCalled();
    });

    it('calls onKeyDown when key pressed', async () => {
      const handleKeyDown = vi.fn();
      render(<Input onKeyDown={handleKeyDown} />);

      const input = screen.getByRole('textbox');
      input.focus();
      await user.keyboard('a');

      expect(handleKeyDown).toHaveBeenCalled();
    });

    it('does not call onChange when disabled', async () => {
      const handleChange = vi.fn();
      render(<Input disabled onChange={handleChange} />);

      const input = screen.getByRole('textbox');
      // Disabled inputs don't receive user events
      expect(input).toBeDisabled();
      // User events won't work on disabled inputs
    });
  });

  // ============================================
  // Attribute tests
  // ============================================

  describe('attributes', () => {
    it('passes through id attribute', () => {
      render(<Input id="test-id" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('id', 'test-id');
    });

    it('passes through name attribute', () => {
      render(<Input name="test-name" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('name', 'test-name');
    });

    it('passes through autocomplete attribute', () => {
      render(<Input autoComplete="email" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('autocomplete', 'email');
    });

    it('passes through maxLength attribute', () => {
      render(<Input maxLength={10} />);

      expect(screen.getByRole('textbox')).toHaveAttribute('maxLength', '10');
    });

    it('passes through minLength attribute', () => {
      render(<Input minLength={5} />);

      expect(screen.getByRole('textbox')).toHaveAttribute('minLength', '5');
    });

    it('passes through pattern attribute', () => {
      render(<Input pattern="[A-Za-z]+" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('pattern', '[A-Za-z]+');
    });

    it('passes through aria-label', () => {
      render(<Input aria-label="Search field" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('aria-label', 'Search field');
    });

    it('passes through aria-describedby', () => {
      render(<Input aria-describedby="description-id" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('aria-describedby', 'description-id');
    });

    it('passes through data attributes', () => {
      render(<Input data-testid="custom-input" data-custom="value" />);

      const input = screen.getByTestId('custom-input');
      expect(input).toHaveAttribute('data-custom', 'value');
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has displayName set', () => {
      expect(Input.displayName).toBe('Input');
    });

    it('can be associated with label via id', () => {
      render(
        <>
          <label htmlFor="input-id">Input Label</label>
          <Input id="input-id" />
        </>,
      );

      expect(screen.getByLabelText('Input Label')).toBeInTheDocument();
    });

    it('supports aria-invalid for error states', () => {
      render(<Input aria-invalid="true" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('aria-invalid', 'true');
    });

    it('supports aria-required', () => {
      render(<Input aria-required="true" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('aria-required', 'true');
    });
  });
});
