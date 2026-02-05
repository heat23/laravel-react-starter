import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import { createRef } from 'react';

import { Badge } from './badge';

describe('Badge', () => {
  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders badge component', () => {
      render(<Badge>Badge</Badge>);

      expect(screen.getByText('Badge')).toBeInTheDocument();
    });

    it('renders children content', () => {
      render(<Badge>Custom Content</Badge>);

      expect(screen.getByText('Custom Content')).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(Badge.displayName).toBe('Badge');
    });

    it('renders as div element', () => {
      const { container } = render(<Badge>Test</Badge>);

      expect(container.firstChild?.nodeName).toBe('DIV');
    });
  });

  // ============================================
  // Variant tests
  // ============================================

  describe('variants', () => {
    it('renders default variant', () => {
      const { container } = render(<Badge>Default</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('bg-primary');
      expect(badge).toHaveClass('text-primary-foreground');
    });

    it('renders secondary variant', () => {
      const { container } = render(<Badge variant="secondary">Secondary</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('bg-secondary');
      expect(badge).toHaveClass('text-secondary-foreground');
    });

    it('renders destructive variant', () => {
      const { container } = render(<Badge variant="destructive">Destructive</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('bg-destructive');
      expect(badge).toHaveClass('text-destructive-foreground');
    });

    it('renders outline variant', () => {
      const { container } = render(<Badge variant="outline">Outline</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('text-foreground');
    });

    it('renders critical severity variant', () => {
      const { container } = render(<Badge variant="critical">Critical</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('severity-critical');
      expect(badge).toHaveClass('font-bold');
      expect(badge).toHaveClass('uppercase');
    });

    it('renders high severity variant', () => {
      const { container } = render(<Badge variant="high">High</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('severity-high');
    });

    it('renders medium severity variant', () => {
      const { container } = render(<Badge variant="medium">Medium</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('severity-medium');
    });

    it('renders low severity variant', () => {
      const { container } = render(<Badge variant="low">Low</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('severity-low');
    });

    it('renders success variant', () => {
      const { container } = render(<Badge variant="success">Success</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('bg-success');
      expect(badge).toHaveClass('text-success-foreground');
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has base badge styling', () => {
      const { container } = render(<Badge>Test</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('inline-flex');
      expect(badge).toHaveClass('items-center');
      expect(badge).toHaveClass('rounded-full');
      expect(badge).toHaveClass('text-xs');
      expect(badge).toHaveClass('font-semibold');
    });

    it('applies custom className', () => {
      const { container } = render(<Badge className="custom-class">Test</Badge>);

      expect(container.firstChild).toHaveClass('custom-class');
    });

    it('merges custom className with variant classes', () => {
      const { container } = render(<Badge variant="secondary" className="ml-2">Test</Badge>);

      const badge = container.firstChild as HTMLElement;
      expect(badge).toHaveClass('bg-secondary');
      expect(badge).toHaveClass('ml-2');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to div element', () => {
      const ref = createRef<HTMLDivElement>();
      render(<Badge ref={ref}>Test</Badge>);

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });

  // ============================================
  // HTML attributes tests
  // ============================================

  describe('HTML attributes', () => {
    it('passes data attributes', () => {
      render(<Badge data-testid="test-badge">Test</Badge>);

      expect(screen.getByTestId('test-badge')).toBeInTheDocument();
    });

    it('passes id attribute', () => {
      const { container } = render(<Badge id="my-badge">Test</Badge>);

      expect(container.firstChild).toHaveAttribute('id', 'my-badge');
    });

    it('passes aria attributes', () => {
      render(<Badge aria-label="Status badge">Test</Badge>);

      expect(screen.getByLabelText('Status badge')).toBeInTheDocument();
    });
  });

  // ============================================
  // Content tests
  // ============================================

  describe('content', () => {
    it('renders text content', () => {
      render(<Badge>Text Badge</Badge>);

      expect(screen.getByText('Text Badge')).toBeInTheDocument();
    });

    it('renders numeric content', () => {
      render(<Badge>42</Badge>);

      expect(screen.getByText('42')).toBeInTheDocument();
    });

    it('renders with icon and text', () => {
      render(
        <Badge>
          <span>Icon</span> Text
        </Badge>,
      );

      expect(screen.getByText('Icon')).toBeInTheDocument();
      expect(screen.getByText(/Text/)).toBeInTheDocument();
    });

    it('renders empty badge', () => {
      const { container } = render(<Badge />);

      expect(container.firstChild).toBeInTheDocument();
    });
  });

  // ============================================
  // Use case tests
  // ============================================

  describe('use cases', () => {
    it('renders status badge', () => {
      render(<Badge variant="success">Active</Badge>);

      expect(screen.getByText('Active')).toBeInTheDocument();
    });

    it('renders count badge', () => {
      render(<Badge variant="secondary">3</Badge>);

      expect(screen.getByText('3')).toBeInTheDocument();
    });

    it('renders severity badge', () => {
      render(<Badge variant="critical">URGENT</Badge>);

      expect(screen.getByText('URGENT')).toBeInTheDocument();
    });

    it('renders notification badge', () => {
      render(<Badge variant="destructive">New</Badge>);

      expect(screen.getByText('New')).toBeInTheDocument();
    });
  });
});
