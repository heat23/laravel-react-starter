<?php

namespace App\Enums;

enum AnalyticsEvent: string
{
    // Auth
    case AUTH_LOGIN = 'auth.login';
    case AUTH_LOGOUT = 'auth.logout';
    case AUTH_REGISTER = 'auth.register';
    case AUTH_VERIFY_EMAIL = 'auth.verify_email';
    case AUTH_EMAIL_VERIFIED = 'auth.email_verified';
    case AUTH_PASSWORD_CHANGED = 'auth.password_changed';
    case AUTH_PASSWORD_RESET = 'auth.password_reset';
    case AUTH_2FA_ENABLED = 'auth.2fa_enabled';
    case AUTH_2FA_DISABLED = 'auth.2fa_disabled';
    case AUTH_2FA_VERIFIED = 'auth.2fa_verified';
    case AUTH_2FA_RECOVERY_REGENERATED = 'auth.2fa_recovery_regenerated';
    case AUTH_SOCIAL_LOGIN = 'auth.social_login';
    case AUTH_SOCIAL_DISCONNECTED = 'auth.social_disconnected';

    // Onboarding
    case ONBOARDING_STARTED = 'onboarding.started';
    case ONBOARDING_STEP_COMPLETED = 'onboarding.step_completed';
    case ONBOARDING_COMPLETED = 'onboarding.completed';

    // Trial
    case TRIAL_STARTED = 'trial.started';
    case TRIAL_CONVERTED = 'trial.converted';
    case TRIAL_EXPIRED = 'trial.expired';

    // Billing
    case SUBSCRIPTION_CREATED = 'subscription.created';
    case SUBSCRIPTION_CANCELED = 'subscription.canceled';
    case SUBSCRIPTION_RESUMED = 'subscription.resumed';
    case SUBSCRIPTION_SWAPPED = 'subscription.swapped';
    case SUBSCRIPTION_QUANTITY_UPDATED = 'subscription.quantity_updated';
    case BILLING_PRICING_VIEWED = 'billing.pricing_viewed';
    case BILLING_PLAN_SELECTED = 'billing.plan_selected';
    case BILLING_CHECKOUT_STARTED = 'billing.checkout_started';
    case BILLING_CHECKOUT_COMPLETED = 'billing.checkout_completed';
    case BILLING_SUBSCRIPTION_CANCELED = 'billing.subscription_canceled';
    case BILLING_SUBSCRIPTION_RESUMED = 'billing.subscription_resumed';
    case BILLING_PLAN_SWAPPED = 'billing.plan_swapped';
    case BILLING_SWAP_CONFIRMED = 'billing.swap_confirmed';
    case BILLING_TRIAL_UPGRADE_CLICKED = 'billing.trial_upgrade_clicked';
    case BILLING_PAYMENT_FAILED = 'billing.payment_failed';
    case BILLING_PERIOD_TOGGLED = 'billing.period_toggled';
    case BILLING_PAYMENT_METHOD_UPDATED = 'billing.payment_method_updated';

    // Admin
    case ADMIN_UNAUTHORIZED_ACCESS = 'admin.unauthorized_access_attempt';
    case ADMIN_TOGGLE_ADMIN = 'admin.toggle_admin';
    case ADMIN_USER_DEACTIVATED = 'admin.user_deactivated';
    case ADMIN_USER_RESTORED = 'admin.user_restored';
    case ADMIN_USER_VIEWED = 'admin.user_viewed';
    case ADMIN_IMPERSONATION_STARTED = 'admin.impersonation_started';
    case ADMIN_IMPERSONATION_STOPPED = 'admin.impersonation_stopped';
    case ADMIN_AUDIT_LOGS_EXPORTED = 'admin.audit_logs_exported';
    case ADMIN_SUBSCRIPTIONS_EXPORTED = 'admin.subscriptions_exported';
    case ADMIN_BILLING_SUBSCRIPTIONS_VIEWED = 'admin.billing.subscriptions_viewed';
    case ADMIN_BILLING_SUBSCRIPTION_VIEWED = 'admin.billing.subscription_viewed';
    case ADMIN_USERS_EXPORTED = 'admin.users_exported';
    case ADMIN_PASSWORD_RESET_SENT = 'admin.password_reset_sent';
    case ADMIN_USER_CREATED = 'admin.user.created';
    case ADMIN_USER_UPDATED = 'admin.user.updated';
    case ADMIN_FAILED_JOB_RETRY = 'admin.failed_job.retry';
    case ADMIN_FAILED_JOB_DELETE = 'admin.failed_job.delete';
    case ADMIN_FEATURE_FLAG_GLOBAL_OVERRIDE = 'admin.feature_flag.global_override';
    case ADMIN_FEATURE_FLAG_GLOBAL_OVERRIDE_REMOVED = 'admin.feature_flag.global_override_removed';
    case ADMIN_FEATURE_FLAG_USER_OVERRIDE = 'admin.feature_flag.user_override';
    case ADMIN_FEATURE_FLAG_USER_OVERRIDE_REMOVED = 'admin.feature_flag.user_override_removed';
    case ADMIN_FEATURE_FLAG_ALL_USER_OVERRIDES_REMOVED = 'admin.feature_flag.all_user_overrides_removed';
    case ADMIN_CACHE_FLUSHED = 'admin.cache_flushed';
    case ADMIN_WEBHOOK_ENDPOINT_RESTORED = 'admin.webhook_endpoint.restored';
    case ADMIN_TOKEN_REVOKED = 'admin.token.revoked';
    case ADMIN_SESSION_TERMINATED = 'admin.session.terminated';
    case ADMIN_FAILED_JOB_BULK_RETRY = 'admin.failed_job.bulk_retry';
    case ADMIN_FAILED_JOB_BULK_DELETE = 'admin.failed_job.bulk_delete';
    case ADMIN_ROADMAP_ENTRY_CREATED = 'admin.roadmap_entry.created';
    case ADMIN_ROADMAP_ENTRY_UPDATED = 'admin.roadmap_entry.updated';
    case ADMIN_ROADMAP_ENTRY_DELETED = 'admin.roadmap_entry.deleted';
    case ADMIN_ROADMAP_EXPORTED = 'admin.roadmap.exported';
    case ADMIN_FEEDBACK_UPDATED = 'admin.feedback.updated';
    case ADMIN_FEEDBACK_DELETED = 'admin.feedback.deleted';
    case ADMIN_FEEDBACK_BULK_UPDATED = 'admin.feedback.bulk_updated';
    case ADMIN_FEEDBACK_EXPORTED = 'admin.feedback.exported';
    case ADMIN_TOKENS_EXPORTED = 'admin.tokens.exported';
    case ADMIN_NOTIFICATION_SENT = 'admin.notification.sent';
    case ADMIN_CONTACT_SUBMISSION_UPDATED = 'admin.contact_submission.updated';
    case ADMIN_CONTACT_SUBMISSION_DELETED = 'admin.contact_submission.deleted';
    case ADMIN_CONTACT_SUBMISSIONS_EXPORTED = 'admin.contact_submissions.exported';
    case ADMIN_CONTACT_SUBMISSION_BULK_UPDATED = 'admin.contact_submission.bulk_updated';
    case ADMIN_DATA_HEALTH_VIEWED = 'admin.data_health.viewed';
    case ADMIN_NPS_EXPORTED = 'admin.nps.exported';
    case ADMIN_EMAIL_SEND_LOGS_EXPORTED = 'admin.email_send_logs.exported';

