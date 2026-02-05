import '@testing-library/jest-dom';
import { afterAll, afterEach, beforeAll, vi } from 'vitest';
import { cleanup } from '@testing-library/react';
import { setupServer } from 'msw/node';
import { handlers } from './mocks/handlers';

// Setup MSW server
export const server = setupServer(...handlers);

beforeAll(() => server.listen({ onUnhandledRequest: 'warn' }));
afterEach(() => {
  cleanup();
  server.resetHandlers();
});
afterAll(() => server.close());

// Mock Inertia router
vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    router: {
      visit: vi.fn(),
      post: vi.fn(),
      get: vi.fn(),
      put: vi.fn(),
      patch: vi.fn(),
      delete: vi.fn(),
      reload: vi.fn(),
    },
    usePage: vi.fn(() => ({
      props: {
        auth: { user: null },
        errors: {},
        flash: {},
      },
    })),
  };
});

// Mock ziggy-js route helper
const mockRoutes: Record<string, string> = {
  'login': '/login',
  'register': '/register',
  'dashboard': '/dashboard',
  'profile.edit': '/profile',
  'profile.update': '/profile',
  'profile.destroy': '/profile',
  'password.request': '/forgot-password',
  'password.email': '/forgot-password',
  'password.reset': '/reset-password',
  'password.store': '/reset-password',
  'password.update': '/password',
  'password.confirm': '/confirm-password',
  'verification.notice': '/verify-email',
  'verification.send': '/email/verification-notification',
  'social.redirect': '/auth/redirect',
  'social.disconnect': '/auth/disconnect',
  'logout': '/logout',
};

const routeFn = (name: string, params?: Record<string, unknown>): string => {
  const baseRoute = mockRoutes[name] || `/${name.replace(/\./g, '/')}`;

  if (params && name === 'social.redirect' && params.provider) {
    return `/auth/${params.provider}/redirect`;
  }
  if (params && name === 'social.disconnect' && params.provider) {
    return `/auth/${params.provider}/disconnect`;
  }

  return baseRoute;
};

// Make route globally available
(globalThis as unknown as { route: typeof routeFn }).route = routeFn;

// Mock window.location
Object.defineProperty(window, 'location', {
  value: {
    href: 'http://localhost',
    pathname: '/',
    search: '',
    hash: '',
    origin: 'http://localhost',
    assign: vi.fn(),
    replace: vi.fn(),
    reload: vi.fn(),
  },
  writable: true,
});

// Mock matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation((query: string) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
});

// Mock ResizeObserver
globalThis.ResizeObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}));

// Mock IntersectionObserver
globalThis.IntersectionObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}));
