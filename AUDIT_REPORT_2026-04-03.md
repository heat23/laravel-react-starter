# SaaS Starter Comprehensive Audit Report
**Date:** 2026-04-03 | **Session:** 8444be4e | **Depth:** Comprehensive  
**Stack:** Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind v4 + Cashier (Stripe)  
**Overall Score: 8.2 / 10** | **51 findings** (4 P1 · 27 P2 · 20 P3)

---

## Executive Summary

The Laravel React Starter template is a production-grade SaaS boilerplate with exceptional coverage: billing, auth, admin panel, webhooks, feature flags, email marketing sequences, lifecycle tracking, and analytics instrumentation. Code quality is high, the test suite is comprehensive (90+ tests, 25 admin test files, 14 billing test files), and the architecture follows Laravel 12 best practices throughout.

**Four P1 issues must be resolved before production launch:**
1. **SEC-001 / OPSRISK-001** — Redis is required for billing locks and queue, but `.env.example` defaults to `database` for both, creating a silent non-atomic billing lock and single-DB SPOF.
2. **LEGAL-001** — Legal pages render a red "Template Content" disclaimer unconditionally in production.
3. **LAUNCH-001** — No database backup strategy exists.
4. **FUNNEL-001** — `CheckExpiredTrials` command crashes at runtime with `toISOString() on string`.

The remaining 27 P2 and 20 P3 findings are quality improvements for a post-launch backlog.

---

## Score by Area

| # | Area | Score | P1 | P2 | P3 |
|---|------|-------|----|----|-----|
| 1 | Code Quality & Security | 8.5 | 1 | 3 | 0 |
| 2 | Architecture & System Design | 8.5 | 0 | 2 | 1 |
| 3 | Technical Debt | 8.0 | 0 | 2 | 2 |
| 4 | Test Suite Quality | 9.0 | 0 | 1 | 1 |
| 5 | Accessibility (WCAG 2.1 AA) | 8.5 | 0 | 1 | 1 |
| 6 | UX Copy | 8.0 | 0 | 2 | 1 |
| 7 | UI/UX Design | 8.5 | 0 | 0 | 2 |
| 8 | Design System | 9.0 | 0 | 0 | 2 |
| 9 | Compliance & Legal | 7.5 | 1 | 3 | 0 |
| 10 | Operational Risk | 8.0 | 1 | 2 | 0 |
| 11 | Deployment & Operations | 8.5 | 0 | 2 | 0 |
| 12 | Documentation | 9.0 | 0 | 0 | 2 |
| 13 | SEO & Public Pages | 8.0 | 0 | 1 | 2 |
| 14 | GTM Strategy | 7.5 | 0 | 3 | 2 |
| 15 | Growth Funnel | 8.0 | 0 | 3 | 2 |
| 16 | Launch Operations | 7.5 | 1 | 3 | 2 |
| 17 | Admin Panel | 8.5 | 0 | 3 | 1 |
| **Totals** | | **8.2** | **4** | **27** | **20** |

---

## P1 Findings — Fix Before Launch

### SEC-001 · BillingService Redis lock degrades silently when CACHE_STORE≠redis
**File:** `app/Services/BillingService.php:295` | `1h`  
`.env.example` defaults to `CACHE_STORE=database`. The database driver does not support atomic locks — `Cache::lock()` silently becomes non-atomic, eliminating concurrency protection for subscription mutations.  
**Fix:** Add boot-time assertion in `AppServiceProvider` — if billing enabled and cache driver is not `redis`, throw/warn. Update `.env.example` to default `CACHE_STORE=redis`.

---

### OPSRISK-001 · Default QUEUE_CONNECTION=database creates single-DB SPOF
**File:** `.env.example:91` | `1h`  
Queue workers, web requests, and cache share the same database. Slow billing jobs increase DB pressure and degrade web response times. A DB outage takes down both simultaneously.  
**Fix:** Change `.env.example` default to `QUEUE_CONNECTION=redis`. Add `AppServiceProvider` boot warning when billing is enabled and queue driver is `database`.

