/**
 * Canonical frontend event names.
 * All analytics tracking must use these constants — never raw strings.
 *
 * Naming convention: category.action (snake_case)
 * See docs/EVENT_TAXONOMY.md for full specification.
 */
export const AnalyticsEvents = {
  // Auth
  AUTH_LOGIN: 'auth.login',
  AUTH_REGISTER: 'auth.register',

  // Onboarding
  ONBOARDING_STARTED: 'onboarding.started',
  ONBOARDING_STEP_COMPLETED: 'onboarding.step_completed',
  ONBOARDING_COMPLETED: 'onboarding.completed',

  // Billing
  BILLING_PRICING_VIEWED: 'billing.pricing_viewed',
  BILLING_PLAN_SELECTED: 'billing.plan_selected',
  BILLING_CHECKOUT_STARTED: 'billing.checkout_started',
  BILLING_CHECKOUT_COMPLETED: 'billing.checkout_completed',

  // Feature usage
  FEATURE_USED: 'feature.used',
  FEATURE_API_TOKEN_CREATED: 'feature.api_token_created',
  FEATURE_WEBHOOK_CREATED: 'feature.webhook_created',
  FEATURE_SETTINGS_UPDATED: 'feature.settings_updated',

  // Engagement
  ENGAGEMENT_DASHBOARD_VIEWED: 'engagement.dashboard_viewed',
  ENGAGEMENT_PAGE_VIEWED: 'engagement.page_viewed',

  // Errors
  ERROR_PAGE_VIEWED: 'error.page_viewed',
} as const;

export type AnalyticsEventName =
  (typeof AnalyticsEvents)[keyof typeof AnalyticsEvents];

/**
 * Per-event property schemas.
 * TypeScript enforces that each event receives only its declared properties.
 */
export type EventPropertyMap = {
  [AnalyticsEvents.AUTH_LOGIN]: { source?: string } | undefined;
  [AnalyticsEvents.AUTH_REGISTER]: { signup_source?: string } | undefined;
  [AnalyticsEvents.ONBOARDING_STARTED]: undefined;
  [AnalyticsEvents.ONBOARDING_STEP_COMPLETED]: { step: string };
  [AnalyticsEvents.ONBOARDING_COMPLETED]: undefined;
  [AnalyticsEvents.BILLING_PRICING_VIEWED]: undefined;
  [AnalyticsEvents.BILLING_PLAN_SELECTED]: { plan: string; billing_period: string };
  [AnalyticsEvents.BILLING_CHECKOUT_STARTED]: { plan: string; price_id: string; billing_period: string };
  [AnalyticsEvents.BILLING_CHECKOUT_COMPLETED]: { plan: string; price_id: string; billing_period: string };
  [AnalyticsEvents.FEATURE_USED]: { feature_name: string };
  [AnalyticsEvents.FEATURE_API_TOKEN_CREATED]: { token_name?: string } | undefined;
  [AnalyticsEvents.FEATURE_WEBHOOK_CREATED]: { endpoint_url?: string } | undefined;
  [AnalyticsEvents.FEATURE_SETTINGS_UPDATED]: { setting_key?: string } | undefined;
  [AnalyticsEvents.ENGAGEMENT_DASHBOARD_VIEWED]: undefined;
  [AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED]: { page: string };
  [AnalyticsEvents.ERROR_PAGE_VIEWED]: { error_code: number; error_title?: string };
};