    // User actions
    case PROFILE_UPDATED = 'profile.updated';
    case ACCOUNT_DELETED = 'account.deleted';
    case API_TOKEN_CREATED = 'api_token.created';
    case API_TOKEN_DELETED = 'api_token.deleted';
    case CONTACT_SUBMITTED = 'contact.submitted';
    case SALES_INQUIRY_SUBMITTED = 'sales_inquiry.submitted';
    case FEEDBACK_SUBMITTED = 'feedback.submitted';

    // Feature usage (frontend-originated, GA4-forwarded)
    case FEATURE_USED = 'feature.used';
    case FEATURE_SETTINGS_UPDATED = 'feature.settings_updated';

    // Engagement (frontend-originated, GA4-forwarded)
    case ENGAGEMENT_PAGE_VIEWED = 'engagement.page_viewed';
    case ENGAGEMENT_CTA_CLICKED = 'engagement.cta_clicked';
    case ENGAGEMENT_CONTACT_FORM_SUBMITTED = 'engagement.contact_form_submitted';

    // Activation (frontend-originated, GA4-forwarded)
    case ACTIVATION_MILESTONE = 'activation.milestone';

    // Errors (frontend-originated, GA4-forwarded)
    case ERROR_PAGE_VIEWED = 'error.page_viewed';

    // Limit (PQL signals)
    case LIMIT_THRESHOLD_50 = 'limit.threshold_50';
    case LIMIT_THRESHOLD_80 = 'limit.threshold_80';
    case LIMIT_THRESHOLD_100 = 'limit.threshold_100';

    /**
     * Shared event names that must exist in both backend (this enum)
     * and frontend (resources/js/lib/events.ts). The sync test in
     * tests/Unit/Enums/AnalyticsEventSyncTest.php enforces parity.
     */
    private const SHARED_EVENTS = [
        'auth.login',
        'auth.logout',
        'auth.register',
        'auth.verify_email',
        'auth.email_verified',
        'auth.password_changed',
        'auth.password_reset',
        'auth.2fa_enabled',
        'auth.2fa_disabled',
        'auth.2fa_verified',
        'auth.2fa_recovery_regenerated',
        'auth.social_login',
        'auth.social_disconnected',
        'onboarding.started',
        'onboarding.step_completed',
        'onboarding.completed',
        'trial.started',
        'trial.converted',
        'trial.expired',
        'subscription.created',
        'subscription.canceled',
        'subscription.resumed',
        'subscription.swapped',
        'subscription.quantity_updated',
        'billing.pricing_viewed',
        'billing.plan_selected',
        'billing.checkout_started',
        'billing.checkout_completed',
        'billing.subscription_canceled',
        'billing.subscription_resumed',
        'billing.plan_swapped',
        'billing.swap_confirmed',
        'billing.trial_upgrade_clicked',
        'billing.payment_failed',
        'billing.period_toggled',
        'billing.payment_method_updated',
        'profile.updated',
        'account.deleted',
        'api_token.created',
        'api_token.deleted',
        'contact.submitted',
        'feedback.submitted',
        'feature.used',
        'feature.settings_updated',
        'engagement.page_viewed',
        'engagement.cta_clicked',
        'engagement.contact_form_submitted',
        'activation.milestone',
        'error.page_viewed',
        'limit.threshold_50',
        'limit.threshold_80',
        'limit.threshold_100',
    ];

    /**
     * Get the list of shared event names for cross-system sync validation.
     *
     * @return list<string>
     */
    public static function sharedEvents(): array
    {
        return self::SHARED_EVENTS;
    }
}