---

### LEGAL-001 · Legal pages show placeholder disclaimer unconditionally in production
**File:** `resources/js/Components/legal/LegalContent.tsx:1` | `1h`  
`<AlertTitle>Template Content — Do Not Use As-Is</AlertTitle>` renders for all users. Under GDPR, a valid privacy policy is required before processing personal data.  
**Fix:** Gate disclaimer behind `process.env.NODE_ENV === 'development'`. Add legal content replacement to `scripts/init.sh` launch checklist.

---

### LAUNCH-001 · No database backup strategy
**File:** `composer.json` (missing) | `3h`  
No `spatie/laravel-backup` or equivalent. No mysqldump, no S3 export, no scheduled backup job. Audit logs are pruned permanently. A server failure or accidental migration = permanent data loss.  
**Fix:** `composer require spatie/laravel-backup`. Configure daily DB-only backups to S3. Schedule `backup:run` + `backup:clean` in `routes/console.php`.

---

### FUNNEL-001 · CheckExpiredTrials crashes calling toISOString() on string
**File:** `app/Console/Commands/CheckExpiredTrials.php` | `0.5h`  
Confirmed by PHPStan baseline: `Cannot call method toISOString() on string`. `trial_ends_at` is retrieved as a string, not cast to Carbon. The trial expiration flow is completely broken.  
**Fix:** Wrap with `Carbon::parse($value)->toISOString()` or add `trial_ends_at` to User `$casts` as `'datetime'`.

---

## P2 Findings — High Priority Backlog

### SEC-002 · Health endpoint accepts token via query string
**File:** `app/Http/Controllers/HealthCheckController.php:45` | `0.5h`  
Query string tokens appear in logs and browser history. Remove `allow_query_token` code path entirely.

### SEC-003 · Blog DOMPurify ALLOWED_ATTR includes 'class' — risky default for template consumers
**File:** `resources/js/Pages/Blog/Show.tsx:127` | `0.25h`  
Comment warns to remove for user content. Remove `'class'` from `ALLOWED_ATTR` now.

### SEC-004 · Stripe webhook skips signature verification in preview environments
**File:** `app/Http/Controllers/Billing/StripeWebhookController.php:23` | `0.5h`  
Change condition from `!environment('local')` to `!isLocal()` to also require secret in preview.

### ARCH-001 · HandleInertiaRequests runs 3+ queries per request for notifications/limits
**File:** `app/Http/Middleware/HandleInertiaRequests.php:43` | `1h`  
Increase `limit_warnings` cache TTL from 60s to 300s. Consider polling endpoint for notification count.

### ARCH-003 · BroadcastAnnouncementJob has $tries=1 — broadcast failures are permanent
**File:** `app/Jobs/BroadcastAnnouncementJob.php:14` | `2h`  
Increase to `$tries = 3`. Add `EmailSendLog` idempotency guard to skip already-notified users on retry.

### DEBT-001 · PHPStan baseline suppresses 499 errors including 2 confirmed real type bugs
**File:** `phpstan-baseline.neon` + `CheckExpiredTrials.php` + `SendTrialNudges.php` | `4h`  
Fix `toISOString()` on string and `Carbon|null` argument mismatch. Target zero new suppressions per sprint.

### DEBT-002 · Plan display prices can silently diverge from Stripe prices
**File:** `config/plans.php:61` | `3h`  
Add deploy-time artisan command that fetches Stripe price objects and asserts they match `plans.php` display prices.

### TEST-002 · Missing test for involuntary churn win-back notification dispatch
**File:** `app/Http/Controllers/Billing/StripeWebhookController.php:71` | `1h`  
Add test asserting `InvoluntaryChurnWinBackNotification` dispatches when `cancellation_details.reason=payment_failed`.

### A11Y-001 · Dashboard health score 'At Risk' uses text-orange-500 without dark mode variant
**File:** `resources/js/Pages/Dashboard.tsx:60` | `0.25h`  
Change to `'text-orange-600 dark:text-orange-400'` for WCAG 4.5:1 contrast.

