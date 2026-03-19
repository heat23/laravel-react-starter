# Event Taxonomy

## Naming Convention

All events use `category.action` format with snake_case:

```
{category}.{action}
```

**Categories:** `auth`, `onboarding`, `billing`, `feature`, `engagement`, `limit`

## Event Catalog

### Auth Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `auth.login` | `email` | Engagement |
| `auth.logout` | `email` | — |
| `auth.register` | `email`, `signup_source` | Registration |
| `auth.verify_email` | — | Registration |
| `auth.password_reset` | — | — |

### Onboarding Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `onboarding.started` | — | Registration |
| `onboarding.step_completed` | `step` | Registration |
| `onboarding.completed` | — | Registration |

### Billing Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `billing.pricing_viewed` | — | Billing |
| `billing.plan_selected` | `plan`, `billing_period` | Billing |
| `billing.checkout_started` | `plan`, `price_id`, `billing_period` | Billing |
| `billing.checkout_completed` | `plan`, `price_id`, `amount` | Billing |
| `billing.subscription_canceled` | `reason`, `immediately` | — |
| `billing.subscription_resumed` | — | — |
| `billing.plan_swapped` | `new_plan`, `new_price_id` | — |

### Feature Usage Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `feature.used` | `feature_name` | Engagement |
| `feature.api_token_created` | — | Engagement |
| `feature.webhook_created` | — | Engagement |
| `feature.settings_updated` | `setting_key` | Engagement |

### Limit Events (PQL Signals)

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `limit.threshold_50` | `limit_key`, `current`, `max` | Billing |
| `limit.threshold_80` | `limit_key`, `current`, `max` | Billing |
| `limit.threshold_100` | `limit_key`, `current`, `max` | Billing |

### Engagement Events

| Event | Properties | Funnel Stage |
|-------|-----------|--------------|
| `engagement.dashboard_viewed` | — | Engagement |
| `engagement.return_visit` | `days_since_last` | Engagement |

## Funnel Definitions

### Registration Funnel

```
visit → auth.register → auth.verify_email → onboarding.started → onboarding.completed
```

**Conversion metric:** `onboarding.completed` / `auth.register`

### Billing Funnel

```
billing.pricing_viewed → billing.plan_selected → billing.checkout_started → billing.checkout_completed
```

**Conversion metric:** `billing.checkout_completed` / `billing.pricing_viewed`

### Engagement Funnel

```
auth.login → feature.used → engagement.return_visit
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
