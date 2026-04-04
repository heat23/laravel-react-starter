import { render } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { useTheme } from '@/Components/theme/use-theme';

import { Toaster } from './sonner';

// Use vi.hoisted to guarantee SonnerToasterMock is initialised before the
// vi.mock('sonner') factory executes.  vi.mock calls are hoisted above ALL
// other module-level statements by Vitest's transform, so a plain vi.fn()
// declaration would be in the TDZ (undefined) when the factory runs — every
// toHaveBeenCalledWith assertion would trivially pass with zero recorded calls.
const SonnerToasterMock = vi.hoisted(() => vi.fn());

vi.mock('@/Components/theme/use-theme', () => ({
  useTheme: vi.fn(),
}));

vi.mock('sonner', () => ({
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  Toaster: (props: any) => {
    SonnerToasterMock(props);
    return null;
  },
}));

// Capture a typed reference AFTER the vi.mock declaration so the module is
// already set up.  Async dynamic imports inside test bodies are fragile and
// redundant given the hoisting semantics above.
const mockUseTheme = vi.mocked(useTheme);

describe('Toaster (sonner wrapper)', () => {
  // Set a known default before every test so no test depends on factory
  // initialisation order or a previous test's mock state.
  beforeEach(() => {
    mockUseTheme.mockReturnValue({ theme: 'system', setTheme: vi.fn() });
  });

  // Full reset after each test: clears recorded calls AND any mockReturnValue
  // overrides, preventing bleed-through across tests (including StrictMode
  // double-invocations that would otherwise consume mockReturnValueOnce early).
  afterEach(() => {
    mockUseTheme.mockReset();
    SonnerToasterMock.mockReset();
  });

  it('renders without crashing', () => {
    render(<Toaster />);
    expect(SonnerToasterMock).toHaveBeenCalled();
  });

  it('forwards the theme from useTheme to the underlying Toaster', () => {
    render(<Toaster />);
    expect(SonnerToasterMock).toHaveBeenCalledWith(
      expect.objectContaining({ theme: 'system' })
    );
  });

  it('forwards a light theme override to the underlying Toaster', () => {
    mockUseTheme.mockReturnValue({ theme: 'light', setTheme: vi.fn() });
    render(<Toaster />);
    expect(SonnerToasterMock).toHaveBeenCalledWith(
      expect.objectContaining({ theme: 'light' })
    );
  });

  it('forwards a dark theme override to the underlying Toaster', () => {
    mockUseTheme.mockReturnValue({ theme: 'dark', setTheme: vi.fn() });
    render(<Toaster />);
    expect(SonnerToasterMock).toHaveBeenCalledWith(
      expect.objectContaining({ theme: 'dark' })
    );
  });

  it('forwards toastOptions prop to the underlying Toaster', () => {
    const customToastOptions = { classNames: { toast: 'my-custom-toast' } };
    render(<Toaster toastOptions={customToastOptions} />);
    expect(SonnerToasterMock).toHaveBeenCalledWith(
      expect.objectContaining({ toastOptions: customToastOptions })
    );
  });

  it('applies the toaster group className by default', () => {
    render(<Toaster />);
    expect(SonnerToasterMock).toHaveBeenCalledWith(
      expect.objectContaining({ className: 'toaster group' })
    );
  });

  it('forwards additional props to the underlying Toaster', () => {
    render(<Toaster position="top-right" />);
    expect(SonnerToasterMock).toHaveBeenCalledWith(
      expect.objectContaining({ position: 'top-right' })
    );
  });
});
