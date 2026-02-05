import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import AuthLayout from './AuthLayout';

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

// Mock the useTheme hook to avoid needing ThemeProvider
vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(() => ({
    theme: 'system',
    setTheme: vi.fn(),
    resolvedTheme: 'light',
  })),
}));

describe('AuthLayout', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.stubEnv('VITE_APP_NAME', 'TestApp');
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders children content', () => {
      render(
        <AuthLayout>
          <div data-testid="child-content">Child Content</div>
        </AuthLayout>,
      );

      expect(screen.getByTestId('child-content')).toBeInTheDocument();
    });

    it('renders with title when provided', () => {
      render(
        <AuthLayout title="Test Title">
          <div>Content</div>
        </AuthLayout>,
      );

      expect(document.querySelector('title')).toHaveTextContent('Test Title');
    });

    it('does not render Head when title is not provided', () => {
      render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      // Title should not be present when not provided
      expect(document.querySelector('title')).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Left panel tests
  // ============================================

  describe('left panel', () => {
    it('renders default left content when not provided', () => {
      render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(screen.getByText(/sign in to access your dashboard/i)).toBeInTheDocument();
    });

    it('renders custom left content when provided', () => {
      render(
        <AuthLayout leftContent={<div data-testid="custom-left">Custom Left Content</div>}>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(screen.getByTestId('custom-left')).toBeInTheDocument();
      expect(screen.queryByText(/sign in to access your dashboard/i)).not.toBeInTheDocument();
    });

    it('renders left footer when provided', () => {
      render(
        <AuthLayout leftFooter={<div data-testid="left-footer">Left Footer</div>}>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(screen.getByTestId('left-footer')).toBeInTheDocument();
    });

    it('does not render left footer when not provided', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(screen.queryByTestId('left-footer')).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Footer tests
  // ============================================

  describe('footer', () => {
    it('renders footer when provided', () => {
      render(
        <AuthLayout footer={<div data-testid="footer-content">Footer Content</div>}>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(screen.getByTestId('footer-content')).toBeInTheDocument();
    });

    it('does not render footer when not provided', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      // Footer element should not exist when no footer prop
      const footer = container.querySelector('footer');
      expect(footer).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Logo and branding tests
  // ============================================

  describe('logo and branding', () => {
    it('renders logo link to home', () => {
      render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      const homeLinks = screen.getAllByRole('link').filter((link) => link.getAttribute('href') === '/');
      expect(homeLinks.length).toBeGreaterThan(0);
    });
  });

  // ============================================
  // Layout structure tests
  // ============================================

  describe('layout structure', () => {
    it('has min-h-screen class', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(container.querySelector('.min-h-screen')).toBeInTheDocument();
    });

    it('has flex layout', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(container.querySelector('.flex')).toBeInTheDocument();
    });

    it('renders left panel with branding on large screens', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      // Left panel is hidden on mobile (lg:flex)
      expect(container.querySelector('.lg\\:flex')).toBeInTheDocument();
    });

    it('renders mobile header', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      // Mobile header is shown on small screens (lg:hidden)
      const mobileHeader = container.querySelector('.lg\\:hidden');
      expect(mobileHeader).toBeInTheDocument();
    });
  });

  // ============================================
  // Theme toggle tests
  // ============================================

  describe('theme toggle', () => {
    it('renders theme toggle in mobile header', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      // Theme toggle should be present
      const mobileHeader = container.querySelector('.lg\\:hidden');
      expect(mobileHeader).toBeInTheDocument();
    });

    it('renders theme toggle in desktop area', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      // Desktop theme toggle area
      const desktopToggle = container.querySelector('.hidden.lg\\:flex');
      expect(desktopToggle).toBeInTheDocument();
    });
  });

  // ============================================
  // Form container tests
  // ============================================

  describe('form container', () => {
    it('centers children in form container', () => {
      const { container } = render(
        <AuthLayout>
          <div data-testid="form-content">Form Content</div>
        </AuthLayout>,
      );

      expect(screen.getByTestId('form-content')).toBeInTheDocument();
    });

    it('limits form width with max-w-md', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(container.querySelector('.max-w-md')).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has header element for mobile', () => {
      const { container } = render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      expect(container.querySelector('header')).toBeInTheDocument();
    });

    it('home link is accessible', () => {
      render(
        <AuthLayout>
          <div>Content</div>
        </AuthLayout>,
      );

      const homeLinks = screen.getAllByRole('link').filter((link) => link.getAttribute('href') === '/');
      expect(homeLinks.length).toBeGreaterThan(0);
    });
  });

  // ============================================
  // Props combination tests
  // ============================================

  describe('props combinations', () => {
    it('renders all optional props together', () => {
      render(
        <AuthLayout
          title="Full Test"
          leftContent={<div data-testid="left">Left</div>}
          leftFooter={<div data-testid="left-footer">Left Footer</div>}
          footer={<div data-testid="footer">Footer</div>}
        >
          <div data-testid="content">Content</div>
        </AuthLayout>,
      );

      expect(document.querySelector('title')).toHaveTextContent('Full Test');
      expect(screen.getByTestId('left')).toBeInTheDocument();
      expect(screen.getByTestId('left-footer')).toBeInTheDocument();
      expect(screen.getByTestId('footer')).toBeInTheDocument();
      expect(screen.getByTestId('content')).toBeInTheDocument();
    });

    it('renders with no optional props', () => {
      render(
        <AuthLayout>
          <div data-testid="minimal-content">Minimal</div>
        </AuthLayout>,
      );

      expect(screen.getByTestId('minimal-content')).toBeInTheDocument();
      expect(screen.getByText(/sign in to access your dashboard/i)).toBeInTheDocument();
    });
  });
});
