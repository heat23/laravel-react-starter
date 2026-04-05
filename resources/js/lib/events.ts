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
  AUTH_LOGOUT: 'auth.logout',
  AUTH_REGISTER: 'auth.register',
  AUTH_VERIFY_EMAIL: 'auth.verify_email',
  AUTH_EMAIL_VERIFIED: 'auth.email_verified',
  AUTH_PASSWORD_CHANGED: 'auth.password_changed',
  AUTH_PASSWORD_RESET: 'auth.password_reset',
  AUTH_2FA_ENABLED: 'auth.2fa_enabled',
  AUTH_2FA_DISABLED: 'auth.2fa_disabled',
  AUTH_2FA_VERIFIED: 'auth.2fa_verified',
  AUTH_2FA_RECOVERY_REGENERATED: 'auth.2fa_recovery_regenerated',
  AUTH_SOCIAL_LOGIN: 'auth.social_login',
  AUTH_SOCIAL_DISCONNECTED: 'auth.social_disconnected',

  // Onboarding
  ONBOARDING_STARTED: 'onboarding.started',
  ONBOARDING_STEP_COMPLETED: 'onboarding.step_completed',
  ONBOARDING_COMPLETED: 'onboarding.completed',

  // Trial
  TRIAL_STARTED: 'trial.started',
  TRIAL_CONVERTED: 'trial.converted',
  TRIAL_EXPIRED: 'trial.expired',

  // Subscription lifecycle (server-side event names for funnel alignment)
  SUBSCRIPTION_CREATED: 'subscription.created',
  SUBSCRIPTION_CANCELED: 'subscription.canceled',
  SUBSCRIPTION_RESUMED: 'subscription.resumed',
  SUBSCRIPTION_SWAPPED: 'subscription.swapped',
  SUBSCRIPTION_QUANTITY_UPDATED: 'subscription.quantity_updated',

  // Billing
  BILLING_PRICING_VIEWED: 'billing.pricing_viewed',
  BILLING_PLAN_SELECTED: 'billing.plan_selected',
  BILLING_CTA_CLICKED: 'billing.cta_clicked',
  BILLING_CHECKOUT_STARTED: 'billing.checkout_started',
  BILLING_CHECKOUT_COMPLETED: 'billing.checkout_completed',
  BILLING_SUBSCRIPTION_CANCELED: 'billing.subscription_canceled',
  BILLING_SUBSCRIPTION_RESUMED: 'billing.subscription_resumed',
  BILLING_PLAN_SWAPPED: 'billing.plan_swapped',
  BILLING_SWAP_CONFIRMED: 'billing.swap_confirmed',
  BILLING_TRIAL_UPGRADE_CLICKED: 'billing.trial_upgrade_clicked',
  BILLING_PAYMENT_FAILED: 'billing.payment_failed',
  BILLING_PERIOD_TOGGLED: 'billing.period_toggled',
  BILLING_PAYMENT_METHOD_UPDATED: 'billing.payment_method_updated',

  // User actions (server-side event names for cross-system correlation)
  PROFILE_UPDATED: 'profile.updated',
  API_TOKEN_CREATED: 'api_token.created',
  API_TOKEN_DELETED: 'api_token.deleted',
  CONTACT_SUBMITTED: 'contact.submitted',
  SALES_INQUIRY_SUBMITTED: 'sales_inquiry.submitted',
  FEEDBACK_SUBMITTED: 'feedback.submitted',

  // Feature usage — all features use FEATURE_USED with feature_name property
  FEATURE_USED: 'feature.used',
  FEATURE_SETTINGS_UPDATED: 'feature.settings_updated',

  // Engagement
  ENGAGEMENT_PAGE_VIEWED: 'engagement.page_viewed',
  ENGAGEMENT_CTA_CLICKED: 'engagement.cta_clicked',
  ENGAGEMENT_CONTACT_FORM_SUBMITTED: 'engagement.contact_form_submitted',

  // Activation
  ACTIVATION_MILESTONE: 'activation.milestone',

  // Account lifecycle
  ACCOUNT_DELETED: 'account.deleted',

  // Errors
  ERROR_PAGE_VIEWED: 'error.page_viewed',

  // Limit thresholds
  LIMIT_THRESHOLD_50: 'limit.threshold_50',
  LIMIT_THRESHOLD_80: 'limit.threshold_80',
  LIMIT_THRESHOLD_100: 'limit.threshold_100',

  // Lifecycle
  LIFECYCLE_EMAIL_SENT: 'lifecycle.email_sent',
} as const;

/**
 * Cross-system sync (TS ↔ PHP) is enforced by the PHP-side
 * AnalyticsEventSyncTest which parses this file directly.
 * Admin-only events (admin.*) are excluded since they are backend-only
 * and never fired from the frontend.
 */