### COPY-001 · Dunning email plan name always shows "your plan" due to wrong config key
**File:** `app/Console/Commands/SendDunningReminders.php:107` | `0.5h`  
Replace `config('plans.tiers', [])` with `BillingService::resolveTierFromPrice()` + `config('plans.{tier}.name')`.

### COPY-002 · Retention coupon success message hardcodes '20%' discount
**File:** `app/Http/Controllers/Billing/SubscriptionController.php:285` | `0.5h`  
Add `plans.retention_coupon_percent` config key. Use in success message.

### COPY-003 · Legal pages render placeholder in-UI warning unconditionally
**File:** `resources/js/Components/legal/LegalContent.tsx:4` | `1h`  
(See LEGAL-001 above — same file, compliance severity elevates to P1.)

### LEGAL-002 · Marketing consent not persisted server-side
**File:** `app/Http/Controllers/Api/ConsentController.php:28` | `1h`  
Add `setSetting('marketing_consent', ...)` and return `consent_id` UUID for GDPR audit trail.

### LEGAL-003 · Audit log retention (90 days) too short for security events
**File:** `config/health.php:8` | `2h`  
Implement tiered retention: auth.* events → 365 days, general events → 90 days.

### LEGAL-004 · PaymentFailedNotification includes marketing unsubscribe on required service email
**File:** `app/Notifications/PaymentFailedNotification.php:9` | `2h`  
Remove `HasUnsubscribeLink` from `PaymentFailed`, `PaymentActionRequired`, `PaymentRecovered`, `RefundProcessed`. Add separate `billing_emails` preference key.

### OPSRISK-002 · StripeWebhookController swallows lifecycle transition failures silently
**File:** `app/Http/Controllers/Billing/StripeWebhookController.php:82` | `1h`  
Add `Log::error('lifecycle_transition_failed', [...])` inside catch blocks.

### OPSRISK-003 · No circuit breaker on Stripe API calls
**File:** `app/Http/Controllers/Billing/SubscriptionController.php` | `3h`  
Add pre-flight Stripe availability check cached flag. Open circuit after 3 consecutive 5xx errors.

### DEPLOY-001 · PHPStan + Pint only run on PRs, not direct pushes to main
**File:** `.github/workflows/ci.yml` | `0.25h`  
Remove `if: github.event_name == 'pull_request'` condition from `code-quality` job.

### DEPLOY-002 · Sentry disabled by default with no production warning
**File:** `.env.example:287` | `0.5h`  
Uncomment Sentry vars. Add `AppServiceProvider` boot warning when production + no `SENTRY_LARAVEL_DSN`.

### SEO-001 · Welcome + Pricing pages missing canonical link and og:url
**File:** `resources/js/Pages/Welcome.tsx` + `Pricing.tsx` | `1h`  
Pass `canonicalUrl` from controllers. Add `<link rel='canonical'>` and `<meta property='og:url'>` in Head.

### GTM-001 · GA4 Measurement Protocol disabled by default—no server-side analytics
**File:** `config/services.php` | `0.5h`  
Add `GA4_MEASUREMENT_PROTOCOL_ENABLED=true` to `.env.example` production section with explanation comment.

### GTM-002 · No retargeting pixel infrastructure on marketing pages
**File:** `resources/views/app.blade.php` | `2h`  
Add consent-gated Facebook Pixel / Google Ads tag following existing GA4 cookie-consent pattern.

### GTM-003 · Blog platform ships with no default content—empty /blog at launch
**File:** `resources/content/blog/` (missing) | `2h`  
Add 2-3 example blog posts in `resources/content/blog/` or add `FEATURE_BLOG` flag to gate empty blog.

### GTM-004 · NPS survey has eligible endpoint but no automated dispatch command
**File:** `routes/web.php` | `3h`  
Create `app/Console/Commands/SendNpsSurveys.php`. Target users 30 days post-ACTIVATED with 90-day cooldown.

### FUNNEL-002 · TrackLastActivity may write to non-existent last_active_at column
**File:** `app/Http/Middleware/TrackLastActivity.php` | `1h`  
Verify `last_active_at` column exists in users migration. Add if missing, with index for re-engagement query.

