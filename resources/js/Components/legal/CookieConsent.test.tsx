import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it } from 'vitest';

import CookieConsent from './CookieConsent';

describe('CookieConsent', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  it('renders banner when no consent stored', () => {
    render(<CookieConsent />);

    expect(
      screen.getByRole('dialog', { name: /cookie consent/i })
    ).toBeInTheDocument();
    expect(screen.getByText(/we use cookies/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /accept/i })).toBeInTheDocument();
    expect(
      screen.getByRole('button', { name: /decline/i })
    ).toBeInTheDocument();
  });

  it('hides banner when consent is accepted', () => {
    localStorage.setItem('cookie_consent', 'accepted');
    localStorage.setItem('cookie_consent_version', '1.0');

    render(<CookieConsent />);

    expect(
      screen.queryByRole('dialog', { name: /cookie consent/i })
    ).not.toBeInTheDocument();
  });

  it('hides banner when consent is declined', () => {
    localStorage.setItem('cookie_consent', 'declined');
    localStorage.setItem('cookie_consent_version', '1.0');

    render(<CookieConsent />);

    expect(
      screen.queryByRole('dialog', { name: /cookie consent/i })
    ).not.toBeInTheDocument();
  });

  it('shows banner when stored version differs from current version', () => {
    localStorage.setItem('cookie_consent', 'accepted');
    localStorage.setItem('cookie_consent_version', '0.9');

    render(<CookieConsent />);

    expect(
      screen.getByRole('dialog', { name: /cookie consent/i })
    ).toBeInTheDocument();
  });

  it('shows banner when consent is declined but version is stale', () => {
    localStorage.setItem('cookie_consent', 'declined');
    localStorage.setItem('cookie_consent_version', '0.9');

    render(<CookieConsent />);

    expect(
      screen.getByRole('dialog', { name: /cookie consent/i })
    ).toBeInTheDocument();
  });

  it('hides banner when stored version matches current version', () => {
    localStorage.setItem('cookie_consent', 'accepted');
    localStorage.setItem('cookie_consent_version', '1.0');

    render(<CookieConsent />);

    expect(
      screen.queryByRole('dialog', { name: /cookie consent/i })
    ).not.toBeInTheDocument();
  });

  it('shows banner when consent accepted but version key is missing', () => {
    localStorage.setItem('cookie_consent', 'accepted');
    // No cookie_consent_version key set

    render(<CookieConsent />);

    expect(
      screen.getByRole('dialog', { name: /cookie consent/i })
    ).toBeInTheDocument();
  });

  it('stores accepted consent in localStorage', async () => {
    const user = userEvent.setup();
    render(<CookieConsent />);

    await user.click(screen.getByRole('button', { name: /accept/i }));

    expect(localStorage.getItem('cookie_consent')).toBe('accepted');
  });

  it('stores declined consent in localStorage', async () => {
    const user = userEvent.setup();
    render(<CookieConsent />);

    await user.click(screen.getByRole('button', { name: /decline/i }));

    expect(localStorage.getItem('cookie_consent')).toBe('declined');
  });

  it('hides banner after accepting', async () => {
    const user = userEvent.setup();
    render(<CookieConsent />);

    await user.click(screen.getByRole('button', { name: /accept/i }));

    expect(
      screen.queryByRole('dialog', { name: /cookie consent/i })
    ).not.toBeInTheDocument();
  });

  it('hides banner after declining', async () => {
    const user = userEvent.setup();
    render(<CookieConsent />);

    await user.click(screen.getByRole('button', { name: /decline/i }));

    expect(
      screen.queryByRole('dialog', { name: /cookie consent/i })
    ).not.toBeInTheDocument();
  });

  it('banner stays hidden after accepting when component remounts', async () => {
    const user = userEvent.setup();
    const { unmount } = render(<CookieConsent />);

    await user.click(screen.getByRole('button', { name: /accept/i }));
    unmount();

    render(<CookieConsent />);

    expect(
      screen.queryByRole('dialog', { name: /cookie consent/i })
    ).not.toBeInTheDocument();
  });

  it('banner stays hidden after declining when component remounts', async () => {
    const user = userEvent.setup();
    const { unmount } = render(<CookieConsent />);

    await user.click(screen.getByRole('button', { name: /decline/i }));
    unmount();

    render(<CookieConsent />);

    expect(
      screen.queryByRole('dialog', { name: /cookie consent/i })
    ).not.toBeInTheDocument();
  });
});
