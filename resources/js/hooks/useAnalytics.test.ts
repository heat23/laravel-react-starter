import { renderHook, act } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { trackEvent } from '@/lib/analytics';
import { AnalyticsEvents } from '@/lib/events';

import { useAnalytics } from './useAnalytics';

vi.mock('@/lib/analytics', () => ({
  trackEvent: vi.fn(),
}));

describe('useAnalytics', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('provides a track function', () => {
    const { result } = renderHook(() => useAnalytics());
    expect(typeof result.current.track).toBe('function');
  });

  it('calls trackEvent with event name and properties', () => {
    const { result } = renderHook(() => useAnalytics());

    act(() => {
      result.current.track(AnalyticsEvents.BILLING_PRICING_VIEWED, {
        plan: 'pro',
      });
    });

    expect(trackEvent).toHaveBeenCalledWith('billing.pricing_viewed', {
      plan: 'pro',
    });
  });

  it('calls trackEvent without properties', () => {
    const { result } = renderHook(() => useAnalytics());

    act(() => {
      result.current.track(AnalyticsEvents.AUTH_LOGIN);
    });

    expect(trackEvent).toHaveBeenCalledWith('auth.login', undefined);
  });
});
