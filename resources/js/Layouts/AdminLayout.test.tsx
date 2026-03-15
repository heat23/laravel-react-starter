import { render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';

// Mock Inertia
vi.mock('@inertiajs/react', () => ({
  Link: ({
    children,
    ...props
  }: {
    children: React.ReactNode;
    href: string;
  }) => <a {...props}>{children}</a>,
  usePage: () => ({
    props: {
      features: {
        billing: false,
        notifications: false,
        webhooks: false,
        api_tokens: false,
        two_factor: false,
      },
      auth: { user: { id: 1, name: 'Admin', email: 'admin@test.com' } },
    },
    url: '/admin',
  }),
}));

// Mock sidebar layout to just render children
vi.mock('@/Components/sidebar/sidebar-layout', () => ({
  default: ({ children }: { children: React.ReactNode }) => (
    <div data-testid="sidebar-layout">{children}</div>
  ),
}));

// Must import after mocks
const { default: AdminLayout } = await import('./AdminLayout');

function ThrowingChild() {
  throw new Error('Test explosion');
}

describe('AdminLayout', () => {
  it('renders children normally', () => {
    render(
      <AdminLayout>
        <div data-testid="child">Hello</div>
      </AdminLayout>
    );

    expect(screen.getByTestId('child')).toBeInTheDocument();
  });

  it('renders error boundary fallback when child throws', () => {
    // Suppress React error boundary console.error noise
    const spy = vi.spyOn(console, 'error').mockImplementation(() => {});

    render(
      <AdminLayout>
        <ThrowingChild />
      </AdminLayout>
    );

    expect(screen.getByText('Something went wrong')).toBeInTheDocument();
    expect(screen.getByText('Try Again')).toBeInTheDocument();

    spy.mockRestore();
  });
});
