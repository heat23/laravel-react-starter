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
 * Event queue.
 * Events tracked before consent or before gtag is available are stored here
 * (max 20) and flushed when both conditions are met. Prevents silent event
 * loss in GDPR-heavy jurisdictions where 20-40% of early-funnel users
 * haven't consented yet, and handles the window between consent grant and
 * GA4 script initialization.
 */
const eventQueue: Array<{ name: string; params: Record<string, unknown> }> = [];
const MAX_QUEUE_SIZE = 20;

/**
 * Track an analytics event via GA4 gtag.
 * - If consent is granted and gtag is loaded: fires immediately.
 * - If consent is not yet granted: queued (up to MAX_QUEUE_SIZE) for later flush.
 * - If consent is granted but gtag is not yet available: queued for retry flush.
 */
export function trackEvent<E extends AnalyticsEventName>(
  eventName: E,
  properties?: EventPropertyMap[E]
): void {
  const params = (properties ?? {}) as Record<string, unknown>;

  if (!hasConsent()) {
    if (eventQueue.length < MAX_QUEUE_SIZE) {
      eventQueue.push({ name: eventName, params });
    }
    return;
  }

  if (!isGtagAvailable()) {
    if (eventQueue.length < MAX_QUEUE_SIZE) {
      eventQueue.push({ name: eventName, params });
    }
    scheduleQueueFlush();
    return;
  }

  window.gtag!('event', eventName, params);
}

/** Retry flush state for polling when gtag is not yet available. */
let flushRetryTimer: ReturnType<typeof setInterval> | null = null;
let flushRetryCount = 0;
const MAX_FLUSH_RETRIES = 10;
const FLUSH_RETRY_INTERVAL_MS = 500;

/**
 * Schedule periodic queue flush attempts until gtag becomes available.
 * Gives up after MAX_FLUSH_RETRIES (5 seconds total) and discards the queue.
 */
function scheduleQueueFlush(): void {
  if (flushRetryTimer !== null || eventQueue.length === 0) return;

  // Do NOT reset flushRetryCount here — the total retry budget across all
  // scheduleQueueFlush invocations for the same queue lifecycle must be bounded.
  // Only a successful drain resets the counter (gtag is proven available).
  flushRetryTimer = setInterval(() => {
    flushRetryCount++;
    if (isGtagAvailable()) {
      clearInterval(flushRetryTimer!);
      flushRetryTimer = null;
      flushRetryCount = 0; // reset only on success — next lifecycle gets fresh budget
      drainQueue();
    } else if (flushRetryCount >= MAX_FLUSH_RETRIES) {
      clearInterval(flushRetryTimer!);
      flushRetryTimer = null;
      // Do NOT reset flushRetryCount — exhausted budget stays exhausted so any
      // subsequent scheduleQueueFlush call for new events exits immediately.
      eventQueue.length = 0;
    }
  }, FLUSH_RETRY_INTERVAL_MS);
}

/** Flush all queued events to gtag. Caller must verify isGtagAvailable(). */
function drainQueue(): void {
  while (eventQueue.length > 0) {
    const event = eventQueue.shift()!;
    window.gtag!('event', event.name, event.params);
  }
}

/**
 * Grant cookie consent, persist to localStorage, and flush the event queue.
 * Call this from the CookieConsent component when the user accepts.
 *
 * If gtag is available (normal flow after initGA4), flushes immediately.
 * If gtag is not yet available, schedules retry flushes so queued events
 * are delivered once GA4 finishes loading instead of being silently dropped.
 */
export function grantConsent(): void {
  try {
    localStorage.setItem('cookie_consent', 'accepted');
  } catch {
    // localStorage unavailable — proceed anyway
  }

  if (!isGtagAvailable()) {
    if (eventQueue.length > 0) {
      scheduleQueueFlush();
    }
    return;
  }

  drainQueue();
}

/**
 * Clear the event queue and cancel any pending retry timer.
 * Only for use in tests — do NOT call in production code.
 * No-op outside the test environment.
 * @internal
 */
export function __clearQueueForTesting(): void {
  if (import.meta.env.MODE !== 'test') {
    return;
  }
  eventQueue.length = 0;
  flushRetryCount = 0;
  if (flushRetryTimer !== null) {
    clearInterval(flushRetryTimer);
    flushRetryTimer = null;
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
