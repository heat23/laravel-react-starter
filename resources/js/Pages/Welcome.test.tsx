import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import Welcome from './Welcome';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    Head: ({ title }: { title: string }) => <title>{title}</title>,
    Link: ({
      children,
      href,
    }: {
      children: React.ReactNode;
      href: string;
      method?: string;
      as?: string;
    }) => <a href={href}>{children}</a>,
  };
});

// Mock the useTheme hook to avoid needing ThemeProvider
vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(() => ({
    theme: 'system',
    setTheme: vi.fn(),
    resolvedTheme: 'light',
  })),
}));

describe('Welcome', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.stubEnv('VITE_APP_NAME', 'TestApp');
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the welcome page', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getAllByText(/laravel \+ react saas/i).length).toBeGreaterThan(0);
    });

    it('renders the hero section', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getAllByText(/laravel \+ react saas starter kit/i).length).toBeGreaterThan(0);
      expect(
        screen.getAllByText(/double-charge prevention/i).length
      ).toBeGreaterThan(0);
    });

    it('sets page title with app name and SaaS keyword', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(document.querySelector('title')).toHaveTextContent(
        /Laravel React SaaS Starter Kit/
      );
    });
  });

  // ============================================
  // Navigation tests
  // ============================================

  describe('navigation', () => {
    it('shows login link when canLogin is true', () => {
      render(<Welcome canLogin={true} canRegister={false} />);

      expect(screen.getByRole('link', { name: /log in/i })).toBeInTheDocument();
    });

    it('hides login link when canLogin is false', () => {
      render(<Welcome canLogin={false} canRegister={true} />);

      expect(
        screen.queryByRole('link', { name: /log in/i })
      ).not.toBeInTheDocument();
    });

    it('shows register link when canRegister is true', () => {
      render(<Welcome canLogin={false} canRegister={true} />);

      expect(
        screen.getAllByRole('link', { name: /get started/i }).length
      ).toBeGreaterThan(0);
    });

    it('hides register link when canRegister is false', () => {
      render(<Welcome canLogin={true} canRegister={false} />);

      expect(
        screen.queryByRole('link', { name: /get started/i })
      ).not.toBeInTheDocument();
    });

    it('shows both login and register links when both are true', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByRole('link', { name: /log in/i })).toBeInTheDocument();
      expect(
        screen.getAllByRole('link', { name: /get started/i }).length
      ).toBeGreaterThan(0);
    });

    it('hides both login and register links when both are false', () => {
      render(<Welcome canLogin={false} canRegister={false} />);

      expect(
        screen.queryByRole('link', { name: /log in/i })
      ).not.toBeInTheDocument();
      expect(
        screen.queryByRole('link', { name: /get started/i })
      ).not.toBeInTheDocument();
    });

    it('renders compare hub link', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      const compareLink = screen.getByRole('link', { name: /compare all/i });
      expect(compareLink).toBeInTheDocument();
      expect(compareLink).toHaveAttribute('href', '/compare');
    });
  });

  // ============================================
  // Hero section tests
  // ============================================

  describe('hero section', () => {
    it('shows Start Building Free button when canRegister is true', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(
        screen.getAllByRole('link', { name: /start building free/i }).length
      ).toBeGreaterThan(0);
    });

    it('hides Start Building Free button when canRegister is false', () => {
      render(<Welcome canLogin={true} canRegister={false} />);

      expect(
        screen.queryByRole('link', { name: /start building free/i })
      ).not.toBeInTheDocument();
    });

    it('renders hero badge with feature and test counts', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(
        screen.getByText(/11 features, 90\+ tests, ready to ship/i)
      ).toBeInTheDocument();
    });
  });

  // ============================================
  // Features section tests
  // ============================================

  describe('features section', () => {
    it('renders the features section header', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(
        screen.getAllByText(/everything you need to launch/i).length
      ).toBeGreaterThan(0);
    });

    it('renders Secure by default feature', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('Secure by default')).toBeInTheDocument();
    });

    it('renders 11 feature flags feature', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('11 feature flags')).toBeInTheDocument();
    });

    it('renders Production-grade billing feature', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('Production-grade billing')).toBeInTheDocument();
    });
  });

  // ============================================
  // Before vs After section tests
  // ============================================

  describe('before vs after section', () => {
    it('renders before vs after section', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getAllByText(/skip months of setup/i).length).toBeGreaterThan(0);
      expect(screen.getAllByText(/without this starter/i).length).toBeGreaterThan(0);
      expect(screen.getAllByText(/with this starter/i).length).toBeGreaterThan(0);
    });
  });

  // ============================================
  // Personas section tests
  // ============================================

  describe('personas section', () => {
    it('renders personas section', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText(/built for builders/i)).toBeInTheDocument();
      expect(screen.getByText('Solo founders')).toBeInTheDocument();
      expect(screen.getByText('Small teams')).toBeInTheDocument();
      expect(screen.getByText('Agencies & freelancers')).toBeInTheDocument();
    });
  });

  // ============================================
  // Tech stack section tests
  // ============================================

  describe('tech stack section', () => {
    it('renders tech stack header', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(
        screen.getByText(/modern stack, ready to customize/i)
      ).toBeInTheDocument();
    });

    it('renders Laravel 12', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('Laravel 12')).toBeInTheDocument();
    });

    it('renders React 18', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('React 18')).toBeInTheDocument();
    });

    it('renders TypeScript', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('TypeScript')).toBeInTheDocument();
    });

    it('renders Tailwind CSS v4', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('Tailwind CSS v4')).toBeInTheDocument();
    });

    it('renders Inertia.js', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('Inertia.js')).toBeInTheDocument();
    });
  });

  // ============================================
  // Footer tests
  // ============================================

  describe('footer', () => {
    it('renders footer with copyright text', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText(/all rights reserved/i)).toBeInTheDocument();
    });

    it('includes current year in copyright', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      const currentYear = new Date().getFullYear().toString();
      expect(screen.getByText(new RegExp(`© ${currentYear}`))).toBeInTheDocument();
    });
  });

  // ============================================
  // Branding tests
  // ============================================

  describe('branding', () => {
    it('renders logo in navigation', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      const homeLink = screen
        .getAllByRole('link')
        .find((link) => link.getAttribute('href') === '/');
      expect(homeLink).toBeInTheDocument();
    });
  });

  // ============================================
  // Layout tests
  // ============================================

  describe('layout', () => {
    it('has gradient background', () => {
      const { container } = render(
        <Welcome canLogin={true} canRegister={true} />
      );

      expect(container.querySelector('.bg-gradient-to-b')).toBeInTheDocument();
    });

    it('has proper section structure', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      // Check for navigation (header and footer)
      const navElements = screen.getAllByRole('navigation');
      expect(navElements.length).toBeGreaterThanOrEqual(1);
    });

    it('renders stat highlight cards', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(
        screen.getAllByText(/11 toggleable feature flags/i).length
      ).toBeGreaterThan(0);
      expect(
        screen.getAllByText(/from clone to first deploy/i).length
      ).toBeGreaterThan(0);
    });
  });
});
