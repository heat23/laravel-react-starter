import { useCallback } from 'react';

import { trackEvent } from '@/lib/analytics';
import type { AnalyticsEventName, EventPropertyMap } from '@/lib/events';

/**
 * React hook for analytics event tracking.
 * Wraps the analytics module for use in components.
 */
export function useAnalytics() {
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
