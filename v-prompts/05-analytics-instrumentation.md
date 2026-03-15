/v Fix the following audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.

## Findings to Fix

### Fix 1: INST-03 — No event taxonomy or funnel definitions (P1, 4h est.)
**Problem:** No documented event taxonomy, naming convention, or funnel definitions. AuditService uses ad-hoc event names.
**Files:** New: docs/EVENT_TAXONOMY.md, New: resources/js/lib/events.ts
**Implementation:**
1. Create docs/EVENT_TAXONOMY.md documenting all events: naming convention (category.action format), event properties, funnel stage mapping.
2. Create resources/js/lib/events.ts with TypeScript const enum/object of all frontend event names.
3. Define funnels: Registration (visit > register > verify > onboard > activate), Billing (pricing_view > plan_select > checkout_start > checkout_complete), Engagement (login > feature_use > return_visit).
**Verify:** TypeScript compiles: `npx tsc --noEmit`

### Fix 2: INST-01 — No product analytics SDK (P0 for growth, 8h est.)
**Problem:** Only GA4 pageview tracking exists. No product analytics for in-app behavior, feature usage, or funnel conversion.
**Files:** New: resources/js/hooks/useAnalytics.ts, New: resources/js/lib/analytics.ts, resources/views/app.blade.php
**Test first:** resources/js/hooks/useAnalytics.test.ts — test hook tracks events, respects consent, queues events when SDK not loaded.
**Implementation:**
1. Create resources/js/lib/analytics.ts — an analytics abstraction layer that can dispatch to PostHog, Mixpanel, or GA4 custom events. Start with GA4 gtag('event', ...) as the default backend since GA4 is already integrated.
2. Create resources/js/hooks/useAnalytics.ts — React hook: `const { track } = useAnalytics()`. Accepts event name (from events.ts) and properties.
3. Add tracking calls to key pages: Register (signup_started, signup_completed), Login (login_success), Onboarding (onboarding_started, onboarding_completed, onboarding_step_N), Pricing (pricing_viewed, plan_selected), Dashboard (dashboard_viewed).
4. Respect cookie consent from session 2's CookieConsent component.
**Verify:** `npm test -- --run --filter=useAnalytics`

### Fix 3: INST-02 — Audit logging not structured for product analytics (P1, 6h est.)
**Problem:** AuditService logs auth events for compliance but lacks product context (tier, cohort, activation_status).
**Files:** app/Services/AuditService.php
**Test first:** tests/Unit/Services/AuditServiceTest.php — add test: `it('includes user tier and signup cohort in event metadata')`.
**Implementation:**
1. Enrich AuditService events with product context: add user's plan tier, signup date (cohort), is_activated flag to metadata.
2. Add a `logProductEvent()` method for product-specific events (not security audit events).
3. Ensure backward compatibility — existing security events continue as-is.
**Verify:** `php artisan test --filter=AuditServiceTest`

### Fix 4: INST-PLG-01 — No PQL signals instrumented (P0 for growth, 12h est.)
**Problem:** PlanLimitService checks limits but does not emit approaching-limit events. No engagement scoring.
**Files:** app/Services/PlanLimitService.php
**Test first:** tests/Unit/Services/PlanLimitServiceTest.php — add test: `it('emits approaching_limit event at 80% usage')`.
**Implementation:**
1. In PlanLimitService::canPerform(), when usage is at 50%, 80%, and 100% of limit, dispatch an event or log via AuditService.
2. Create an EngagementScoringService that scores users based on: login frequency, feature adoption (features used / features available), API token usage, settings customized.
3. Add engagement_score to admin user list as a sortable column.
**Verify:** `php artisan test --filter=PlanLimitServiceTest`

### Fix 5: INST-PLG-02 — Free-to-paid conversion funnel not tracked (P1, 4h est.)
**Problem:** Billing events are audit-logged but the full conversion funnel is not instrumented client-side.
**Files:** resources/js/Pages/Pricing.tsx (line ~80-105), app/Http/Controllers/Billing/SubscriptionController.php
**Implementation:**
1. Add analytics events using the useAnalytics hook from Fix 2: pricing_viewed (on Pricing page mount), plan_selected (on plan card click), checkout_started (on handleCheckout), checkout_completed (on success redirect).
2. In SubscriptionController, log checkout_completed via AuditService with plan_id and amount.
**Verify:** `php artisan test --filter=Billing && npm test -- --run`

### Fix 6: INST-04 — No growth dashboard in admin (P2, 8h est.)
**Problem:** Admin billing dashboard has MRR/churn but no activation rate, signup-to-paid conversion, or cohort retention.
**Files:** app/Services/AdminBillingStatsService.php, resources/js/Pages/Admin/Billing/Dashboard.tsx
**Test first:** tests/Unit/Services/AdminBillingStatsServiceTest.php — add test for activation_rate computation.
**Implementation:**
1. Add activation_rate metric to AdminBillingStatsService: activated users / total signups (where activated = completed onboarding + used 2+ features).
2. Add signup_to_paid_conversion: paid users / total signups.
3. Add a cohort retention chart: group users by signup week, show % active at week 1, 2, 4, 8.
4. Add these to the admin billing dashboard page.
**Verify:** `php artisan test --filter=AdminBillingStats`

## After All Fixes

```bash
php artisan test --parallel
npm test -- --run
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "feat(analytics): event taxonomy, analytics SDK, PQL signals, conversion funnel, growth dashboard"`
