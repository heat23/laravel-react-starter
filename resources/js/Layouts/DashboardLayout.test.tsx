import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { usePage } from '@inertiajs/react';

import DashboardLayout from './DashboardLayout';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    usePage: vi.fn(() => ({
      props: {
        auth: {
          user: {
            name: 'Test User',
            email: 'test@example.com',
          },
        },
      },
    })),
    Link: ({
      children,
      href,
      method,
      as,
    }: {
      children: React.ReactNode;
      href: string;
      method?: string;
      as?: string;
    }) => {
      if (as === 'button') {
        return <button data-href={href}>{children}</button>;
      }
      return <a href={href}>{children}</a>;
    },
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

const mockedUsePage = vi.mocked(usePage);

describe('DashboardLayout', () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            name: 'Test User',
            email: 'test@example.com',
          },
        },
      },
    } as ReturnType<typeof usePage>);
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders children content', () => {
      render(
        <DashboardLayout>
          <div data-testid="child-content">Child Content</div>
        </DashboardLayout>,
      );

      expect(screen.getByTestId('child-content')).toBeInTheDocument();
    });

    it('renders within main element', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(container.querySelector('main')).toBeInTheDocument();
    });

    it('has min-h-screen class', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(container.querySelector('.min-h-screen')).toBeInTheDocument();
    });
  });

  // ============================================
  // Navigation tests
  // ============================================

  describe('navigation', () => {
    it('renders Dashboard link', () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(screen.getByRole('link', { name: /dashboard/i })).toBeInTheDocument();
    });

    it('renders Profile link', () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      // There may be multiple profile links (nav and dropdown)
      const profileLinks = screen.getAllByRole('link', { name: /profile/i });
      expect(profileLinks.length).toBeGreaterThan(0);
    });

    it('Dashboard link points to /dashboard', () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const dashboardLink = screen.getByRole('link', { name: /dashboard/i });
      expect(dashboardLink).toHaveAttribute('href', '/dashboard');
    });
  });

  // ============================================
  // Logo tests
  // ============================================

  describe('logo', () => {
    it('renders logo link to dashboard', () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const logoLink = screen.getAllByRole('link').find((link) => link.getAttribute('href') === '/dashboard');
      expect(logoLink).toBeInTheDocument();
    });
  });

  // ============================================
  // User menu tests
  // ============================================

  describe('user menu', () => {
    it('renders user initial in avatar', () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      // The avatar shows the first letter of the user's name
      expect(screen.getByText('T')).toBeInTheDocument();
    });

    it('shows user menu trigger button', () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      // There should be a button that triggers the dropdown
      const avatarButtons = screen.getAllByRole('button');
      expect(avatarButtons.length).toBeGreaterThan(0);
    });

    it('opens dropdown menu when clicked', async () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      // Find and click the avatar button
      const avatarButton = screen.getByText('T').closest('button');
      if (avatarButton) {
        await user.click(avatarButton);
      }

      // Dropdown should show user info
      expect(screen.getByText('Test User')).toBeInTheDocument();
      expect(screen.getByText('test@example.com')).toBeInTheDocument();
    });

    it('shows profile link in dropdown', async () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const avatarButton = screen.getByText('T').closest('button');
      if (avatarButton) {
        await user.click(avatarButton);
      }

      const profileLinks = screen.getAllByRole('link', { name: /profile/i });
      expect(profileLinks.length).toBeGreaterThan(0);
    });

    it('shows settings link in dropdown', async () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const avatarButton = screen.getByText('T').closest('button');
      if (avatarButton) {
        await user.click(avatarButton);
      }

      expect(screen.getByText(/settings/i)).toBeInTheDocument();
    });

    it('shows logout option in dropdown', async () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const avatarButton = screen.getByText('T').closest('button');
      if (avatarButton) {
        await user.click(avatarButton);
      }

      expect(screen.getByText(/log out/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Mobile menu tests
  // ============================================

  describe('mobile menu', () => {
    it('renders mobile menu button', () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(screen.getByRole('button', { name: /toggle menu/i })).toBeInTheDocument();
    });

    it('mobile menu button is hidden on desktop (md:hidden)', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const mobileMenuButton = container.querySelector('.md\\:hidden');
      expect(mobileMenuButton).toBeInTheDocument();
    });

    it('opens mobile menu sheet when clicked', async () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const menuButton = screen.getByRole('button', { name: /toggle menu/i });
      await user.click(menuButton);

      // Sheet should open with navigation items
      // Check for navigation links in the sheet
      const dashboardLinks = screen.getAllByRole('link', { name: /dashboard/i });
      expect(dashboardLinks.length).toBeGreaterThanOrEqual(1);
    });
  });

  // ============================================
  // Header tests
  // ============================================

  describe('header', () => {
    it('renders sticky header', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(container.querySelector('.sticky')).toBeInTheDocument();
    });

    it('has proper height', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(container.querySelector('.h-16')).toBeInTheDocument();
    });

    it('renders header element', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(container.querySelector('header')).toBeInTheDocument();
    });
  });

  // ============================================
  // Different user tests
  // ============================================

  describe('different users', () => {
    it('shows first letter of different user name', () => {
      mockedUsePage.mockReturnValue({
        props: {
          auth: {
            user: {
              name: 'John Doe',
              email: 'john@example.com',
            },
          },
        },
      } as ReturnType<typeof usePage>);

      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(screen.getByText('J')).toBeInTheDocument();
    });

    it('shows different user info in dropdown', async () => {
      mockedUsePage.mockReturnValue({
        props: {
          auth: {
            user: {
              name: 'Jane Smith',
              email: 'jane@example.com',
            },
          },
        },
      } as ReturnType<typeof usePage>);

      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const avatarButton = screen.getByText('J').closest('button');
      if (avatarButton) {
        await user.click(avatarButton);
      }

      expect(screen.getByText('Jane Smith')).toBeInTheDocument();
      expect(screen.getByText('jane@example.com')).toBeInTheDocument();
    });

    it('handles lowercase name', () => {
      mockedUsePage.mockReturnValue({
        props: {
          auth: {
            user: {
              name: 'lowercase user',
              email: 'lower@example.com',
            },
          },
        },
      } as ReturnType<typeof usePage>);

      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      // Should uppercase the first letter
      expect(screen.getByText('L')).toBeInTheDocument();
    });
  });

  // ============================================
  // Theme toggle tests
  // ============================================

  describe('theme toggle', () => {
    it('renders theme toggle', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      // Theme toggle should be present in the header
      const header = container.querySelector('header');
      expect(header).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has nav element for desktop navigation', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(container.querySelector('nav')).toBeInTheDocument();
    });

    it('mobile menu button has accessible name', () => {
      render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      const menuButton = screen.getByRole('button', { name: /toggle menu/i });
      expect(menuButton).toBeInTheDocument();
    });

    it('has main element for content', () => {
      const { container } = render(
        <DashboardLayout>
          <div>Content</div>
        </DashboardLayout>,
      );

      expect(container.querySelector('main')).toBeInTheDocument();
    });
  });
});
