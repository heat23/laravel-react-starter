import { describe, it, expect, vi, beforeEach, afterEach, type MockInstance } from 'vitest';

import { trackEvent, grantConsent, __clearQueueForTesting } from '@/lib/analytics';
import { AnalyticsEvents } from '@/lib/events';

describe('trackEvent', () => {
  let originalGtag: typeof window.gtag;

  beforeEach(() => {
    vi.useFakeTimers();
    originalGtag = window.gtag;
    localStorage.clear();
    __clearQueueForTesting();
  });

  afterEach(() => {
    window.gtag = originalGtag;
    vi.useRealTimers();
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

  it('re-queues event when consent is granted but gtag is not loaded', () => {
    window.gtag = undefined;
    localStorage.setItem('cookie_consent', 'accepted');

    expect(() => trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' })).not.toThrow();

    // Simulate gtag becoming available after the retry timer fires
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    // Advance timers to trigger the retry flush (500ms interval)
    vi.advanceTimersByTime(500);

    expect(mockGtag).toHaveBeenCalledTimes(1);
    expect(mockGtag).toHaveBeenCalledWith('event', 'auth.login', { source: 'form' });
  });

  it('passes empty object when no properties provided', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;
    localStorage.setItem('cookie_consent', 'accepted');

    trackEvent(AnalyticsEvents.ONBOARDING_STARTED);

    expect(mockGtag).toHaveBeenCalledWith(
      'event',
      'onboarding.started',
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

describe('grantConsent — queue flush', () => {
  let originalGtag: typeof window.gtag;

  beforeEach(() => {
    vi.useFakeTimers();
    originalGtag = window.gtag;
    localStorage.clear();
    __clearQueueForTesting();
  });

  afterEach(() => {
    window.gtag = originalGtag;
    vi.useRealTimers();
  });

  it('flushes queued events to gtag when consent is granted', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    // Queue 2 events before consent
    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });
    trackEvent(AnalyticsEvents.ONBOARDING_STARTED);
    expect(mockGtag).not.toHaveBeenCalled();

    // Grant consent — queue should flush
    grantConsent();

    expect(mockGtag).toHaveBeenCalledTimes(2);
    expect(mockGtag).toHaveBeenNthCalledWith(1, 'event', 'auth.login', { source: 'form' });
    expect(mockGtag).toHaveBeenNthCalledWith(2, 'event', 'onboarding.started', {});
  });

  it('fires new events immediately after grantConsent()', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    grantConsent();
    mockGtag.mockClear();

    // After consent, trackEvent must fire immediately without re-queueing
    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'google' });

    expect(mockGtag).toHaveBeenCalledTimes(1);
    expect(mockGtag).toHaveBeenCalledWith('event', 'auth.login', { source: 'google' });
  });

  it('queue does not exceed MAX_QUEUE_SIZE (20)', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    // Attempt to queue 25 events without consent
    for (let i = 0; i < 25; i++) {
      trackEvent(AnalyticsEvents.ONBOARDING_STARTED);
    }

    // Grant consent — only the first 20 should flush (MAX_QUEUE_SIZE = 20)
    grantConsent();

    // Manually computed: min(25, 20) = 20 events flushed
    expect(mockGtag).toHaveBeenCalledTimes(20);
  });

  it('queue fills exactly to MAX_QUEUE_SIZE and stops accepting', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    // Fill queue to capacity (20)
    for (let i = 0; i < 20; i++) {
      trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });
    }
    // 21st event should be silently dropped (capacity reached)
    trackEvent(AnalyticsEvents.BILLING_PLAN_SELECTED, { plan: 'pro', billing_period: 'monthly' });

    grantConsent();

    // Manually computed: exactly 20 auth.login events, billing event was dropped
    expect(mockGtag).toHaveBeenCalledTimes(20);
    const calls = mockGtag.mock.calls;
    expect(calls.every((call) => call[1] === 'auth.login')).toBe(true);
  });

  it('queue is empty after grantConsent() so a second call does not re-fire events', () => {
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    trackEvent(AnalyticsEvents.AUTH_LOGIN);
    grantConsent(); // first flush
    mockGtag.mockClear();

    // Simulating a second grantConsent() call (e.g. user re-accepts) — queue is drained
    grantConsent();

    expect(mockGtag).not.toHaveBeenCalled();
  });

  it('does not throw when gtag is unavailable at flush time', () => {
    window.gtag = undefined;

    trackEvent(AnalyticsEvents.AUTH_LOGIN);

    // Should not throw even without gtag — schedules retry flush
    expect(() => grantConsent()).not.toThrow();
  });

  it('retries queue flush when gtag becomes available after grantConsent', () => {
    window.gtag = undefined;

    // Queue events before consent
    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });
    trackEvent(AnalyticsEvents.ONBOARDING_STARTED);

    // Grant consent without gtag — schedules retry
    grantConsent();

    // Simulate gtag becoming available after 1 retry interval (500ms)
    const mockGtag = vi.fn();
    window.gtag = mockGtag;
    vi.advanceTimersByTime(500);

    // Manually computed: 2 events queued, both should flush on retry
    expect(mockGtag).toHaveBeenCalledTimes(2);
    expect(mockGtag).toHaveBeenNthCalledWith(1, 'event', 'auth.login', { source: 'form' });
    expect(mockGtag).toHaveBeenNthCalledWith(2, 'event', 'onboarding.started', {});
  });

  it('discards queue after MAX_FLUSH_RETRIES (10 retries = 5 seconds) when gtag never loads', () => {
    window.gtag = undefined;

    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });
    grantConsent();

    // Advance past all 10 retries (10 * 500ms = 5000ms)
    vi.advanceTimersByTime(5000);

    // Now assign gtag — queue should already be discarded
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    // Another grantConsent should find empty queue
    grantConsent();
    expect(mockGtag).not.toHaveBeenCalled();
  });

  it('flushes all queued events when gtag is assigned before grantConsent (correct initGA4→grantConsent ordering)', () => {
    // Simulate the documented required order: initGA4() assigns window.gtag synchronously,
    // then grantConsent() flushes the pre-consent queue.
    window.gtag = undefined;

    // Queue 3 events before gtag is available
    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });
    trackEvent(AnalyticsEvents.ONBOARDING_STARTED);
    trackEvent(AnalyticsEvents.BILLING_PLAN_SELECTED, { plan: 'pro', billing_period: 'monthly' });

    // Simulate initGA4() assigning window.gtag synchronously
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    // grantConsent() should flush all 3 queued events in order
    grantConsent();

    // Manually computed: 3 events queued, all should flush — min(3, 20) = 3
    expect(mockGtag).toHaveBeenCalledTimes(3);
    expect(mockGtag).toHaveBeenNthCalledWith(1, 'event', 'auth.login', { source: 'form' });
    expect(mockGtag).toHaveBeenNthCalledWith(2, 'event', 'onboarding.started', {});
    expect(mockGtag).toHaveBeenNthCalledWith(3, 'event', 'billing.plan_selected', {
      plan: 'pro',
      billing_period: 'monthly',
    });
  });

  it('retries then discards queue when grantConsent is called and gtag remains permanently undefined', () => {
    window.gtag = undefined;

    // Queue 3 events before consent with no gtag configured
    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });
    trackEvent(AnalyticsEvents.ONBOARDING_STARTED);
    trackEvent(AnalyticsEvents.BILLING_PLAN_SELECTED, { plan: 'pro', billing_period: 'monthly' });

    // grantConsent with gtag permanently undefined — schedules retry, eventually discards
    grantConsent();

    // Advance past all retries (10 * 500ms = 5000ms) — queue discarded after timeout
    vi.advanceTimersByTime(5000);

    // Even if gtag becomes available after timeout, the discarded queue means no events fire
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    // Manually: 3 events were queued, retried 10 times, then discarded — 0 events on second call
    grantConsent();

    expect(mockGtag).not.toHaveBeenCalled();
  });

  it('does not grant a second retry window after exhaustion — total retry budget is bounded', () => {
    window.gtag = undefined;

    // Queue an event and grant consent — starts retry flush
    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });
    grantConsent();

    // Exhaust all 10 retries (10 * 500ms = 5000ms) — queue discarded
    vi.advanceTimersByTime(5000);

    // Queue a NEW event after exhaustion (consent already granted)
    localStorage.setItem('cookie_consent', 'accepted');
    trackEvent(AnalyticsEvents.ONBOARDING_STARTED);

    // scheduleQueueFlush() starts a new timer (flushRetryTimer was cleared
    // on exhaustion), but flushRetryCount is still 10 from prior exhaustion.
    // On the very first tick (500ms), count becomes 11 >= MAX_FLUSH_RETRIES,
    // so the queue is immediately discarded — no fresh 10-retry window.
    // gtag stays undefined during the tick to trigger the exhaustion path.
    vi.advanceTimersByTime(500);

    // Now make gtag available and verify the event was already discarded
    // by budget enforcement — NOT by grantConsent() resetting state.
    const mockGtag = vi.fn();
    window.gtag = mockGtag;

    // Manually computed: 0 events — ONBOARDING_STARTED was discarded by
    // the exhaustion path on the first tick. No grantConsent() call here
    // to avoid masking the invariant with a counter-reset path.
    expect(mockGtag).not.toHaveBeenCalled();

    // Extra 5s confirms no deferred flush — timer was cleared on exhaustion
    vi.advanceTimersByTime(5000);
    expect(mockGtag).not.toHaveBeenCalled();
  });

  it('flushes retry-queued events when gtag becomes available within retry window', () => {
    window.gtag = undefined;

    // Queue an event before consent
    trackEvent(AnalyticsEvents.AUTH_LOGIN, { source: 'form' });
    grantConsent();

    // Advance 3 intervals without gtag (1500ms)
    vi.advanceTimersByTime(1500);

    // Now assign gtag — next retry should flush
    const mockGtag = vi.fn();
    window.gtag = mockGtag;
    vi.advanceTimersByTime(500);

    // Manually computed: 1 event queued, flushed on 4th retry (2000ms total)
    expect(mockGtag).toHaveBeenCalledTimes(1);
    expect(mockGtag).toHaveBeenCalledWith('event', 'auth.login', { source: 'form' });
  });
});
