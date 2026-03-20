/**
 * Funnel configuration for GA4 funnel exploration setup.
 *
 * Each funnel has a `maxDays` field that defines the completion window.
 * This value should match the corresponding GA4 Funnel Exploration "Completion
 * window" setting when configuring funnels in GA4. Keeping this in code ensures
 * there is a versioned reference for what GA4 should be configured with.
 *
 * To configure in GA4:
 * 1. Go to Explore → Funnel exploration
 * 2. Set the completion window to match `maxDays` for the relevant funnel
 * 3. Add each step using the event name from `FunnelStep.event`
 */
import { AnalyticsEvents, type AnalyticsEventName } from './events';

export interface FunnelStep {
  name: string;
  event: AnalyticsEventName;
  required: boolean;
}

export interface FunnelConfig {
  id: string;
  name: string;
  steps: FunnelStep[];
  /** Completion window in days for GA4 funnel setup. */
  maxDays: number;
  description: string;
}

export const FUNNELS: Record<string, FunnelConfig> = {
  SIGNUP: {
    id: 'signup',
    name: 'Signup Funnel',
    description: 'Visitor to registered account',
    // 7 days: reasonable window to complete registration + onboarding
    maxDays: 7,
    steps: [
      { name: 'Pricing Viewed', event: AnalyticsEvents.BILLING_PRICING_VIEWED, required: false },
      { name: 'Register', event: AnalyticsEvents.AUTH_REGISTER, required: true },
      { name: 'Email Verified', event: AnalyticsEvents.AUTH_EMAIL_VERIFIED, required: false },
      { name: 'Onboarding Started', event: AnalyticsEvents.ONBOARDING_STARTED, required: false },
      { name: 'Onboarding Completed', event: AnalyticsEvents.ONBOARDING_COMPLETED, required: true },
    ],
  },
  TRIAL_TO_PAID: {
    id: 'trial_to_paid',
    name: 'Trial to Paid Conversion',
    description: 'Registered user to paying subscriber',
    // 30 days: matches trial_days in config/plans.php (default 14 days, extended to 30 for funnel window)
    maxDays: 30,
    steps: [
      { name: 'Onboarding Completed', event: AnalyticsEvents.ONBOARDING_COMPLETED, required: false },
      { name: 'Pricing Viewed', event: AnalyticsEvents.BILLING_PRICING_VIEWED, required: false },
      { name: 'Plan Selected', event: AnalyticsEvents.BILLING_PLAN_SELECTED, required: false },
      { name: 'Checkout Started', event: AnalyticsEvents.BILLING_CHECKOUT_STARTED, required: true },
      { name: 'Checkout Completed', event: AnalyticsEvents.BILLING_CHECKOUT_COMPLETED, required: true },
    ],
  },
  LOGIN: {
    id: 'login',
    name: 'Login Funnel',
    description: 'Login attempt to authenticated session',
    // 1 day: login is a same-session action
    maxDays: 1,
    steps: [
      { name: 'Login', event: AnalyticsEvents.AUTH_LOGIN, required: true },
    ],
  },
};
