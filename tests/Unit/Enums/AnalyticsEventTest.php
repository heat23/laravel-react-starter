<?php

use App\Enums\AnalyticsEvent;

it('contains all documented taxonomy events', function () {
    $taxonomyEvents = [
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
        'admin.unauthorized_access_attempt',
        'admin.toggle_admin',
        'admin.user_deactivated',
        'admin.user_restored',
        'admin.user_viewed',
        'admin.impersonation_started',
        'admin.impersonation_stopped',
        'admin.audit_logs_exported',
        'admin.subscriptions_exported',
        'admin.users_exported',
        'admin.password_reset_sent',
        'admin.failed_job.retry',
        'admin.failed_job.delete',
        'admin.feature_flag.global_override',
        'admin.feature_flag.global_override_removed',
        'admin.feature_flag.user_override',
        'admin.feature_flag.user_override_removed',
        'admin.feature_flag.all_user_overrides_removed',
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

    foreach ($taxonomyEvents as $event) {
        expect(AnalyticsEvent::tryFrom($event))->not->toBeNull(
            "Missing enum case for taxonomy event: {$event}"
        );
    }
});

it('all enum values follow category.action format', function () {
    foreach (AnalyticsEvent::cases() as $case) {
        expect($case->value)->toMatch('/^[a-z_0-9]+(\.[a-z_0-9]+)+$/');
    }
});

it('has no duplicate values', function () {
    $values = array_map(fn ($case) => $case->value, AnalyticsEvent::cases());
    expect($values)->toHaveCount(count(array_unique($values)));
});
