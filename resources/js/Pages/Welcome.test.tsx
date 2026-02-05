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
    // Reset VITE_APP_NAME mock
    vi.stubEnv('VITE_APP_NAME', 'TestApp');
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders the welcome page', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText(/build your next/i)).toBeInTheDocument();
    });

    it('renders the hero section', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('great application')).toBeInTheDocument();
      expect(
        screen.getByText(/a modern laravel starter template with react, typescript, and tailwind css/i),
      ).toBeInTheDocument();
    });

    it('sets page title to Welcome', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(document.querySelector('title')).toHaveTextContent('Welcome');
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

      expect(screen.queryByRole('link', { name: /log in/i })).not.toBeInTheDocument();
    });

    it('shows register link when canRegister is true', () => {
      render(<Welcome canLogin={false} canRegister={true} />);

      expect(screen.getByRole('link', { name: /get started/i })).toBeInTheDocument();
    });

    it('hides register link when canRegister is false', () => {
      render(<Welcome canLogin={true} canRegister={false} />);

      expect(screen.queryByRole('link', { name: /get started/i })).not.toBeInTheDocument();
    });

    it('shows both login and register links when both are true', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByRole('link', { name: /log in/i })).toBeInTheDocument();
      expect(screen.getByRole('link', { name: /get started/i })).toBeInTheDocument();
    });

    it('hides both login and register links when both are false', () => {
      render(<Welcome canLogin={false} canRegister={false} />);

      expect(screen.queryByRole('link', { name: /log in/i })).not.toBeInTheDocument();
      expect(screen.queryByRole('link', { name: /get started/i })).not.toBeInTheDocument();
    });

    it('renders documentation link', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      const docLink = screen.getByRole('link', { name: /documentation/i });
      expect(docLink).toBeInTheDocument();
      expect(docLink).toHaveAttribute('href', 'https://laravel.com/docs');
      expect(docLink).toHaveAttribute('target', '_blank');
      expect(docLink).toHaveAttribute('rel', 'noopener noreferrer');
    });
  });

  // ============================================
  // Hero section tests
  // ============================================

  describe('hero section', () => {
    it('shows Start Building button when canRegister is true', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByRole('link', { name: /start building/i })).toBeInTheDocument();
    });

    it('hides Start Building button when canRegister is false', () => {
      render(<Welcome canLogin={true} canRegister={false} />);

      expect(screen.queryByRole('link', { name: /start building/i })).not.toBeInTheDocument();
    });

    it('renders hero tagline', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText(/everything you need to ship faster/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Features section tests
  // ============================================

  describe('features section', () => {
    it('renders the features section header', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText(/everything you need to get started/i)).toBeInTheDocument();
    });

    it('renders Secure by Default feature', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('Secure by Default')).toBeInTheDocument();
      expect(screen.getByText(/csrf protection, xss prevention/i)).toBeInTheDocument();
    });

    it('renders Lightning Fast feature', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('Lightning Fast')).toBeInTheDocument();
      expect(screen.getByText(/laravel octane support/i)).toBeInTheDocument();
    });

    it('renders User Management feature', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText('User Management')).toBeInTheDocument();
      expect(screen.getByText(/registration, login, password reset/i)).toBeInTheDocument();
    });

    it('renders all three feature cards', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      const featureCards = [
        'Secure by Default',
        'Lightning Fast',
        'User Management',
      ];

      featureCards.forEach((title) => {
        expect(screen.getByText(title)).toBeInTheDocument();
      });
    });
  });

  // ============================================
  // Tech stack section tests
  // ============================================

  describe('tech stack section', () => {
    it('renders tech stack header', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      expect(screen.getByText(/built with modern technologies/i)).toBeInTheDocument();
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
      expect(screen.getByText(new RegExp(currentYear))).toBeInTheDocument();
    });
  });

  // ============================================
  // Branding tests
  // ============================================

  describe('branding', () => {
    it('renders logo in navigation', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      // Logo should be in the nav area - check for link to home
      const homeLink = screen.getAllByRole('link').find((link) => link.getAttribute('href') === '/');
      expect(homeLink).toBeInTheDocument();
    });
  });

  // ============================================
  // Layout tests
  // ============================================

  describe('layout', () => {
    it('has gradient background', () => {
      const { container } = render(<Welcome canLogin={true} canRegister={true} />);

      expect(container.querySelector('.bg-gradient-to-b')).toBeInTheDocument();
    });

    it('has proper section structure', () => {
      render(<Welcome canLogin={true} canRegister={true} />);

      // Check for navigation
      expect(screen.getByRole('navigation')).toBeInTheDocument();
    });
  });
});
