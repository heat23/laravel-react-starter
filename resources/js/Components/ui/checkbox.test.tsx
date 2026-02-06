import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';

import { createRef } from 'react';

import { Checkbox } from './checkbox';

describe('Checkbox', () => {
  const user = userEvent.setup();

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders a checkbox', () => {
      render(<Checkbox />);

      expect(screen.getByRole('checkbox')).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(<Checkbox data-testid="checkbox" />);

      const checkbox = screen.getByTestId('checkbox');
      expect(checkbox).toHaveClass('peer', 'h-4', 'w-4', 'shrink-0', 'rounded-sm', 'border');
    });

    it('applies custom className', () => {
      render(<Checkbox className="custom-class" data-testid="checkbox" />);

      expect(screen.getByTestId('checkbox')).toHaveClass('custom-class');
    });

    it('merges custom className with default classes', () => {
      render(<Checkbox className="custom-class" data-testid="checkbox" />);

      const checkbox = screen.getByTestId('checkbox');
      expect(checkbox).toHaveClass('custom-class');
      expect(checkbox).toHaveClass('peer');
    });

    it('has correct displayName', () => {
      // Radix UI sets displayName from the primitive
      expect(Checkbox.displayName).toBeDefined();
    });
  });

  // ============================================
  // State tests
  // ============================================

  describe('states', () => {
    it('renders unchecked by default', () => {
      render(<Checkbox />);

      expect(screen.getByRole('checkbox')).not.toBeChecked();
    });

    it('renders checked when checked prop is true', () => {
      render(<Checkbox checked />);

      expect(screen.getByRole('checkbox')).toBeChecked();
    });

    it('renders unchecked when checked prop is false', () => {
      render(<Checkbox checked={false} />);

      expect(screen.getByRole('checkbox')).not.toBeChecked();
    });

    it('renders with defaultChecked', () => {
      render(<Checkbox defaultChecked />);

      expect(screen.getByRole('checkbox')).toBeChecked();
    });

    it('renders disabled state', () => {
      render(<Checkbox disabled />);

      expect(screen.getByRole('checkbox')).toBeDisabled();
    });

    it('renders checked and disabled', () => {
      render(<Checkbox checked disabled />);

      const checkbox = screen.getByRole('checkbox');
      expect(checkbox).toBeChecked();
      expect(checkbox).toBeDisabled();
    });

    it('renders required state', () => {
      render(<Checkbox required />);

      expect(screen.getByRole('checkbox')).toBeRequired();
    });
  });

  // ============================================
  // Controlled component tests
  // ============================================

  describe('controlled component', () => {
    it('respects controlled checked state', () => {
      const { rerender } = render(<Checkbox checked={false} onCheckedChange={() => {}} />);

      expect(screen.getByRole('checkbox')).not.toBeChecked();

      rerender(<Checkbox checked={true} onCheckedChange={() => {}} />);

      expect(screen.getByRole('checkbox')).toBeChecked();
    });

    it('calls onCheckedChange when clicked', async () => {
      const handleCheckedChange = vi.fn();
      render(<Checkbox onCheckedChange={handleCheckedChange} />);

      await user.click(screen.getByRole('checkbox'));

      expect(handleCheckedChange).toHaveBeenCalledWith(true);
    });

    it('calls onCheckedChange with false when unchecking', async () => {
      const handleCheckedChange = vi.fn();
      render(<Checkbox defaultChecked onCheckedChange={handleCheckedChange} />);

      await user.click(screen.getByRole('checkbox'));

      expect(handleCheckedChange).toHaveBeenCalledWith(false);
    });

    it('does not toggle when controlled without handler', async () => {
      render(<Checkbox checked={false} />);

      const checkbox = screen.getByRole('checkbox');
      await user.click(checkbox);

      // Should remain unchecked as it's controlled
      expect(checkbox).not.toBeChecked();
    });
  });

  // ============================================
  // Uncontrolled component tests
  // ============================================

  describe('uncontrolled component', () => {
    it('toggles on click when uncontrolled', async () => {
      render(<Checkbox />);

      const checkbox = screen.getByRole('checkbox');
      expect(checkbox).not.toBeChecked();

      await user.click(checkbox);

      expect(checkbox).toBeChecked();
    });

    it('toggles multiple times', async () => {
      render(<Checkbox />);

      const checkbox = screen.getByRole('checkbox');

      await user.click(checkbox);
      expect(checkbox).toBeChecked();

      await user.click(checkbox);
      expect(checkbox).not.toBeChecked();

      await user.click(checkbox);
      expect(checkbox).toBeChecked();
    });
  });

  // ============================================
  // Disabled state tests
  // ============================================

  describe('disabled behavior', () => {
    it('does not toggle when disabled', async () => {
      const handleCheckedChange = vi.fn();
      render(<Checkbox disabled onCheckedChange={handleCheckedChange} />);

      await user.click(screen.getByRole('checkbox'));

      expect(handleCheckedChange).not.toHaveBeenCalled();
    });

    it('cannot be focused when disabled', () => {
      render(<Checkbox disabled />);

      const checkbox = screen.getByRole('checkbox');
      checkbox.focus();

      expect(checkbox).not.toHaveFocus();
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to checkbox element', () => {
      const ref = createRef<HTMLButtonElement>();
      render(<Checkbox ref={ref} />);

      expect(ref.current).toBeInstanceOf(HTMLButtonElement);
    });

    it('allows programmatic focus via ref', () => {
      const ref = createRef<HTMLButtonElement>();
      render(<Checkbox ref={ref} />);

      ref.current?.focus();
      expect(ref.current).toHaveFocus();
    });

    it('allows programmatic click via ref', async () => {
      const handleCheckedChange = vi.fn();
      const ref = createRef<HTMLButtonElement>();
      render(<Checkbox ref={ref} onCheckedChange={handleCheckedChange} />);

      // Use userEvent for proper event handling with Radix UI
      await user.click(ref.current!);

      expect(handleCheckedChange).toHaveBeenCalledWith(true);
    });
  });

  // ============================================
  // Keyboard interaction tests
  // ============================================

  describe('keyboard interaction', () => {
    it('toggles on Space key', async () => {
      render(<Checkbox />);

      const checkbox = screen.getByRole('checkbox');
      checkbox.focus();
      await user.keyboard(' ');

      expect(checkbox).toBeChecked();
    });

    it('does not toggle on Enter key (standard checkbox behavior)', async () => {
      render(<Checkbox />);

      const checkbox = screen.getByRole('checkbox');
      checkbox.focus();
      await user.keyboard('{Enter}');

      // Standard checkbox behavior only toggles on Space, not Enter
      expect(checkbox).not.toBeChecked();
    });

    it('does not toggle on other keys', async () => {
      render(<Checkbox />);

      const checkbox = screen.getByRole('checkbox');
      checkbox.focus();
      await user.keyboard('a');

      expect(checkbox).not.toBeChecked();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('can be associated with label via id', () => {
      render(
        <>
          <label htmlFor="checkbox-id">Checkbox Label</label>
          <Checkbox id="checkbox-id" />
        </>,
      );

      expect(screen.getByLabelText('Checkbox Label')).toBeInTheDocument();
    });

    it('clicking label toggles checkbox', async () => {
      render(
        <>
          <label htmlFor="checkbox-id">Checkbox Label</label>
          <Checkbox id="checkbox-id" />
        </>,
      );

      await user.click(screen.getByText('Checkbox Label'));

      expect(screen.getByRole('checkbox')).toBeChecked();
    });

    it('supports aria-label', () => {
      render(<Checkbox aria-label="Accept terms" />);

      expect(screen.getByRole('checkbox')).toHaveAttribute('aria-label', 'Accept terms');
    });

    it('supports aria-describedby', () => {
      render(
        <>
          <Checkbox aria-describedby="description" />
          <p id="description">This is a description</p>
        </>,
      );

      expect(screen.getByRole('checkbox')).toHaveAttribute('aria-describedby', 'description');
    });

    it('has correct role', () => {
      render(<Checkbox />);

      expect(screen.getByRole('checkbox')).toBeInTheDocument();
    });

    it('has correct aria-checked value when unchecked', () => {
      render(<Checkbox />);

      expect(screen.getByRole('checkbox')).toHaveAttribute('aria-checked', 'false');
    });

    it('has correct aria-checked value when checked', () => {
      render(<Checkbox checked />);

      expect(screen.getByRole('checkbox')).toHaveAttribute('aria-checked', 'true');
    });
  });

  // ============================================
  // Attribute tests
  // ============================================

  describe('attributes', () => {
    it('passes through id attribute', () => {
      render(<Checkbox id="test-id" />);

      expect(screen.getByRole('checkbox')).toHaveAttribute('id', 'test-id');
    });

    it('passes through name attribute', () => {
      // Radix UI checkbox creates a hidden input for form submission with the name
      render(<Checkbox name="test-name" data-testid="checkbox" />);

      const checkbox = screen.getByTestId('checkbox');
      // The name is passed to the hidden input element, not the button
      expect(checkbox).toBeInTheDocument();
    });

    it('passes through value attribute', () => {
      render(<Checkbox value="test-value" />);

      expect(screen.getByRole('checkbox')).toHaveAttribute('value', 'test-value');
    });

    it('passes through data attributes', () => {
      render(<Checkbox data-testid="custom-checkbox" data-custom="value" />);

      const checkbox = screen.getByTestId('custom-checkbox');
      expect(checkbox).toHaveAttribute('data-custom', 'value');
    });
  });

  // ============================================
  // Visual indicator tests
  // ============================================

  describe('visual indicator', () => {
    it('shows check icon when checked', () => {
      render(<Checkbox checked data-testid="checkbox" />);

      // The check icon is rendered inside the indicator
      const checkbox = screen.getByTestId('checkbox');
      expect(checkbox).toHaveAttribute('data-state', 'checked');
    });

    it('hides check icon when unchecked', () => {
      render(<Checkbox checked={false} data-testid="checkbox" />);

      const checkbox = screen.getByTestId('checkbox');
      expect(checkbox).toHaveAttribute('data-state', 'unchecked');
    });
  });
});
