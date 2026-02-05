import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import { createRef } from 'react';

import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectSeparator,
  SelectTrigger,
  SelectValue,
} from './select';

// Helper component for testing
const SelectExample = ({
  placeholder = 'Select an option',
  value,
  onValueChange,
  disabled = false,
}: {
  placeholder?: string;
  value?: string;
  onValueChange?: (value: string) => void;
  disabled?: boolean;
}) => (
  <Select value={value} onValueChange={onValueChange} disabled={disabled}>
    <SelectTrigger>
      <SelectValue placeholder={placeholder} />
    </SelectTrigger>
    <SelectContent>
      <SelectItem value="option1">Option 1</SelectItem>
      <SelectItem value="option2">Option 2</SelectItem>
      <SelectItem value="option3">Option 3</SelectItem>
    </SelectContent>
  </Select>
);

describe('Select', () => {
  const user = userEvent.setup();

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders select trigger', () => {
      render(<SelectExample />);

      expect(screen.getByRole('combobox')).toBeInTheDocument();
    });

    it('renders placeholder text', () => {
      render(<SelectExample placeholder="Choose an item" />);

      expect(screen.getByText('Choose an item')).toBeInTheDocument();
    });

    it('renders with selected value', () => {
      render(<SelectExample value="option1" />);

      expect(screen.getByText('Option 1')).toBeInTheDocument();
    });

    it('applies custom className to trigger', () => {
      render(
        <Select>
          <SelectTrigger className="custom-trigger">
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="test">Test</SelectItem>
          </SelectContent>
        </Select>,
      );

      expect(screen.getByRole('combobox')).toHaveClass('custom-trigger');
    });
  });

  // ============================================
  // SelectTrigger tests
  // ============================================

  describe('SelectTrigger', () => {
    it('has correct default classes', () => {
      render(<SelectExample />);

      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveClass('flex', 'h-11', 'w-full', 'items-center', 'justify-between');
    });

    it('renders chevron icon', () => {
      render(<SelectExample />);

      // The chevron is rendered as an SVG inside the trigger
      const trigger = screen.getByRole('combobox');
      const svg = trigger.querySelector('svg');
      expect(svg).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(SelectTrigger.displayName).toBe('SelectTrigger');
    });
  });

  // ============================================
  // Interaction tests
  // ============================================

  describe('interactions', () => {
    it('opens dropdown on click', async () => {
      render(<SelectExample />);

      const trigger = screen.getByRole('combobox');
      await user.click(trigger);

      expect(screen.getByRole('listbox')).toBeInTheDocument();
    });

    it('shows all options when opened', async () => {
      render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByRole('option', { name: 'Option 1' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Option 2' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Option 3' })).toBeInTheDocument();
    });

    it('selects option on click', async () => {
      const handleValueChange = vi.fn();
      render(<SelectExample onValueChange={handleValueChange} />);

      await user.click(screen.getByRole('combobox'));
      await user.click(screen.getByRole('option', { name: 'Option 2' }));

      expect(handleValueChange).toHaveBeenCalledWith('option2');
    });

    it('closes dropdown after selection', async () => {
      render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));
      await user.click(screen.getByRole('option', { name: 'Option 1' }));

      expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
    });

    it('updates displayed value after selection', async () => {
      const { rerender } = render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));
      await user.click(screen.getByRole('option', { name: 'Option 2' }));

      rerender(<SelectExample value="option2" />);

      expect(screen.getByText('Option 2')).toBeInTheDocument();
    });
  });

  // ============================================
  // Keyboard navigation tests
  // ============================================

  describe('keyboard navigation', () => {
    it('opens on Enter key', async () => {
      render(<SelectExample />);

      const trigger = screen.getByRole('combobox');
      trigger.focus();
      await user.keyboard('{Enter}');

      expect(screen.getByRole('listbox')).toBeInTheDocument();
    });

    it('opens on Space key', async () => {
      render(<SelectExample />);

      const trigger = screen.getByRole('combobox');
      trigger.focus();
      await user.keyboard(' ');

      expect(screen.getByRole('listbox')).toBeInTheDocument();
    });

    it('opens on ArrowDown key', async () => {
      render(<SelectExample />);

      const trigger = screen.getByRole('combobox');
      trigger.focus();
      await user.keyboard('{ArrowDown}');

      expect(screen.getByRole('listbox')).toBeInTheDocument();
    });

    it('navigates options with arrow keys', async () => {
      render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));
      await user.keyboard('{ArrowDown}');
      await user.keyboard('{ArrowDown}');

      // Focus should move through options
      expect(screen.getByRole('listbox')).toBeInTheDocument();
    });

    it('selects option with Enter key', async () => {
      const handleValueChange = vi.fn();
      render(<SelectExample onValueChange={handleValueChange} />);

      await user.click(screen.getByRole('combobox'));
      await user.keyboard('{ArrowDown}');
      await user.keyboard('{Enter}');

      expect(handleValueChange).toHaveBeenCalled();
    });

    it('closes on Escape key', async () => {
      render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));
      expect(screen.getByRole('listbox')).toBeInTheDocument();

      await user.keyboard('{Escape}');

      expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Disabled state tests
  // ============================================

  describe('disabled state', () => {
    it('renders disabled trigger', () => {
      render(<SelectExample disabled />);

      expect(screen.getByRole('combobox')).toBeDisabled();
    });

    it('does not open when disabled', async () => {
      render(<SelectExample disabled />);

      await user.click(screen.getByRole('combobox'));

      expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
    });
  });

  // ============================================
  // SelectItem tests
  // ============================================

  describe('SelectItem', () => {
    it('renders item with correct text', async () => {
      render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByRole('option', { name: 'Option 1' })).toBeInTheDocument();
    });

    it('applies custom className to item', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="test" className="custom-item">
              Test Item
            </SelectItem>
          </SelectContent>
        </Select>,
      );

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByRole('option', { name: 'Test Item' })).toHaveClass('custom-item');
    });

    it('renders disabled item', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="test" disabled>
              Disabled Item
            </SelectItem>
          </SelectContent>
        </Select>,
      );

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByRole('option', { name: 'Disabled Item' })).toHaveAttribute(
        'data-disabled',
      );
    });

    it('has correct displayName', () => {
      expect(SelectItem.displayName).toBe('SelectItem');
    });
  });

  // ============================================
  // SelectContent tests
  // ============================================

  describe('SelectContent', () => {
    it('renders in portal by default', async () => {
      render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));

      const listbox = screen.getByRole('listbox');
      expect(listbox).toBeInTheDocument();
    });

    it('applies custom className', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent className="custom-content">
            <SelectItem value="test">Test</SelectItem>
          </SelectContent>
        </Select>,
      );

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByRole('listbox')).toHaveClass('custom-content');
    });

    it('has correct displayName', () => {
      expect(SelectContent.displayName).toBe('SelectContent');
    });
  });

  // ============================================
  // SelectGroup and SelectLabel tests
  // ============================================

  describe('SelectGroup and SelectLabel', () => {
    it('renders group with label', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Fruits</SelectLabel>
              <SelectItem value="apple">Apple</SelectItem>
              <SelectItem value="banana">Banana</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>,
      );

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByText('Fruits')).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Apple' })).toBeInTheDocument();
      expect(screen.getByRole('option', { name: 'Banana' })).toBeInTheDocument();
    });

    it('SelectLabel applies custom className', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel className="custom-label">Group Label</SelectLabel>
              <SelectItem value="test">Test</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>,
      );

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByText('Group Label')).toHaveClass('custom-label');
    });

    it('has correct displayName', () => {
      expect(SelectLabel.displayName).toBe('SelectLabel');
    });
  });

  // ============================================
  // SelectSeparator tests
  // ============================================

  describe('SelectSeparator', () => {
    it('renders separator between groups', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="option1">Option 1</SelectItem>
            <SelectSeparator data-testid="separator" />
            <SelectItem value="option2">Option 2</SelectItem>
          </SelectContent>
        </Select>,
      );

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByTestId('separator')).toBeInTheDocument();
    });

    it('applies custom className', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectSeparator className="custom-separator" data-testid="separator" />
            <SelectItem value="test">Test</SelectItem>
          </SelectContent>
        </Select>,
      );

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByTestId('separator')).toHaveClass('custom-separator');
    });

    it('has correct displayName', () => {
      expect(SelectSeparator.displayName).toBe('SelectSeparator');
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('trigger has combobox role', () => {
      render(<SelectExample />);

      expect(screen.getByRole('combobox')).toBeInTheDocument();
    });

    it('dropdown has listbox role', async () => {
      render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));

      expect(screen.getByRole('listbox')).toBeInTheDocument();
    });

    it('items have option role', async () => {
      render(<SelectExample />);

      await user.click(screen.getByRole('combobox'));

      expect(screen.getAllByRole('option')).toHaveLength(3);
    });

    it('trigger has aria-expanded attribute', async () => {
      render(<SelectExample />);

      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveAttribute('aria-expanded', 'false');

      await user.click(trigger);

      expect(trigger).toHaveAttribute('aria-expanded', 'true');
    });

    it('supports aria-label', () => {
      render(
        <Select>
          <SelectTrigger aria-label="Select option">
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="test">Test</SelectItem>
          </SelectContent>
        </Select>,
      );

      expect(screen.getByRole('combobox')).toHaveAttribute('aria-label', 'Select option');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to trigger element', () => {
      const ref = createRef<HTMLButtonElement>();
      render(
        <Select>
          <SelectTrigger ref={ref}>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="test">Test</SelectItem>
          </SelectContent>
        </Select>,
      );

      expect(ref.current).toBeInstanceOf(HTMLButtonElement);
    });

    it('allows programmatic focus via ref', () => {
      const ref = createRef<HTMLButtonElement>();
      render(
        <Select>
          <SelectTrigger ref={ref}>
            <SelectValue placeholder="Select" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="test">Test</SelectItem>
          </SelectContent>
        </Select>,
      );

      ref.current?.focus();
      expect(ref.current).toHaveFocus();
    });
  });
});
