import type { AnalyticsEventName, EventPropertyMap } from './events';

declare global {
  interface Window {
    gtag?: (...args: unknown[]) => void;
  }
}

/**
 * Check if the user has accepted cookie consent.
 */
function hasConsent(): boolean {
  try {
    return localStorage.getItem('cookie_consent') === 'accepted';
  } catch {
    return false;
  }
}

/**
 * Check if GA4 gtag is available.
 */
function isGtagAvailable(): boolean {
  return typeof window !== 'undefined' && typeof window.gtag === 'function';
}

/**
 * Pre-consent event queue.
 * Events tracked before consent is granted are stored here (max 20) and
 * flushed when the user accepts cookies. Prevents silent event loss in
 * GDPR-heavy jurisdictions where 20-40% of early-funnel users haven't
 * consented yet.
 */
const eventQueue: Array<{ name: string; params: Record<string, unknown> }> = [];
const MAX_QUEUE_SIZE = 20;

/**
 * Track an analytics event via GA4 gtag.
 * - If consent is granted and gtag is loaded: fires immediately.
 * - If consent is not yet granted: queued (up to MAX_QUEUE_SIZE) for later flush.
 */
export function trackEvent<E extends AnalyticsEventName>(
  eventName: E,
  properties?: EventPropertyMap[E]
): void {
  if (!hasConsent()) {
    if (eventQueue.length < MAX_QUEUE_SIZE) {
      eventQueue.push({
        name: eventName,
        params: (properties ?? {}) as Record<string, unknown>,
      });
    }
    return;
  }

  if (!isGtagAvailable()) {
    return;
  }

  window.gtag!('event', eventName, properties ?? {});
}

/**
 * Grant cookie consent, persist to localStorage, and flush the pre-consent
 * event queue. Call this from the CookieConsent component when the user accepts.
 *
 * Note: if the page reloads after consent (e.g. to load the GA4 script),
 * in-flight queued events are lost — this is the minimum viable fix.
 * Future improvement: persist queue to sessionStorage before reload.
 */
export function grantConsent(): void {
  try {
    localStorage.setItem('cookie_consent', 'accepted');
  } catch {
    // localStorage unavailable — proceed anyway
  }

  // Flush all queued events now that consent is granted
  while (eventQueue.length > 0) {
    const event = eventQueue.shift()!;
    // Re-call trackEvent to re-check gtag availability after consent
    if (isGtagAvailable()) {
      window.gtag!('event', event.name, event.params);
    }
  }
}

/**
 * Set the GA4 user_id for cross-device attribution and user-level LTV analysis.
 * Pass null on logout to clear the identity.
 * Only runs in production when consent is granted and gtag is loaded.
 */
export function setUserId(userId: number | null): void {
  if (!hasConsent() || !isGtagAvailable()) {
    return;
  }

  window.gtag!('set', { user_id: userId ?? undefined });
}
