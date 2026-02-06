import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';

import { createRef } from 'react';

import { Textarea } from './textarea';

describe('Textarea', () => {
  const user = userEvent.setup();

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders textarea element', () => {
      render(<Textarea />);

      expect(screen.getByRole('textbox')).toBeInTheDocument();
    });

    it('renders with placeholder', () => {
      render(<Textarea placeholder="Enter text here" />);

      expect(screen.getByPlaceholderText('Enter text here')).toBeInTheDocument();
    });

    it('renders with default value', () => {
      render(<Textarea defaultValue="Default text" />);

      expect(screen.getByDisplayValue('Default text')).toBeInTheDocument();
    });

    it('renders with controlled value', () => {
      render(<Textarea value="Controlled text" onChange={() => {}} />);

      expect(screen.getByDisplayValue('Controlled text')).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(Textarea.displayName).toBe('Textarea');
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has default textarea styling', () => {
      render(<Textarea />);

      const textarea = screen.getByRole('textbox');
      expect(textarea).toHaveClass('flex');
      expect(textarea).toHaveClass('min-h-[80px]');
      expect(textarea).toHaveClass('w-full');
      expect(textarea).toHaveClass('rounded-md');
      expect(textarea).toHaveClass('border');
    });

    it('applies custom className', () => {
      render(<Textarea className="custom-class" />);

      expect(screen.getByRole('textbox')).toHaveClass('custom-class');
    });

    it('merges custom className with defaults', () => {
      render(<Textarea className="h-32" />);

      const textarea = screen.getByRole('textbox');
      expect(textarea).toHaveClass('rounded-md');
      expect(textarea).toHaveClass('h-32');
    });
  });

  // ============================================
  // Interaction tests
  // ============================================

  describe('interactions', () => {
    it('accepts user input', async () => {
      render(<Textarea />);

      const textarea = screen.getByRole('textbox');
      await user.type(textarea, 'Hello World');

      expect(textarea).toHaveValue('Hello World');
    });

    it('accepts multiline input', async () => {
      render(<Textarea />);

      const textarea = screen.getByRole('textbox');
      await user.type(textarea, 'Line 1{Enter}Line 2');

      expect(textarea).toHaveValue('Line 1\nLine 2');
    });

    it('calls onChange handler', async () => {
      const handleChange = vi.fn();
      render(<Textarea onChange={handleChange} />);

      await user.type(screen.getByRole('textbox'), 'a');

      expect(handleChange).toHaveBeenCalled();
    });

    it('calls onFocus handler', async () => {
      const handleFocus = vi.fn();
      render(<Textarea onFocus={handleFocus} />);

      await user.click(screen.getByRole('textbox'));

      expect(handleFocus).toHaveBeenCalled();
    });

    it('calls onBlur handler', async () => {
      const handleBlur = vi.fn();
      render(<Textarea onBlur={handleBlur} />);

      const textarea = screen.getByRole('textbox');
      await user.click(textarea);
      await user.tab();

      expect(handleBlur).toHaveBeenCalled();
    });

    it('handles paste event', async () => {
      render(<Textarea />);

      const textarea = screen.getByRole('textbox');
      await user.click(textarea);
      await user.paste('Pasted text');

      expect(textarea).toHaveValue('Pasted text');
    });
  });

  // ============================================
  // Disabled state tests
  // ============================================

  describe('disabled state', () => {
    it('renders as disabled', () => {
      render(<Textarea disabled />);

      expect(screen.getByRole('textbox')).toBeDisabled();
    });

    it('has disabled styling', () => {
      render(<Textarea disabled />);

      const textarea = screen.getByRole('textbox');
      expect(textarea).toHaveClass('disabled:cursor-not-allowed');
      expect(textarea).toHaveClass('disabled:opacity-50');
    });

    it('does not accept input when disabled', async () => {
      render(<Textarea disabled />);

      const textarea = screen.getByRole('textbox');
      await user.type(textarea, 'test');

      expect(textarea).toHaveValue('');
    });
  });

  // ============================================
  // ReadOnly state tests
  // ============================================

  describe('readOnly state', () => {
    it('renders as readOnly', () => {
      render(<Textarea readOnly />);

      expect(screen.getByRole('textbox')).toHaveAttribute('readonly');
    });

    it('does not accept input when readOnly', async () => {
      render(<Textarea readOnly defaultValue="readonly" />);

      const textarea = screen.getByRole('textbox');
      await user.type(textarea, 'new text');

      expect(textarea).toHaveValue('readonly');
    });
  });

  // ============================================
  // Required state tests
  // ============================================

  describe('required state', () => {
    it('renders as required', () => {
      render(<Textarea required />);

      expect(screen.getByRole('textbox')).toBeRequired();
    });

    it('has required attribute', () => {
      render(<Textarea required />);

      expect(screen.getByRole('textbox')).toHaveAttribute('required');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to textarea element', () => {
      const ref = createRef<HTMLTextAreaElement>();
      render(<Textarea ref={ref} />);

      expect(ref.current).toBeInstanceOf(HTMLTextAreaElement);
    });

    it('allows programmatic focus via ref', () => {
      const ref = createRef<HTMLTextAreaElement>();
      render(<Textarea ref={ref} />);

      ref.current?.focus();

      expect(ref.current).toHaveFocus();
    });

    it('allows programmatic blur via ref', () => {
      const ref = createRef<HTMLTextAreaElement>();
      render(<Textarea ref={ref} />);

      ref.current?.focus();
      expect(ref.current).toHaveFocus();

      ref.current?.blur();
      expect(ref.current).not.toHaveFocus();
    });

    it('allows reading value via ref', async () => {
      const ref = createRef<HTMLTextAreaElement>();
      render(<Textarea ref={ref} />);

      await user.type(screen.getByRole('textbox'), 'Test value');

      expect(ref.current?.value).toBe('Test value');
    });

    it('allows setting value via ref', () => {
      const ref = createRef<HTMLTextAreaElement>();
      render(<Textarea ref={ref} />);

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
      render(<Textarea name="message" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('name', 'message');
    });

    it('passes id attribute', () => {
      render(<Textarea id="comment" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('id', 'comment');
    });

    it('passes rows attribute', () => {
      render(<Textarea rows={5} />);

      expect(screen.getByRole('textbox')).toHaveAttribute('rows', '5');
    });

    it('passes cols attribute', () => {
      render(<Textarea cols={40} />);

      expect(screen.getByRole('textbox')).toHaveAttribute('cols', '40');
    });

    it('passes maxLength attribute', () => {
      render(<Textarea maxLength={200} />);

      expect(screen.getByRole('textbox')).toHaveAttribute('maxLength', '200');
    });

    it('passes aria-label attribute', () => {
      render(<Textarea aria-label="Comment input" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('aria-label', 'Comment input');
    });

    it('passes aria-describedby attribute', () => {
      render(<Textarea aria-describedby="help-text" />);

      expect(screen.getByRole('textbox')).toHaveAttribute('aria-describedby', 'help-text');
    });
  });

  // ============================================
  // Resize behavior tests
  // ============================================

  describe('resize behavior', () => {
    it('allows custom resize class', () => {
      render(<Textarea className="resize-none" />);

      expect(screen.getByRole('textbox')).toHaveClass('resize-none');
    });

    it('allows vertical resize class', () => {
      render(<Textarea className="resize-y" />);

      expect(screen.getByRole('textbox')).toHaveClass('resize-y');
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('is focusable', async () => {
      render(<Textarea />);

      const textarea = screen.getByRole('textbox');
      await user.tab();

      expect(textarea).toHaveFocus();
    });

    it('has accessible name when aria-label provided', () => {
      render(<Textarea aria-label="Feedback message" />);

      expect(screen.getByRole('textbox', { name: 'Feedback message' })).toBeInTheDocument();
    });
  });

  // ============================================
  // Min height tests
  // ============================================

  describe('min height', () => {
    it('has default min-height', () => {
      render(<Textarea />);

      expect(screen.getByRole('textbox')).toHaveClass('min-h-[80px]');
    });

    it('allows custom min-height', () => {
      render(<Textarea className="min-h-[120px]" />);

      expect(screen.getByRole('textbox')).toHaveClass('min-h-[120px]');
    });
  });
});
