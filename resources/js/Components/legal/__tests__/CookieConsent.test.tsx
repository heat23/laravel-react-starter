import { fireEvent, render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import CookieConsent from '../CookieConsent';

// localStorage mock
const localStorageMock = (() => {
  let store: Record<string, string> = {};
  return {
    getItem: (key: string) => store[key] ?? null,
    setItem: (key: string, value: string) => { store[key] = value; },
    removeItem: (key: string) => { delete store[key]; },
    clear: () => { store = {}; },
  };
})();

Object.defineProperty(window, 'localStorage', { value: localStorageMock, writable: true });

describe('CookieConsent', () => {
  beforeEach(() => {
    localStorageMock.clear();
    vi.restoreAllMocks();
    global.fetch = vi.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ success: true }) } as Response)
    );
    Object.defineProperty(window, 'location', {
      writable: true,
      value: { ...window.location, reload: vi.fn() },
    });
  });

  it('renders per-category toggles for necessary, analytics, and marketing', () => {
    render(<CookieConsent />);

    // Open the preferences panel
    fireEvent.click(screen.getByRole('button', { name: /manage preferences/i }));

    // Use aria-label on checkboxes — avoids ambiguity with description text (e.g. "Google Analytics")
    expect(screen.getByRole('checkbox', { name: /strictly necessary cookies/i })).toBeInTheDocument();
    expect(screen.getByRole('checkbox', { name: /analytics cookies/i })).toBeInTheDocument();
    expect(screen.getByRole('checkbox', { name: /marketing cookies/i })).toBeInTheDocument();
  });

  it('necessary cookies cannot be disabled', () => {
    render(<CookieConsent />);
    fireEvent.click(screen.getByRole('button', { name: /manage preferences/i }));

    const necessaryCheckbox = screen.getByLabelText(/strictly necessary/i);
    expect(necessaryCheckbox).toBeDisabled();
    expect(necessaryCheckbox).toBeChecked();
  });

  it('calls consent API when accepting all', () => {
    render(<CookieConsent />);
    fireEvent.click(screen.getByRole('button', { name: /accept all/i }));

    expect(global.fetch).toHaveBeenCalledWith(
      '/api/consent',
      expect.objectContaining({ method: 'POST' })
    );

    const callBody = JSON.parse(
      (global.fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body as string
    );
    expect(callBody.categories).toEqual({ necessary: true, analytics: true, marketing: true });
  });

  it('calls consent API with partial consent when declining analytics', () => {
    render(<CookieConsent />);

    // Open preferences — analytics starts unchecked (false)
    fireEvent.click(screen.getByRole('button', { name: /manage preferences/i }));
    fireEvent.click(screen.getByRole('button', { name: /save preferences/i }));

    expect(global.fetch).toHaveBeenCalledWith(
      '/api/consent',
      expect.objectContaining({ method: 'POST' })
    );

    const callBody = JSON.parse(
      (global.fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body as string
    );
    expect(callBody.categories).toEqual({ necessary: true, analytics: false, marketing: false });
  });

  it('stores consent version in localStorage', () => {
    render(<CookieConsent />);
    fireEvent.click(screen.getByRole('button', { name: /accept all/i }));

    expect(localStorageMock.getItem('cookie_consent_version')).toBe('1.0');
  });
});