export type AnalyticsEventName =
  (typeof AnalyticsEvents)[keyof typeof AnalyticsEvents];

/**
 * Returns true the first time a feature is used on this browser, false
 * on subsequent calls. Backed by localStorage so it persists across sessions.
 *
 * Resets on browser data clear — acceptable for activation metrics (a
 * returning user on a new browser counts as a first use, minor overcounting).
 *
 * Usage: track(AnalyticsEvents.FEATURE_USED, { feature_name: 'api_token', is_first_use: isFirstUse('api_token') })
 */
export function isFirstUse(featureName: string): boolean {
  const key = `analytics_first_use_${featureName}`;
  try {
    if (localStorage.getItem(key) !== null) {
      return false;
    }
    localStorage.setItem(key, '1');
    return true;
  } catch {
    // localStorage unavailable — treat as first use to avoid silent undercounting
    return true;
  }
}

/** Valid Stripe billing periods. */
export type BillingPeriod = 'monthly' | 'annual';

/** Valid plan keys — mirrors config/plans.php. */
export type PlanKey = 'free' | 'pro' | 'team' | 'enterprise';

/**
 * Per-event property schemas.
 * TypeScript enforces that each event receives only its declared properties.
 *
 * Internal: _EventPropertyMapEntries defines per-event property types.
 * EventPropertyMap is a mapped type over AnalyticsEventName that enforces
 * exhaustiveness — adding an event to AnalyticsEvents without a matching
 * entry here causes a compile error (the missing key defaults to `never`
 * and downstream `track()` calls using it will fail type-checking).
 */
