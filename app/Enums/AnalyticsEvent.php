<?php

namespace App\Enums;

enum AnalyticsEvent: string
{
    // Auth
    case AUTH_LOGIN = 'auth.login';
    case AUTH_LOGOUT = 'auth.logout';
    case AUTH_REGISTER = 'auth.register';
    case AUTH_VERIFY_EMAIL = 'auth.verify_email';
    case AUTH_PASSWORD_CHANGED = 'auth.password_changed';
    case AUTH_2FA_ENABLED = 'auth.2fa_enabled';
    case AUTH_2FA_DISABLED = 'auth.2fa_disabled';
    case AUTH_2FA_VERIFIED = 'auth.2fa_verified';
    case AUTH_2FA_RECOVERY_REGENERATED = 'auth.2fa_recovery_regenerated';
    case AUTH_SOCIAL_LOGIN = 'auth.social_login';
    case AUTH_SOCIAL_DISCONNECTED = 'auth.social_disconnected';

    // Onboarding
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

    // User actions
    case PROFILE_UPDATED = 'profile.updated';
    case ACCOUNT_DELETED = 'account.deleted';
    case API_TOKEN_CREATED = 'api_token.created';
    case API_TOKEN_DELETED = 'api_token.deleted';
    case CONTACT_SUBMITTED = 'contact.submitted';
    case FEEDBACK_SUBMITTED = 'feedback.submitted';

    // Limit (PQL signals)
    case LIMIT_THRESHOLD_50 = 'limit.threshold_50';
    case LIMIT_THRESHOLD_80 = 'limit.threshold_80';
    case LIMIT_THRESHOLD_100 = 'limit.threshold_100';
}
