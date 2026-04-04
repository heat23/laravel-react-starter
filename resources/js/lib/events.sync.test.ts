import { describe, it, expect } from 'vitest';

import { AnalyticsEvents } from './events';

/**
 * Cross-system sync guarantees:
 * - TS ↔ PHP parity: enforced by PHP-side AnalyticsEventSyncTest which
 *   parses this file directly (no runtime TS dependency needed).
 * - EventPropertyMap exhaustiveness: enforced at compile time via the
 *   mapped type in events.ts. Adding an event without a property map
 *   entry causes a TypeScript build error.
 * - Runtime regression guard: the snapshot test below catches accidental
 *   deletions of events that would otherwise silently pass type checks.
 */
describe('AnalyticsEvents sync', () => {
  it('all event values follow category.action format', () => {
    for (const [key, value] of Object.entries(AnalyticsEvents)) {
      expect(value).toMatch(
        /^[a-z_0-9]+(\.[a-z_0-9]+)+$/,
        `Event ${key} has invalid format: ${value}`
      );
    }
  });

  it('has no duplicate event values', () => {
    const values = Object.values(AnalyticsEvents);
    const unique = [...new Set(values)];
    expect(values).toHaveLength(unique.length);
  });

  it('event count does not regress below known floor', () => {
    const eventKeys = Object.keys(AnalyticsEvents);
    // Floor value: the number of events at the time this test was written.
    // This catches accidental bulk deletions. Increase when adding events.
    expect(eventKeys.length).toBeGreaterThanOrEqual(52);
  });

  it('sorted event names match snapshot (catches accidental deletions)', () => {
    const sortedKeys = Object.keys(AnalyticsEvents).sort();
    expect(sortedKeys).toEqual([
      'ACCOUNT_DELETED',
      'ACTIVATION_MILESTONE',
      'API_TOKEN_CREATED',
      'API_TOKEN_DELETED',
      'AUTH_2FA_DISABLED',
      'AUTH_2FA_ENABLED',
      'AUTH_2FA_RECOVERY_REGENERATED',
      'AUTH_2FA_VERIFIED',
      'AUTH_EMAIL_VERIFIED',
      'AUTH_LOGIN',
      'AUTH_LOGOUT',
      'AUTH_PASSWORD_CHANGED',
      'AUTH_PASSWORD_RESET',
      'AUTH_REGISTER',
      'AUTH_SOCIAL_DISCONNECTED',
      'AUTH_SOCIAL_LOGIN',
      'AUTH_VERIFY_EMAIL',
      'BILLING_CHECKOUT_COMPLETED',
      'BILLING_CHECKOUT_STARTED',
      'BILLING_PAYMENT_FAILED',
      'BILLING_PAYMENT_METHOD_UPDATED',
      'BILLING_PERIOD_TOGGLED',
      'BILLING_PLAN_SELECTED',
      'BILLING_PLAN_SWAPPED',
      'BILLING_PRICING_VIEWED',
      'BILLING_SUBSCRIPTION_CANCELED',
      'BILLING_SUBSCRIPTION_RESUMED',
      'BILLING_SWAP_CONFIRMED',
      'BILLING_TRIAL_UPGRADE_CLICKED',
      'CONTACT_SUBMITTED',
      'ENGAGEMENT_CONTACT_FORM_SUBMITTED',
      'ENGAGEMENT_CTA_CLICKED',
      'ENGAGEMENT_PAGE_VIEWED',
      'ERROR_PAGE_VIEWED',
      'FEATURE_SETTINGS_UPDATED',
      'FEATURE_USED',
      'FEEDBACK_SUBMITTED',
      'LIMIT_THRESHOLD_100',
      'LIMIT_THRESHOLD_50',
      'LIMIT_THRESHOLD_80',
      'ONBOARDING_COMPLETED',
      'ONBOARDING_STARTED',
      'ONBOARDING_STEP_COMPLETED',
      'PROFILE_UPDATED',
      'SALES_INQUIRY_SUBMITTED',
      'SUBSCRIPTION_CANCELED',
      'SUBSCRIPTION_CREATED',
      'SUBSCRIPTION_QUANTITY_UPDATED',
      'SUBSCRIPTION_RESUMED',
      'SUBSCRIPTION_SWAPPED',
      'TRIAL_CONVERTED',
      'TRIAL_EXPIRED',
      'TRIAL_STARTED',
    ]);
  });
});
