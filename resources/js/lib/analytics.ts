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
 * Track an analytics event via GA4 gtag.
 * Only fires if cookie consent has been granted and gtag is loaded.
 */
export function trackEvent<E extends AnalyticsEventName>(
  eventName: E,
  properties?: EventPropertyMap[E]
): void {
  if (!hasConsent()) {
    return;
  }

  if (!isGtagAvailable()) {
    return;
  }

  window.gtag!('event', eventName, properties ?? {});
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