type _EventPropertyMapEntries = {
  [AnalyticsEvents.AUTH_LOGIN]: { source?: string } | undefined;
  // Both auth events use `source` for attribution consistency across GA4 queries.
  // NEVER include `email` — sending email addresses to GA4 violates GDPR/CCPA.
  [AnalyticsEvents.AUTH_LOGOUT]: Record<string, never> | undefined;
  [AnalyticsEvents.AUTH_REGISTER]: { source?: string } | undefined;
  [AnalyticsEvents.AUTH_VERIFY_EMAIL]: Record<string, never> | undefined;
  [AnalyticsEvents.AUTH_EMAIL_VERIFIED]: Record<string, never> | undefined;
  [AnalyticsEvents.AUTH_PASSWORD_CHANGED]: Record<string, never> | undefined;
  [AnalyticsEvents.AUTH_PASSWORD_RESET]: { method?: string } | undefined;
  [AnalyticsEvents.AUTH_2FA_ENABLED]: undefined;
  [AnalyticsEvents.AUTH_2FA_DISABLED]: Record<string, never> | undefined;
  [AnalyticsEvents.AUTH_2FA_VERIFIED]: Record<string, never> | undefined;
  [AnalyticsEvents.AUTH_2FA_RECOVERY_REGENERATED]:
    | Record<string, never>
    | undefined;
  [AnalyticsEvents.AUTH_SOCIAL_LOGIN]: { provider?: string } | undefined;
  [AnalyticsEvents.AUTH_SOCIAL_DISCONNECTED]: { provider?: string } | undefined;
  [AnalyticsEvents.ONBOARDING_STARTED]: undefined;
  [AnalyticsEvents.ONBOARDING_STEP_COMPLETED]: { step: string };
  [AnalyticsEvents.ONBOARDING_COMPLETED]: undefined;
  [AnalyticsEvents.TRIAL_STARTED]: { tier: string; trial_days: number };
  [AnalyticsEvents.TRIAL_CONVERTED]: { tier: string } | undefined;
  [AnalyticsEvents.TRIAL_EXPIRED]: Record<string, never> | undefined;
  [AnalyticsEvents.SUBSCRIPTION_CREATED]: { plan?: PlanKey } | undefined;
  [AnalyticsEvents.SUBSCRIPTION_CANCELED]:
    | { plan?: PlanKey; reason?: string; grace_period_ends?: string }
    | undefined;
  [AnalyticsEvents.SUBSCRIPTION_RESUMED]: { plan?: PlanKey } | undefined;
  [AnalyticsEvents.SUBSCRIPTION_SWAPPED]:
    | { from_plan?: string; to_plan?: string }
    | undefined;
  [AnalyticsEvents.SUBSCRIPTION_QUANTITY_UPDATED]:
    | { quantity?: number }
    | undefined;
  [AnalyticsEvents.BILLING_PRICING_VIEWED]:
    | { user_type?: 'authenticated' | 'anonymous' }
    | undefined;
  [AnalyticsEvents.BILLING_PLAN_SELECTED]: {
    plan: PlanKey;
    billing_period: BillingPeriod;
  };
  [AnalyticsEvents.BILLING_CTA_CLICKED]:
    | { plan?: PlanKey; billing_period?: BillingPeriod; is_upgrade?: boolean }
    | undefined;
  [AnalyticsEvents.BILLING_CHECKOUT_STARTED]: {
    plan: PlanKey;
    price_id?: string;
    billing_period?: BillingPeriod;
    source?: string;
  };
  [AnalyticsEvents.BILLING_CHECKOUT_COMPLETED]: {
    plan: PlanKey;
    price_id?: string;
    billing_period: BillingPeriod;
  };
  [AnalyticsEvents.BILLING_SUBSCRIPTION_CANCELED]:
    | { reason?: string }
    | undefined;
  [AnalyticsEvents.BILLING_SUBSCRIPTION_RESUMED]:
    | { plan?: PlanKey }
    | undefined;
  [AnalyticsEvents.BILLING_PLAN_SWAPPED]:
    | { from_plan?: string; to_plan?: string }
    | undefined;
  [AnalyticsEvents.BILLING_SWAP_CONFIRMED]:
    | { from_plan?: string; to_plan: string; price_id?: string }
    | undefined;
  [AnalyticsEvents.BILLING_TRIAL_UPGRADE_CLICKED]: { tier: string } | undefined;
  [AnalyticsEvents.BILLING_PAYMENT_FAILED]: { reason?: string } | undefined;
  [AnalyticsEvents.BILLING_PERIOD_TOGGLED]: {
    from: BillingPeriod;
    to: BillingPeriod;
  };
  [AnalyticsEvents.BILLING_PAYMENT_METHOD_UPDATED]:
    | Record<string, never>
    | undefined;
  [AnalyticsEvents.PROFILE_UPDATED]: Record<string, never> | undefined;
  [AnalyticsEvents.API_TOKEN_CREATED]: { token_name?: string } | undefined;
  [AnalyticsEvents.API_TOKEN_DELETED]: { token_name?: string } | undefined;
  [AnalyticsEvents.CONTACT_SUBMITTED]: Record<string, never> | undefined;
  [AnalyticsEvents.FEEDBACK_SUBMITTED]: { type?: string } | undefined;
  [AnalyticsEvents.FEATURE_USED]: {
    feature_name: string;
    is_first_use?: boolean;
  };
  [AnalyticsEvents.FEATURE_SETTINGS_UPDATED]:
    | { setting_key?: string }
    | undefined;
  [AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED]: { page: string; section?: string };
  [AnalyticsEvents.ENGAGEMENT_CTA_CLICKED]: {
    source: string;
    label?: string;
    page?: string;
  };
  [AnalyticsEvents.ENGAGEMENT_CONTACT_FORM_SUBMITTED]: Record<string, never>;
  [AnalyticsEvents.ACTIVATION_MILESTONE]: { trigger: string };
  [AnalyticsEvents.ACCOUNT_DELETED]: { reason?: string } | undefined;
  [AnalyticsEvents.ERROR_PAGE_VIEWED]: {
    error_code: number;
    error_title?: string;
  };
  [AnalyticsEvents.LIMIT_THRESHOLD_50]: {
    resource: string;
    current_value: number;
  };
  [AnalyticsEvents.LIMIT_THRESHOLD_80]: {
    resource: string;
    current_value: number;
  };
  [AnalyticsEvents.LIMIT_THRESHOLD_100]: {
    resource: string;
    current_value: number;
  };
  // Server-side only — no client-side properties required
  [AnalyticsEvents.LIFECYCLE_EMAIL_SENT]: Record<string, never> | undefined;
};

/**
 * Mapped type that requires an entry for every AnalyticsEventName.
 * If an event is added to AnalyticsEvents without a corresponding entry in
 * _EventPropertyMapEntries, this mapped type resolves to `never` for that key,
 * which causes a compile error at any track() call site using that event.
 */
export type EventPropertyMap = {
  [K in AnalyticsEventName]: K extends keyof _EventPropertyMapEntries
    ? _EventPropertyMapEntries[K]
    : never;
};

/**
 * Compile-time exhaustiveness check: verifies every AnalyticsEventName is a
 * key of _EventPropertyMapEntries. If a key is missing, _MissingEvents resolves
 * to the missing event name(s) and _AssertNoMissingEvents resolves to those
 * names instead of `true`, producing a type error at any usage site.
 * Pure type-level — zero runtime JS emitted.
 */
type _MissingEvents = Exclude<
  AnalyticsEventName,
  keyof _EventPropertyMapEntries
>;
type _AssertNoMissingEvents = _MissingEvents extends never
  ? true
  : _MissingEvents;
