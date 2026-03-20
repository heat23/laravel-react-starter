import { useCallback, useEffect } from 'react';

import { usePage } from '@inertiajs/react';

import { setUserId, trackEvent } from '@/lib/analytics';
import type { AnalyticsEventName, EventPropertyMap } from '@/lib/events';
import type { PageProps } from '@/types';

/**
 * React hook for analytics event tracking.
 * Wraps the analytics module for use in components.
 * Also syncs the authenticated user's ID to GA4 for cross-device attribution.
 *
 * usePage() is called unconditionally to preserve hook order. The try-catch
 * handles components rendered outside Inertia context (e.g. unit tests that
 * render components in isolation without a full Inertia app wrapper).
 */
export function useAnalytics() {
  let auth: PageProps['auth'] | undefined;
  try {
    // usePage is called unconditionally on every render — hook order is preserved.
    // The try/catch handles components rendered outside Inertia context (unit tests).
    // eslint-disable-next-line react-hooks/rules-of-hooks
    ({ auth } = usePage<PageProps>().props);
  } catch {
    auth = undefined;
  }

  useEffect(() => {
    setUserId(auth?.user?.id ?? null);
  }, [auth?.user?.id]);

  const track = useCallback(
    <E extends AnalyticsEventName>(
      eventName: E,
      properties?: EventPropertyMap[E]
    ) => {
      trackEvent(eventName, properties);
    },
    []
  );

  return { track };
}
