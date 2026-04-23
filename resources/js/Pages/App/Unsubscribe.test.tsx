import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { AnalyticsEvents } from '@/lib/events';

import Unsubscribe from './Unsubscribe';

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
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
  };
});

const defaultProps = {
  email: 'user@example.com',
};

describe('Unsubscribe Page', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the unsubscribed confirmation heading', () => {
      render(<Unsubscribe {...defaultProps} />);

      expect(screen.getByText(/you've been unsubscribed/i)).toBeInTheDocument();
    });

    it('renders the user email', () => {
      render(<Unsubscribe {...defaultProps} />);

      expect(screen.getByText('user@example.com')).toBeInTheDocument();
    });

    it('renders return to home link', () => {
      render(<Unsubscribe {...defaultProps} />);

      expect(screen.getByText(/return to home/i)).toBeInTheDocument();
    });

    it('explains that transactional emails are unaffected', () => {
      render(<Unsubscribe {...defaultProps} />);

      expect(screen.getByText(/transactional emails/i)).toBeInTheDocument();
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
      render(<Unsubscribe {...defaultProps} />);

      expect(mockTrack).toHaveBeenCalledWith(
        AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED,
        { page: 'unsubscribe' }
      );
    });

    it('tracks page_viewed exactly once', () => {
      render(<Unsubscribe {...defaultProps} />);

      const pageViewedCalls = mockTrack.mock.calls.filter(
        ([event]) => event === AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED
      );
      expect(pageViewedCalls).toHaveLength(1);
    });
  });
});
