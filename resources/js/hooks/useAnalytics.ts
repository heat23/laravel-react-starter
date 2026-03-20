import { useCallback, useEffect } from 'react';

import { usePage } from '@inertiajs/react';

import { setUserId, trackEvent } from '@/lib/analytics';
import type { AnalyticsEventName, EventPropertyMap } from '@/lib/events';
import type { PageProps } from '@/types';

/**
 * React hook for analytics event tracking.
 * Wraps the analytics module for use in components.
 * Also syncs the authenticated user's ID to GA4 for cross-device attribution.
 */
export function useAnalytics() {
  const { auth } = usePage<PageProps>().props;

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