### FUNNEL-003 · SendWinBackEmails does not guard against billing being disabled
**File:** `app/Console/Commands/SendWinBackEmails.php:47` | `0.5h`  
Add `if (!config('features.billing.enabled')) return 0;` guard at start of `handle()`.

### ADMIN-001 · Audit log CSV export available to all admins—PII not role-gated
**File:** `routes/admin.php` | `1h`  
Gate audit log export route with `super_admin` middleware.

### ADMIN-002 · AdminUsersController::show() loads all audit logs without row limit
**File:** `app/Http/Controllers/Admin/AdminUsersController.php:108` | `1h`  
Add `->limit(50)` to audit log query. Add `->limit(20)` to stage history query.

### ADMIN-003 · Admin billing stats cache not invalidated on subscription webhook events
**File:** `app/Http/Controllers/Billing/StripeWebhookController.php` | `0.5h`  
Add `Cache::forget(AdminCacheKey::BILLING_STATS->value)` and `BILLING_TIER_DIST` to all subscription webhook handlers.

### LAUNCH-002 · No zero-downtime deploy—migrations run on live database
**File:** `scripts/vps-setup.sh` | `2h`  
Wrap migrations in `php artisan down` / `php artisan up` with a custom 503 page.

### LAUNCH-003 · SESSION_SECURE_COOKIE not in .env.example production defaults
**File:** `.env.example` | `0.5h`  
Add `SESSION_SECURE_COOKIE=true` to production section. Add `AppServiceProvider` boot warning.

### LAUNCH-004 · E2E tests hardcode localhost:8000
**File:** `playwright.config.ts` + `.github/workflows/ci.yml` | `0.5h`  
Use `process.env.APP_URL || 'http://localhost:8000'` as `baseURL` in playwright.config.ts.

### LAUNCH-005 · Sentry performance monitoring disabled (traces_sample_rate=0.0)
**File:** `config/sentry.php` | `0.25h`  
Add `SENTRY_TRACES_SAMPLE_RATE=0.1` to `.env.example` production section.

---

## P3 Findings — Polish Backlog

| ID | Title | File | Hours |
|----|-------|------|-------|
| ARCH-002 | Login streak logic belongs in CustomerHealthService | `DashboardController.php:51` | 1h |
| DEBT-003 | Sitemap hardcoded lastmod dates go stale | `SeoController.php:143` | 1h |
| DEBT-004 | ExportController route 'export.users' misleadingly named | `ExportController.php:14` | 0.5h |
| TEST-001 | AuthenticationTest uses PHPUnit syntax in Pest suite | `AuthenticationTest.php:13` | 0.5h |
| A11Y-002 | Flash toasts lack differentiated ARIA role for error severity | `sonner.tsx:8` | 0.5h |
| UX-001 | Contact page success uses raw div instead of Alert component | `Contact.tsx:88` | 0.25h |
| UX-002 | Admin Notifications list body has no truncation | `Admin/Notifications/Dashboard.tsx:196` | 0.25h |
| DS-001 | ThemeProvider hardcoded hex fallbacks bypass design tokens | `ThemeProvider.tsx:57` | 0.25h |
| DS-002 | NPS responses use raw Tailwind colors instead of semantic tokens | `NpsResponses/Index.tsx:47` | 0.25h |
| DOCS-001 | README screenshots section is empty | `README.md` | 1h |
| DOCS-002 | No OpenAPI/Scribe docs generated despite api_docs feature existing | `config/features.php` | 1h |
| SEO-002 | Welcome page has no SoftwareApplication structured data | `Welcome.tsx` | 1h |
| SEO-003 | Contact + About pages missing meta description | `Contact.tsx` + `About.tsx` | 0.25h |
| GTM-005 | Missing funnel_step property for GA4 conversion funnel | `AnalyticsEvent.php` | 2h |
| FUNNEL-004 | ExpansionNudgeNotification has no automated trigger | `ExpansionNudgeNotification.php` | 2h |
| FUNNEL-005 | EngagementScoringService scores existence not usage | `EngagementScoringService.php` | 2h |
| LAUNCH-006 | vps-setup.sh has no atomic deploy support | `scripts/vps-setup.sh` | 4h |
| ADMIN-004 | Feature flag UI doesn't warn route-dependent flags need restart | `FeatureFlagService.php` | 1h |

