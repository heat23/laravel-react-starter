/**
 * Canonical frontend event names.
 * All analytics tracking must use these constants — never raw strings.
 *
 * Naming convention: category.action (snake_case)
 * See docs/EVENT_TAXONOMY.md for full specification.
 *
 * Feature tracking pattern: all feature actions use FEATURE_USED with a
 * consistent `feature_name` value (e.g. 'api_token_created', 'webhook_created',
 * '2fa_enabled'). Dedicated per-feature events were removed to allow unified
 * feature adoption queries in GA4.
 */
export const AnalyticsEvents = {
  // Auth
  AUTH_LOGIN: 'auth.login',
  AUTH_REGISTER: 'auth.register',
  AUTH_EMAIL_VERIFIED: 'auth.email_verified',
  AUTH_LOGOUT: 'auth.logout',
  AUTH_PASSWORD_RESET: 'auth.password_reset',

  // Onboarding
  ONBOARDING_STARTED: 'onboarding.started',
  ONBOARDING_STEP_COMPLETED: 'onboarding.step_completed',
  ONBOARDING_COMPLETED: 'onboarding.completed',

  // Billing
  BILLING_PRICING_VIEWED: 'billing.pricing_viewed',
  BILLING_PLAN_SELECTED: 'billing.plan_selected',
  BILLING_CHECKOUT_STARTED: 'billing.checkout_started',
  BILLING_CHECKOUT_COMPLETED: 'billing.checkout_completed',
  BILLING_SUBSCRIPTION_CANCELED: 'billing.subscription_canceled',
  BILLING_SUBSCRIPTION_RESUMED: 'billing.subscription_resumed',
  BILLING_PLAN_SWAPPED: 'billing.plan_swapped',

  // Feature usage — all features use FEATURE_USED with feature_name property
  FEATURE_USED: 'feature.used',
  FEATURE_SETTINGS_UPDATED: 'feature.settings_updated',

  // Engagement
  ENGAGEMENT_PAGE_VIEWED: 'engagement.page_viewed',
  ENGAGEMENT_RETURN_VISIT: 'engagement.return_visit',
  CONTACT_FORM_SUBMITTED: 'engagement.contact_form_submitted',

  // Limit thresholds
  LIMIT_THRESHOLD_50: 'limit.threshold_50',
  LIMIT_THRESHOLD_80: 'limit.threshold_80',
  LIMIT_THRESHOLD_100: 'limit.threshold_100',

  // Errors
  ERROR_PAGE_VIEWED: 'error.page_viewed',
} as const;

export type AnalyticsEventName =
  (typeof AnalyticsEvents)[keyof typeof AnalyticsEvents];

/** Valid Stripe billing periods. */
export type BillingPeriod = 'monthly' | 'annual';

/** Valid plan keys — mirrors config/plans.php. */
export type PlanKey = 'free' | 'pro' | 'team' | 'enterprise';

/**
 * Per-event property schemas.
 * TypeScript enforces that each event receives only its declared properties.
 */
export type EventPropertyMap = {
  [AnalyticsEvents.AUTH_LOGIN]: { source?: string } | undefined;
  // Both auth events use `source` for attribution consistency across GA4 queries.
  // NEVER include `email` — sending email addresses to GA4 violates GDPR/CCPA.
  [AnalyticsEvents.AUTH_REGISTER]: { source?: string } | undefined;
  [AnalyticsEvents.AUTH_EMAIL_VERIFIED]: Record<string, never> | undefined;
  [AnalyticsEvents.AUTH_LOGOUT]: Record<string, never> | undefined;
  [AnalyticsEvents.AUTH_PASSWORD_RESET]: { method?: string } | undefined;
  [AnalyticsEvents.ONBOARDING_STARTED]: undefined;
  [AnalyticsEvents.ONBOARDING_STEP_COMPLETED]: { step: string };
  [AnalyticsEvents.ONBOARDING_COMPLETED]: undefined;
  [AnalyticsEvents.BILLING_PRICING_VIEWED]: { user_type?: 'authenticated' | 'anonymous' } | undefined;
  [AnalyticsEvents.BILLING_PLAN_SELECTED]: { plan: PlanKey; billing_period: BillingPeriod };
  [AnalyticsEvents.BILLING_CHECKOUT_STARTED]: { plan: PlanKey; price_id?: string; billing_period: BillingPeriod };
  [AnalyticsEvents.BILLING_CHECKOUT_COMPLETED]: { plan: PlanKey; price_id?: string; billing_period: BillingPeriod };
  [AnalyticsEvents.BILLING_SUBSCRIPTION_CANCELED]: { reason?: string } | undefined;
  [AnalyticsEvents.BILLING_SUBSCRIPTION_RESUMED]: { plan?: PlanKey } | undefined;
  [AnalyticsEvents.BILLING_PLAN_SWAPPED]: { from_plan?: string; to_plan?: string } | undefined;
  [AnalyticsEvents.FEATURE_USED]: { feature_name: string };
  [AnalyticsEvents.FEATURE_SETTINGS_UPDATED]: { setting_key?: string } | undefined;
  [AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED]: { page: string };
  [AnalyticsEvents.ENGAGEMENT_RETURN_VISIT]: { days_since_last_visit?: number } | undefined;
  [AnalyticsEvents.CONTACT_FORM_SUBMITTED]: Record<string, never>;
  [AnalyticsEvents.LIMIT_THRESHOLD_50]: { resource: string; current_value: number };
  [AnalyticsEvents.LIMIT_THRESHOLD_80]: { resource: string; current_value: number };
  [AnalyticsEvents.LIMIT_THRESHOLD_100]: { resource: string; current_value: number };
  [AnalyticsEvents.ERROR_PAGE_VIEWED]: { error_code: number; error_title?: string };
};
