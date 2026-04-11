import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

import Larafast from './Larafast';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    Head: ({ title, children }: { title: string; children?: React.ReactNode }) => (
      <>
        <title>{title}</title>
        {children}
      </>
    ),
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
  canonicalUrl: 'http://localhost/compare/larafast',
};

describe('Compare/Larafast', () => {
  beforeEach(() => {
    // The component's isOwnOriginUrl helper uses window.location.hostname for origin-gating.
    // jsdom defaults to about:blank (hostname=""); stub a stable origin so tests are predictable.
    vi.stubGlobal('location', { ...window.location, hostname: 'localhost' });
  });

  afterEach(() => {
    vi.unstubAllGlobals();
  });

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
    const links = container.querySelectorAll('link[rel="canonical"]');
    expect(links).toHaveLength(1);
    expect(links[0].getAttribute('href')).toBe('http://localhost/compare/larafast');
  });

  it('does not render canonical link when canonicalUrl prop is omitted', () => {
    const { canonicalUrl: _, ...propsWithoutCanonical } = defaultProps;
    const { container } = render(<Larafast {...propsWithoutCanonical} />);
    const canonicalLinks = container.querySelectorAll('link[rel="canonical"]');
    expect(canonicalLinks).toHaveLength(0);
  });

  it('does not render canonical link when canonicalUrl is an empty string', () => {
    const { container } = render(<Larafast {...defaultProps} canonicalUrl="" />);
    const canonicalLinks = container.querySelectorAll('link[rel="canonical"]');
    expect(canonicalLinks).toHaveLength(0);
  });

  it('resolves relative canonicalUrl to an absolute URL using window.location.origin', () => {
    // toAbsoluteUrl() converts relative paths to absolute using window.location.origin (jsdom: http://localhost).
    // The resulting absolute URL passes isOwnOriginUrl(), so the canonical link is rendered.
    const { container } = render(<Larafast {...defaultProps} canonicalUrl="/compare/larafast" />);
    const canonicalLinks = container.querySelectorAll('link[rel="canonical"]');
    expect(canonicalLinks).toHaveLength(1);
    expect(canonicalLinks[0].getAttribute('href')).toBe('http://localhost/compare/larafast');
  });

  it('does not render canonical link when canonicalUrl points to an external domain', () => {
    // Security: external-origin URLs must be rejected to prevent SEO hijacking.
    // jsdom sets window.location.hostname to 'localhost'; 'evil.com' must not match.
    const { container } = render(
      <Larafast {...defaultProps} canonicalUrl="https://evil.com/compare/larafast" />,
    );
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
