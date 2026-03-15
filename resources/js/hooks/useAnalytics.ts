import { useCallback } from 'react';

import { trackEvent } from '@/lib/analytics';
import type { AnalyticsEventName } from '@/lib/events';

type EventProperties = Record<string, string | number | boolean | undefined>;

/**
 * React hook for analytics event tracking.
 * Wraps the analytics module for use in components.
 */
export function useAnalytics() {
  const track = useCallback(
    (eventName: AnalyticsEventName, properties?: EventProperties) => {
      trackEvent(eventName, properties);
    },
    []
  );

  return { track };
}
