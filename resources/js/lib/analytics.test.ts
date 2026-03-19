import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

import { trackEvent } from '@/lib/analytics';
import { AnalyticsEvents } from '@/lib/events';

describe('trackEvent', () => {
  let originalGtag: typeof window.gtag;

  beforeEach(() => {
    originalGtag = window.gtag;
    localStorage.clear();
  });

  afterEach(() => {
    window.gtag = originalGtag;
  });

  it('fires gtag when consent is accepted and gtag exists', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;
    localStorage.setItem('cookie_consent', 'accepted');

    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });

    expect(mockGtag).toHaveBeenCalledWith('event', 'auth.login', {
      source: 'form',
    });
  });

  it('does not fire when consent is declined', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;
    localStorage.setItem('cookie_consent', 'declined');

    trackEvent(AnalyticsEvents.AUTH_LOGIN);

    expect(mockGtag).not.toHaveBeenCalled();
  });

  it('does not fire when no consent decision exists', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    trackEvent(AnalyticsEvents.AUTH_LOGIN);

    expect(mockGtag).not.toHaveBeenCalled();
  });

  it('does not fire when gtag is not loaded', () => {
    window.gtag = undefined;
    localStorage.setItem('cookie_consent', 'accepted');

    expect(() => trackEvent(AnalyticsEvents.AUTH_LOGIN)).not.toThrow();
  });

  it('passes empty object when no properties provided', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;
    localStorage.setItem('cookie_consent', 'accepted');

    trackEvent(AnalyticsEvents.ENGAGEMENT_DASHBOARD_VIEWED);

    expect(mockGtag).toHaveBeenCalledWith(
      'event',
      'engagement.dashboard_viewed',
      {}
    );
  });

  it('passes typed properties through to gtag', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;
    localStorage.setItem('cookie_consent', 'accepted');

    trackEvent(AnalyticsEvents.BILLING_PLAN_SELECTED, {
      plan: 'pro',
      billing_period: 'monthly',
    });

    expect(mockGtag).toHaveBeenCalledWith('event', 'billing.plan_selected', {
      plan: 'pro',
      billing_period: 'monthly',
    });
  });
});
