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
