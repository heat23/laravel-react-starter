import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import { createRef } from 'react';

import { Alert, AlertTitle, AlertDescription } from './alert';

describe('Alert', () => {
  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders alert component', () => {
      render(<Alert>Alert Content</Alert>);

      expect(screen.getByRole('alert')).toBeInTheDocument();
    });

    it('renders children content', () => {
      render(<Alert>Test Alert</Alert>);

      expect(screen.getByText('Test Alert')).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(Alert.displayName).toBe('Alert');
    });
  });

  // ============================================
  // Variant tests
  // ============================================

  describe('variants', () => {
    it('renders default variant', () => {
      const { container } = render(<Alert>Default</Alert>);

      const alert = container.querySelector('[role="alert"]');
      expect(alert).toHaveClass('bg-background');
      expect(alert).toHaveClass('text-foreground');
    });

    it('renders destructive variant', () => {
      const { container } = render(<Alert variant="destructive">Error</Alert>);

      const alert = container.querySelector('[role="alert"]');
      expect(alert).toHaveClass('border-destructive/50');
      expect(alert).toHaveClass('text-destructive');
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has base alert styling', () => {
      const { container } = render(<Alert>Test</Alert>);

      const alert = container.querySelector('[role="alert"]');
      expect(alert).toHaveClass('relative');
      expect(alert).toHaveClass('w-full');
      expect(alert).toHaveClass('rounded-lg');
      expect(alert).toHaveClass('border');
      expect(alert).toHaveClass('p-4');
    });

    it('applies custom className', () => {
      const { container } = render(<Alert className="custom-class">Test</Alert>);

      const alert = container.querySelector('[role="alert"]');
      expect(alert).toHaveClass('custom-class');
    });

    it('merges custom className with variant classes', () => {
      const { container } = render(
        <Alert variant="destructive" className="mb-4">
          Test
        </Alert>,
      );

      const alert = container.querySelector('[role="alert"]');
      expect(alert).toHaveClass('text-destructive');
      expect(alert).toHaveClass('mb-4');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to div element', () => {
      const ref = createRef<HTMLDivElement>();
      render(<Alert ref={ref}>Test</Alert>);

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has role="alert"', () => {
      render(<Alert>Accessible Alert</Alert>);

      expect(screen.getByRole('alert')).toBeInTheDocument();
    });

    it('alert is visible to screen readers', () => {
      render(<Alert>Screen reader content</Alert>);

      expect(screen.getByRole('alert')).toBeVisible();
    });
  });
});

describe('AlertTitle', () => {
  describe('rendering', () => {
    it('renders as h5 element', () => {
      render(<AlertTitle>Title</AlertTitle>);

      expect(screen.getByText('Title').tagName).toBe('H5');
    });

    it('has correct displayName', () => {
      expect(AlertTitle.displayName).toBe('AlertTitle');
    });
  });

  describe('styling', () => {
    it('has title styling', () => {
      render(<AlertTitle>Title</AlertTitle>);

      const title = screen.getByText('Title');
      expect(title).toHaveClass('mb-1');
      expect(title).toHaveClass('font-medium');
    });

    it('applies custom className', () => {
      render(<AlertTitle className="custom">Title</AlertTitle>);

      expect(screen.getByText('Title')).toHaveClass('custom');
    });
  });

  describe('ref forwarding', () => {
    it('forwards ref', () => {
      const ref = createRef<HTMLParagraphElement>();
      render(<AlertTitle ref={ref}>Title</AlertTitle>);

      expect(ref.current).toBeInstanceOf(HTMLHeadingElement);
    });
  });
});

describe('AlertDescription', () => {
  describe('rendering', () => {
    it('renders description', () => {
      render(<AlertDescription>Description</AlertDescription>);

      expect(screen.getByText('Description')).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(AlertDescription.displayName).toBe('AlertDescription');
    });
  });

  describe('styling', () => {
    it('has description styling', () => {
      render(<AlertDescription>Description</AlertDescription>);

      const desc = screen.getByText('Description');
      expect(desc).toHaveClass('text-sm');
    });

    it('applies custom className', () => {
      render(<AlertDescription className="custom">Description</AlertDescription>);

      expect(screen.getByText('Description')).toHaveClass('custom');
    });
  });
});

describe('Alert composition', () => {
  it('renders complete alert with title and description', () => {
    render(
      <Alert>
        <AlertTitle>Error Occurred</AlertTitle>
        <AlertDescription>Please try again later.</AlertDescription>
      </Alert>,
    );

    expect(screen.getByRole('alert')).toBeInTheDocument();
    expect(screen.getByText('Error Occurred')).toBeInTheDocument();
    expect(screen.getByText('Please try again later.')).toBeInTheDocument();
  });

  it('renders alert with only description', () => {
    render(
      <Alert>
        <AlertDescription>Simple message</AlertDescription>
      </Alert>,
    );

    expect(screen.getByText('Simple message')).toBeInTheDocument();
  });

  it('renders destructive alert with full content', () => {
    render(
      <Alert variant="destructive">
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>Something went wrong</AlertDescription>
      </Alert>,
    );

    const alert = screen.getByRole('alert');
    expect(alert).toHaveClass('text-destructive');
    expect(screen.getByText('Error')).toBeInTheDocument();
    expect(screen.getByText('Something went wrong')).toBeInTheDocument();
  });

  it('renders alert with icon', () => {
    render(
      <Alert>
        <svg data-testid="alert-icon" />
        <AlertTitle>With Icon</AlertTitle>
        <AlertDescription>Alert with icon</AlertDescription>
      </Alert>,
    );

    expect(screen.getByTestId('alert-icon')).toBeInTheDocument();
    expect(screen.getByText('With Icon')).toBeInTheDocument();
  });
});
