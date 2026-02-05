import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';

import { createRef } from 'react';

import { Button } from './button';

describe('Button Component', () => {
  const user = userEvent.setup();

  // ============================================
  // Basic rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders children correctly', () => {
      render(<Button>Click me</Button>);
      expect(screen.getByRole('button', { name: /click me/i })).toBeInTheDocument();
    });

    it('renders as a button element by default', () => {
      render(<Button>Test</Button>);
      const button = screen.getByRole('button');
      expect(button.tagName).toBe('BUTTON');
    });

    it('renders with custom className', () => {
      render(<Button className="custom-class">Test</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('custom-class');
    });

    it('preserves default classes when custom className added', () => {
      render(<Button className="custom-class">Test</Button>);
      const button = screen.getByRole('button');
      // Should have both custom and default classes
      expect(button).toHaveClass('custom-class');
      expect(button).toHaveClass('inline-flex');
    });
  });

  // ============================================
  // Variant tests
  // ============================================

  describe('variants', () => {
    it('renders default variant', () => {
      render(<Button variant="default">Default</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('bg-primary');
    });

    it('renders destructive variant', () => {
      render(<Button variant="destructive">Delete</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('bg-destructive');
    });

    it('renders outline variant', () => {
      render(<Button variant="outline">Outline</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('border');
      expect(button).toHaveClass('bg-background');
    });

    it('renders secondary variant', () => {
      render(<Button variant="secondary">Secondary</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('bg-secondary');
    });

    it('renders ghost variant', () => {
      render(<Button variant="ghost">Ghost</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('hover:bg-accent');
    });

    it('renders link variant', () => {
      render(<Button variant="link">Link</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('text-primary');
      expect(button).toHaveClass('underline-offset-4');
    });

    it('uses default variant when not specified', () => {
      render(<Button>Default</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('bg-primary');
    });
  });

  // ============================================
  // Size tests
  // ============================================

  describe('sizes', () => {
    it('renders default size', () => {
      render(<Button size="default">Default Size</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('h-10');
      expect(button).toHaveClass('px-4');
    });

    it('renders small size', () => {
      render(<Button size="sm">Small</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('h-9');
      expect(button).toHaveClass('px-3');
    });

    it('renders large size', () => {
      render(<Button size="lg">Large</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('h-11');
      expect(button).toHaveClass('px-8');
    });

    it('renders icon size', () => {
      render(<Button size="icon">Icon</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('h-10');
      expect(button).toHaveClass('w-10');
    });

    it('uses default size when not specified', () => {
      render(<Button>Default</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('h-10');
    });
  });

  // ============================================
  // Disabled state tests
  // ============================================

  describe('disabled state', () => {
    it('renders disabled button', () => {
      render(<Button disabled>Disabled</Button>);
      const button = screen.getByRole('button');
      expect(button).toBeDisabled();
    });

    it('applies disabled styles', () => {
      render(<Button disabled>Disabled</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveClass('disabled:pointer-events-none');
      expect(button).toHaveClass('disabled:opacity-50');
    });

    it('does not trigger click when disabled', async () => {
      const onClick = vi.fn();
      render(<Button disabled onClick={onClick}>Disabled</Button>);

      const button = screen.getByRole('button');
      await user.click(button);

      expect(onClick).not.toHaveBeenCalled();
    });
  });

  // ============================================
  // Click handling tests
  // ============================================

  describe('click handling', () => {
    it('calls onClick handler when clicked', async () => {
      const onClick = vi.fn();
      render(<Button onClick={onClick}>Click me</Button>);

      const button = screen.getByRole('button');
      await user.click(button);

      expect(onClick).toHaveBeenCalledTimes(1);
    });

    it('passes event to onClick handler', async () => {
      const onClick = vi.fn();
      render(<Button onClick={onClick}>Click me</Button>);

      const button = screen.getByRole('button');
      await user.click(button);

      expect(onClick).toHaveBeenCalledWith(
        expect.objectContaining({
          type: 'click',
        })
      );
    });
  });

  // ============================================
  // asChild prop tests
  // ============================================

  describe('asChild prop', () => {
    it('renders as Slot when asChild is true', () => {
      render(
        <Button asChild>
          <a href="/test">Link Button</a>
        </Button>
      );

      // Should render as anchor, not button
      const link = screen.getByRole('link', { name: /link button/i });
      expect(link).toBeInTheDocument();
      expect(link).toHaveAttribute('href', '/test');
    });

    it('passes classes to child when asChild is true', () => {
      render(
        <Button asChild variant="destructive">
          <a href="/test">Link Button</a>
        </Button>
      );

      const link = screen.getByRole('link');
      expect(link).toHaveClass('bg-destructive');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to button element', () => {
      const ref = createRef<HTMLButtonElement>();
      render(<Button ref={ref}>Test</Button>);

      expect(ref.current).toBeInstanceOf(HTMLButtonElement);
    });

    it('forwards ref to child when asChild is true', () => {
      const ref = createRef<HTMLAnchorElement>();
      render(
        <Button asChild ref={ref as React.Ref<HTMLButtonElement>}>
          <a href="/test">Link</a>
        </Button>
      );

      expect(ref.current).toBeInstanceOf(HTMLAnchorElement);
    });
  });

  // ============================================
  // HTML attribute tests
  // ============================================

  describe('HTML attributes', () => {
    it('passes type attribute', () => {
      render(<Button type="submit">Submit</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveAttribute('type', 'submit');
    });

    it('passes aria attributes', () => {
      render(<Button aria-label="Close dialog">X</Button>);
      const button = screen.getByRole('button', { name: /close dialog/i });
      expect(button).toBeInTheDocument();
    });

    it('passes data attributes', () => {
      render(<Button data-testid="custom-button">Test</Button>);
      expect(screen.getByTestId('custom-button')).toBeInTheDocument();
    });

    it('passes id attribute', () => {
      render(<Button id="my-button">Test</Button>);
      const button = screen.getByRole('button');
      expect(button).toHaveAttribute('id', 'my-button');
    });
  });

  // ============================================
  // Combination tests
  // ============================================

  describe('variant and size combinations', () => {
    it('combines destructive variant with large size', () => {
      render(
        <Button variant="destructive" size="lg">
          Delete All
        </Button>
      );
      const button = screen.getByRole('button');
      expect(button).toHaveClass('bg-destructive');
      expect(button).toHaveClass('h-11');
    });

    it('combines outline variant with small size', () => {
      render(
        <Button variant="outline" size="sm">
          Cancel
        </Button>
      );
      const button = screen.getByRole('button');
      expect(button).toHaveClass('border');
      expect(button).toHaveClass('h-9');
    });

    it('combines ghost variant with icon size', () => {
      render(
        <Button variant="ghost" size="icon">
          X
        </Button>
      );
      const button = screen.getByRole('button');
      expect(button).toHaveClass('hover:bg-accent');
      expect(button).toHaveClass('w-10');
    });
  });

  // ============================================
  // Display name test
  // ============================================

  describe('displayName', () => {
    it('has correct displayName', () => {
      expect(Button.displayName).toBe('Button');
    });
  });
});
