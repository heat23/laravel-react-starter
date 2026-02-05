import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';

import { Skeleton } from './skeleton';

describe('Skeleton', () => {
  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders skeleton component', () => {
      const { container } = render(<Skeleton />);

      expect(container.firstChild).toBeInTheDocument();
    });

    it('renders as div element', () => {
      const { container } = render(<Skeleton />);

      expect(container.firstChild?.nodeName).toBe('DIV');
    });

    it('renders with children', () => {
      render(<Skeleton>Content</Skeleton>);

      expect(screen.getByText('Content')).toBeInTheDocument();
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has base skeleton styling', () => {
      const { container } = render(<Skeleton />);

      const skeleton = container.firstChild as HTMLElement;
      expect(skeleton).toHaveClass('animate-pulse');
      expect(skeleton).toHaveClass('rounded-md');
      expect(skeleton).toHaveClass('bg-muted');
    });

    it('applies custom className', () => {
      const { container } = render(<Skeleton className="h-12 w-12" />);

      const skeleton = container.firstChild as HTMLElement;
      expect(skeleton).toHaveClass('h-12');
      expect(skeleton).toHaveClass('w-12');
    });

    it('merges custom className with defaults', () => {
      const { container } = render(<Skeleton className="custom-class" />);

      const skeleton = container.firstChild as HTMLElement;
      expect(skeleton).toHaveClass('animate-pulse');
      expect(skeleton).toHaveClass('custom-class');
    });
  });

  // ============================================
  // HTML attributes tests
  // ============================================

  describe('HTML attributes', () => {
    it('passes data attributes', () => {
      render(<Skeleton data-testid="test-skeleton" />);

      expect(screen.getByTestId('test-skeleton')).toBeInTheDocument();
    });

    it('passes id attribute', () => {
      const { container } = render(<Skeleton id="my-skeleton" />);

      expect(container.firstChild).toHaveAttribute('id', 'my-skeleton');
    });

    it('passes aria attributes', () => {
      render(<Skeleton aria-label="Loading" />);

      expect(screen.getByLabelText('Loading')).toBeInTheDocument();
    });
  });

  // ============================================
  // Common skeleton patterns tests
  // ============================================

  describe('common patterns', () => {
    it('renders avatar skeleton', () => {
      const { container } = render(<Skeleton className="h-12 w-12 rounded-full" />);

      const skeleton = container.firstChild as HTMLElement;
      expect(skeleton).toHaveClass('h-12');
      expect(skeleton).toHaveClass('w-12');
      expect(skeleton).toHaveClass('rounded-full');
    });

    it('renders text line skeleton', () => {
      const { container } = render(<Skeleton className="h-4 w-full" />);

      const skeleton = container.firstChild as HTMLElement;
      expect(skeleton).toHaveClass('h-4');
      expect(skeleton).toHaveClass('w-full');
    });

    it('renders button skeleton', () => {
      const { container } = render(<Skeleton className="h-10 w-24" />);

      const skeleton = container.firstChild as HTMLElement;
      expect(skeleton).toHaveClass('h-10');
      expect(skeleton).toHaveClass('w-24');
    });

    it('renders card skeleton', () => {
      const { container } = render(<Skeleton className="h-48 w-full" />);

      const skeleton = container.firstChild as HTMLElement;
      expect(skeleton).toHaveClass('h-48');
      expect(skeleton).toHaveClass('w-full');
    });
  });

  // ============================================
  // Skeleton composition tests
  // ============================================

  describe('skeleton composition', () => {
    it('renders multiple skeletons for loading state', () => {
      render(
        <div className="space-y-2">
          <Skeleton className="h-4 w-full" data-testid="skeleton-1" />
          <Skeleton className="h-4 w-3/4" data-testid="skeleton-2" />
          <Skeleton className="h-4 w-1/2" data-testid="skeleton-3" />
        </div>,
      );

      expect(screen.getByTestId('skeleton-1')).toBeInTheDocument();
      expect(screen.getByTestId('skeleton-2')).toBeInTheDocument();
      expect(screen.getByTestId('skeleton-3')).toBeInTheDocument();
    });

    it('renders skeleton with avatar and text', () => {
      render(
        <div className="flex items-center space-x-4">
          <Skeleton className="h-12 w-12 rounded-full" data-testid="avatar-skeleton" />
          <div className="space-y-2">
            <Skeleton className="h-4 w-48" data-testid="title-skeleton" />
            <Skeleton className="h-4 w-36" data-testid="subtitle-skeleton" />
          </div>
        </div>,
      );

      expect(screen.getByTestId('avatar-skeleton')).toBeInTheDocument();
      expect(screen.getByTestId('title-skeleton')).toBeInTheDocument();
      expect(screen.getByTestId('subtitle-skeleton')).toBeInTheDocument();
    });
  });

  // ============================================
  // Animation tests
  // ============================================

  describe('animation', () => {
    it('has pulse animation class', () => {
      const { container } = render(<Skeleton />);

      expect(container.firstChild).toHaveClass('animate-pulse');
    });
  });
});