---

## Strengths — What's Working Well

**Security:** Rate limiting on all auth endpoints, CSRF via Sanctum, hash_equals webhook verification, Redis-locked billing, session regeneration on login, HSTS + CSP headers, HMAC-SHA256 signed outgoing webhooks.

**Test Coverage:** 90+ tests across 14 billing files, 25+ admin files, contract tests, Playwright E2E, frontend Vitest with .test.tsx counterparts. Engagement score sync test between PHP enum and TypeScript.

**Analytics:** 196 events across 14 categories, TypeScript-enforced event schemas with compile-time exhaustiveness check, server-side GA4 Measurement Protocol, PII blocklist, frontend/backend event parity test.

**Email Marketing:** 7 notification sequence types with 18+ variants — welcome (3 emails), onboarding reminders (3), re-engagement (4), trial nudge (3), win-back (3), dunning (3), upgrade/expansion nudge. All feature-flag-aware, with engagement score personalization.

**Lifecycle Tracking:** 6-stage lifecycle enum with transition history table, audit log outside transaction, funnel visualization in admin dashboard.

**Documentation:** Comprehensive CLAUDE.md with billing gotchas, query budgets, cache invalidation checklist, service decision framework, and review checklist. 14 docs/ files covering planning, testing, debugging, ADRs.

**Admin Panel:** 25 controllers covering users (bulk actions, impersonation), billing stats (MRR, cohort retention, tier distribution), feature flag management (global + per-user overrides), audit log export, failed jobs, cache management, NPS, roadmap, sessions, system info.

---

## Effort Estimates by Priority

| Priority | Count | Total Hours |
|----------|-------|-------------|
| P1 | 4 | ~6h |
| P2 | 27 | ~38h |
| P3 | 20 | ~18h |
| **Total** | **51** | **~62h** |

---

## Recommended Sprint Plan

### Sprint 1 — Launch Blockers (Week 1, ~10h)
1. SEC-001 + OPSRISK-001: Redis requirement assertions + .env.example defaults
2. LEGAL-001: Gate legal disclaimer behind dev-only conditional
3. LAUNCH-001: Install spatie/laravel-backup, schedule daily backup
4. FUNNEL-001: Fix CheckExpiredTrials Carbon cast
5. COPY-001: Fix dunning email plan name resolver

### Sprint 2 — Security & Compliance (Week 2, ~12h)
1. LEGAL-002: Persist marketing consent server-side
2. LEGAL-003: Tiered audit log retention
3. LEGAL-004: Remove unsubscribe from transactional billing emails
4. LAUNCH-002: Add maintenance mode to deploy script
5. LAUNCH-003: SESSION_SECURE_COOKIE default
6. DEPLOY-001: Remove PR-only gate from code-quality CI job
7. DEPLOY-002: Sentry production warning
8. ADMIN-001: Gate audit log export to super_admin

### Sprint 3 — Quality & GTM (Week 3, ~15h)
1. GTM-001: Document + enable GA4 Measurement Protocol
2. GTM-003: Seed blog content or add blog feature flag
3. GTM-004: Create NpsSurvey dispatch command
4. FUNNEL-002: Verify last_active_at migration column
5. ADMIN-002: Limit audit log rows in user detail
6. ADMIN-003: Add billing cache invalidation to webhooks
7. SEO-001: Add canonical + og:url to Welcome + Pricing

### Sprint 4 — Polish (Ongoing)
Remaining P3 items, PHPStan baseline reduction (DEBT-001), Scribe API docs, README screenshots.

---

*Generated: 2026-04-03 | Session: 8444be4e-2923-4d4e-9ce0-40c50811ee9a*
