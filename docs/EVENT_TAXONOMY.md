# Event Taxonomy

## Naming Convention

All events use `category.action` format with snake_case:

```
{category}.{action}
```

**Categories:** `auth`, `onboarding`, `trial`, `subscription`, `billing`, `admin`, `profile`, `account`, `api_token`, `contact`, `feedback`, `limit`

## Event Catalog

### Auth Events

> **PII rule:** Never include `email` or any personally identifiable information in GA4 event properties. Sending email addresses to GA4 violates GDPR/CCPA.

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `auth.login` | `source` | Engagement |
| `auth.logout` | — | — |
| `auth.register` | `source` | Registration |
| `auth.verify_email` | — | Registration |
| `auth.password_changed` | — | — |
| `auth.2fa_enabled` | — | — |
| `auth.2fa_disabled` | — | — |
| `auth.2fa_verified` | — | — |
| `auth.2fa_recovery_regenerated` | — | — |
| `auth.social_login` | `provider` | Registration |
| `auth.social_disconnected` | `provider` | — |

### Onboarding Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `onboarding.completed` | — | Registration |

### Trial Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `trial.started` | `plan`, `trial_ends_at` | Billing |
| `trial.converted` | `plan` | Billing |
| `trial.expired` | `plan` | Billing |

### Subscription Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `subscription.created` | `plan`, `price_id`, `billing_period` | Billing |
| `subscription.canceled` | `reason`, `immediately` | — |
| `subscription.resumed` | — | — |
| `subscription.swapped` | `new_plan`, `new_price_id` | — |
| `subscription.quantity_updated` | `quantity` | — |

### Billing Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `billing.payment_method_updated` | — | — |

### User Action Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `profile.updated` | — | Engagement |
| `account.deleted` | — | — |
| `api_token.created` | — | Engagement |
| `api_token.deleted` | — | — |
| `contact.submitted` | — | Engagement |
| `feedback.submitted` | — | Engagement |

### Limit Events (PQL Signals)

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `limit.threshold_50` | `limit_key`, `current`, `max` | Billing |
| `limit.threshold_80` | `limit_key`, `current`, `max` | Billing |
| `limit.threshold_100` | `limit_key`, `current`, `max` | Billing |

### Admin Events

Admin events are server-side only and are never forwarded to GA4 in production by default. They appear in the audit log and can be forwarded to an analytics backend that supports admin-level event ingestion.

#### User Management

| Event | Properties |
|-------|-----------|
| `admin.toggle_admin` | `target_user_id`, `is_admin` |
| `admin.user_deactivated` | `target_user_id` |
| `admin.user_restored` | `target_user_id` |
| `admin.user_viewed` | `target_user_id` |
| `admin.user.created` | `target_user_id` |
| `admin.user.updated` | `target_user_id` |
| `admin.password_reset_sent` | `target_user_id` |
| `admin.session.terminated` | `target_user_id` |
| `admin.token.revoked` | `target_user_id` |

#### Security

| Event | Properties |
|-------|-----------|
| `admin.unauthorized_access_attempt` | `path` |

#### Data Export

| Event | Properties |
|-------|-----------|
| `admin.audit_logs_exported` | — |
| `admin.subscriptions_exported` | — |
| `admin.users_exported` | — |
| `admin.tokens.exported` | — |
| `admin.feedback.exported` | — |
| `admin.nps.exported` | — |
| `admin.email_send_logs.exported` | — |
| `admin.contact_submissions.exported` | — |
| `admin.roadmap.exported` | — |

#### Billing Admin

| Event | Properties |
|-------|-----------|
| `admin.billing.subscriptions_viewed` | — |
| `admin.billing.subscription_viewed` | `subscription_id` |

#### Feature Flags

| Event | Properties |
|-------|-----------|
| `admin.feature_flag.global_override` | `flag`, `enabled` |
| `admin.feature_flag.global_override_removed` | `flag` |
| `admin.feature_flag.user_override` | `flag`, `enabled`, `target_user_id` |
| `admin.feature_flag.user_override_removed` | `flag`, `target_user_id` |
| `admin.feature_flag.all_user_overrides_removed` | `flag` |

#### Failed Jobs

| Event | Properties |
|-------|-----------|
| `admin.failed_job.retry` | `job_id` |
| `admin.failed_job.delete` | `job_id` |
| `admin.failed_job.bulk_retry` | `count` |
| `admin.failed_job.bulk_delete` | `count` |

#### Infrastructure

| Event | Properties |
|-------|-----------|
| `admin.cache_flushed` | — |
| `admin.notification.sent` | `channel`, `target_user_id` |

#### Webhooks

| Event | Properties |
|-------|-----------|
| `admin.webhook_endpoint.restored` | `endpoint_id` |

#### Feedback & Roadmap

| Event | Properties |
|-------|-----------|
| `admin.feedback.updated` | `feedback_id` |
| `admin.feedback.deleted` | `feedback_id` |
| `admin.feedback.bulk_updated` | `count` |
| `admin.roadmap_entry.created` | `entry_id` |
| `admin.roadmap_entry.updated` | `entry_id` |
| `admin.roadmap_entry.deleted` | `entry_id` |

#### Contact Submissions

| Event | Properties |
|-------|-----------|
| `admin.contact_submission.updated` | `submission_id` |
| `admin.contact_submission.deleted` | `submission_id` |
| `admin.contact_submission.bulk_updated` | `count` |

#### Data Health

| Event | Properties |
|-------|-----------|
| `admin.data_health.viewed` | — |

## Funnel Definitions

### Registration Funnel

```
visit → auth.register → auth.verify_email → onboarding.completed
```

**Conversion metric:** `onboarding.completed` / `auth.register`

### Billing Funnel

```
trial.started → trial.converted → subscription.created
```

or direct:

```
subscription.created (no trial)
```

**Conversion metric:** `subscription.created` / `auth.register`

### Churn Funnel

```
limit.threshold_80 → limit.threshold_100 → subscription.canceled
```

**Expansion signal:** `limit.threshold_80` without subsequent `subscription.swapped` within 7 days.

### Engagement Funnel

```
auth.login → profile.updated / api_token.created / feedback.submitted → auth.login (return)
```

**Activation metric:** Users who completed onboarding. (Future: AND used 2+ features within first 7 days — requires feature.used event instrumentation.)

## Product Context (Backend Enrichment)

Backend audit events are automatically enriched with:

| Property | Source | Description |
|----------|--------|-------------|
| `plan_tier` | `PlanLimitService::getUserPlan()` | Current plan (free/pro/team/enterprise) |
| `signup_cohort` | `User::created_at` | Signup month (YYYY-MM) |
| `is_activated` | Onboarding + feature usage | Has completed onboarding and used 2+ features |

## Frontend Analytics Integration

Frontend events are dispatched via the `useAnalytics` hook which:
1. Checks cookie consent before sending
2. Uses GA4 `gtag('event', ...)` as the default backend
3. Event names from `resources/js/lib/events.ts` are the canonical source

## Canonical Source

The complete list of event cases is defined in `app/Enums/AuditEvent.php`. This document mirrors that enum. When adding a new event case to the enum, update this file in the same PR.
