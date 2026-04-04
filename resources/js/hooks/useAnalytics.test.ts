import { renderHook, act } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { setUserId, trackEvent } from '@/lib/analytics';
import { AnalyticsEvents } from '@/lib/events';

import { useAnalytics } from './useAnalytics';

vi.mock('@/lib/analytics', () => ({
  trackEvent: vi.fn(),
  setUserId: vi.fn(),
}));

vi.mock('@inertiajs/react', () => ({
  usePage: () => ({ props: { auth: { user: { id: 1 } } } }),
}));

describe('useAnalytics', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('provides a track function', () => {
    const { result } = renderHook(() => useAnalytics());
    expect(typeof result.current.track).toBe('function');
  });

  it('calls setUserId with the authenticated user id on mount', () => {
    renderHook(() => useAnalytics());
    expect(setUserId).toHaveBeenCalledWith(1);
  });

  it('calls trackEvent with event name and properties', () => {
    const { result } = renderHook(() => useAnalytics());

    act(() => {
      result.current.track(AnalyticsEvents.BILLING_PLAN_SELECTED, {
        plan: 'pro',
        billing_period: 'monthly',
      });
    });

    expect(trackEvent).toHaveBeenCalledWith('billing.plan_selected', {
      plan: 'pro',
      billing_period: 'monthly',
    });
  });

  it('calls trackEvent without properties', () => {
    const { result } = renderHook(() => useAnalytics());

    act(() => {
      result.current.track(AnalyticsEvents.AUTH_LOGIN);
    });

    expect(trackEvent).toHaveBeenCalledWith('auth.login', undefined);
  });

  it('returns a stable track reference across re-renders', () => {
    // Verifies useCallback([]) keeps the reference identity stable so
    // useEffect(..., [track]) callers fire exactly once on mount.
    const { result, rerender } = renderHook(() => useAnalytics());
    const firstRef = result.current.track;

    rerender();

    expect(result.current.track).toBe(firstRef);
  });
});
