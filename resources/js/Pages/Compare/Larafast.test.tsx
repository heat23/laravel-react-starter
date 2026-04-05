import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import Larafast from './Larafast';

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

vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(() => ({
    theme: 'system',
    setTheme: vi.fn(),
    resolvedTheme: 'light',
  })),
}));

const defaultProps = {
  competitor: 'larafast',
  competitorName: 'Larafast',
  title: 'Laravel React Starter vs Larafast 2026 — Full Comparison',
  metaDescription: 'Larafast vs Laravel React Starter: honest comparison.',
  features: [
    {
      feature: 'Frontend stack',
      us: 'React 18 + TypeScript',
      them: 'Blade / Livewire (React add-on)',
    },
    {
      feature: 'TypeScript',
      us: 'Full (frontend + admin)',
      them: 'No (PHP/Blade default)',
    },
  ],
  breadcrumbs: [
    { name: 'Home', url: 'https://example.com' },
    { name: 'Compare', url: 'https://example.com/compare' },
    {
      name: 'Larafast vs Laravel React Starter',
      url: 'https://example.com/compare/larafast',
    },
  ],
  canonicalUrl: 'https://example.com/compare/larafast',
};

describe('Compare/Larafast', () => {
  it('renders without crashing', () => {
    render(<Larafast {...defaultProps} />);
    expect(document.body).toBeTruthy();
  });

  it('renders the H1', () => {
    render(<Larafast {...defaultProps} />);
    expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent(
      /Laravel React Starter vs Larafast/
    );
  });

  it('uses absolute canonical URL from prop', () => {
    const { container } = render(<Larafast {...defaultProps} />);
    // canonicalUrl prop is passed as absolute URL from the server (CompareController)
    // and rendered via {canonicalUrl && <link rel="canonical" href={canonicalUrl} />}
    // Verify page renders correctly with the absolute URL prop
    expect(container).toBeTruthy();
    // The canonicalUrl prop value is absolute — confirm no relative fallback is rendered
    const links = container.querySelectorAll('link[rel="canonical"]');
    links.forEach((link) => {
      expect(link.getAttribute('href')).not.toBe('/compare/larafast');
    });
  });

  it('does not render canonical link when canonicalUrl prop is omitted', () => {
    const { canonicalUrl: _, ...propsWithoutCanonical } = defaultProps;
    const { container } = render(<Larafast {...propsWithoutCanonical} />);
    const canonicalLinks = container.querySelectorAll('link[rel="canonical"]');
    expect(canonicalLinks).toHaveLength(0);
  });

  it('renders FAQ questions', () => {
    render(<Larafast {...defaultProps} />);
    expect(screen.getByText(/Is Larafast open source\?/)).toBeInTheDocument();
    expect(
      screen.getByText(/Does Laravel React Starter include Stripe billing/)
    ).toBeInTheDocument();
  });

  it('has CTA button linking to home and pricing', () => {
    render(<Larafast {...defaultProps} />);
    // Primary CTA button text is "Get started" and links to "/"
    const getStartedBtn = screen.getByRole('link', { name: /^get started/i });
    expect(getStartedBtn).toHaveAttribute('href', '/');
    // Secondary CTA links to pricing
    const pricingLink = screen.getByRole('link', { name: /view pricing/i });
    expect(pricingLink).toHaveAttribute('href', '/pricing');
  });
});
