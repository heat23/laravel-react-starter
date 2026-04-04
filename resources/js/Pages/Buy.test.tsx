import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { AnalyticsEvents } from '@/lib/events';

import Buy from './Buy';

// Mock useAnalytics
const mockTrack = vi.fn();
vi.mock('@/hooks/useAnalytics', () => ({
  useAnalytics: () => ({ track: mockTrack }),
}));

// Mock Inertia
vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    Head: ({ title, children }: { title: string; children?: React.ReactNode }) => (
      <><title>{title}</title>{children}</>
    ),
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
  };
});

// Mock marketing components
vi.mock('@/Components/marketing/PublicNav', () => ({
  PublicNav: () => <nav data-testid="public-nav" />,
}));

vi.mock('@/Components/marketing/PublicFooter', () => ({
  PublicFooter: () => <footer data-testid="public-footer" />,
}));

const defaultProps = {
  templatePrice: '$149',
};

describe('Buy Page', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the buy page heading', () => {
      render(<Buy {...defaultProps} />);

      expect(screen.getByRole('heading', { name: /laravel react starter/i })).toBeInTheDocument();
    });

    it('renders the template price', () => {
      render(<Buy {...defaultProps} />);

      expect(screen.getAllByText('$149').length).toBeGreaterThan(0);
    });

    it('renders the get instant access CTA', () => {
      render(<Buy {...defaultProps} />);

      expect(screen.getAllByText(/get instant access/i).length).toBeGreaterThan(0);
    });

    it('renders the what\'s included section', () => {
      render(<Buy {...defaultProps} />);

      expect(screen.getByText(/what's included/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Analytics tests
  // ============================================

  describe('analytics', () => {
    beforeEach(() => {
      mockTrack.mockClear();
    });

    it('tracks page_viewed on mount', () => {
      render(<Buy {...defaultProps} />);

      expect(mockTrack).toHaveBeenCalledWith(
        AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED,
        { page: 'buy' }
      );
    });

    it('tracks page_viewed exactly once', () => {
      render(<Buy {...defaultProps} />);

      const pageViewedCalls = mockTrack.mock.calls.filter(
        ([event]) => event === AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED
      );
      expect(pageViewedCalls).toHaveLength(1);
    });

    it('tracks CTA click on hero get instant access button', () => {
      render(<Buy {...defaultProps} />);

      const ctaLinks = screen.getAllByText(/get instant access/i);
      fireEvent.click(ctaLinks[0]);

      expect(mockTrack).toHaveBeenCalledWith(
        AnalyticsEvents.ENGAGEMENT_CTA_CLICKED,
        expect.objectContaining({ source: 'buy_page', page: 'buy' })
      );
    });

    it('tracks CTA click on bottom get instant access button', () => {
      render(<Buy {...defaultProps} />);

      const ctaLinks = screen.getAllByText(/get instant access/i);
      fireEvent.click(ctaLinks[ctaLinks.length - 1]);

      expect(mockTrack).toHaveBeenCalledWith(
        AnalyticsEvents.ENGAGEMENT_CTA_CLICKED,
        expect.objectContaining({ source: 'buy_page', page: 'buy' })
      );
    });
  });

  // ============================================
  // Navigation guard tests
  // ============================================

  describe('navigation guard', () => {
    it('prevents default navigation on hero CTA click', () => {
      render(<Buy {...defaultProps} />);

      const ctaLinks = screen.getAllByText(/get instant access/i);
      const heroCta = ctaLinks[0].closest('a')!;

      // Dispatch a cancelable click and verify the handler called preventDefault()
      const event = new MouseEvent('click', { bubbles: true, cancelable: true });
      heroCta.dispatchEvent(event);

      expect(event.defaultPrevented).toBe(true);
    });

    it('prevents default navigation on bottom CTA click', () => {
      render(<Buy {...defaultProps} />);

      const ctaLinks = screen.getAllByText(/get instant access/i);
      const bottomCta = ctaLinks[ctaLinks.length - 1].closest('a')!;

      const event = new MouseEvent('click', { bubbles: true, cancelable: true });
      bottomCta.dispatchEvent(event);

      expect(event.defaultPrevented).toBe(true);
    });
  });
});
