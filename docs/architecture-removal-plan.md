# Architecture Removal Plan

**Context:** Acting on the sole-operator review. Scope: SSR hybrid, Scribe, admin impersonation, lifecycle/scoring stack.
**Prerequisite reading:** [`architecture-review-sole-operator.md`](./architecture-review-sole-operator.md)

This document is a pre-flight plan, not a finished refactor. Each phase lists **every file touched**, the blast radius, the test gate to run, and (for the lifecycle stack) a **scope decision that requires approval before any deletion**.

Each phase is intended as a single commit. If a phase breaks the quality gate, stop and investigate — do not stack commits.

---

## Quality gate (run after each phase)

```bash
bash scripts/test-quality-check.sh
# OR manually:
php artisan test --parallel
npm test
vendor/bin/phpstan analyse
vendor/bin/pint --test
npm run lint
npm run build
```

For phases 2 and 4 below, also run: `composer install --no-interaction` (Scribe removal) and `php artisan migrate:fresh --seed` (if any migrations are dropped).

---

## Phase 1 — Remove SSR (keep the SEO shell)

**Rationale:** Two code paths doing the same crawler-fallback job. For a sole op, the Blade shell wins (no Node process in prod, one less build step, one less bundle to ship).

**Current wiring (verified):**
- `vite.config.ts` — `laravel({ input: ['resources/js/app.tsx'], ssr: 'resources/js/ssr.tsx', refresh: true })`
- `package.json` — `"build": "vite build && vite build --ssr"`
- `resources/js/ssr.tsx` — 1 file, SSR entry point
- `bootstrap/ssr/` — generated bundle (`ssr.js` ~1.7 MB, `ssr-manifest.json`)
- No `INERTIA_SSR_ENABLED` references remain in code today; `app.blade.php` uses plain `@inertiaHead` / `@inertia` which also works without SSR.
- `seo-shell.blade.php` — 44 lines, already the active fallback.

**Files deleted**
1. `resources/js/ssr.tsx`
2. `bootstrap/ssr/ssr.js`
3. `bootstrap/ssr/ssr-manifest.json`
4. `bootstrap/ssr/` directory itself (if empty after the above)

**Files edited**
1. `vite.config.ts` — remove `ssr: 'resources/js/ssr.tsx'` from the `laravel()` plugin config
2. `package.json` — change `"build": "vite build && vite build --ssr"` → `"build": "vite build"`
3. `.env.example` — remove `INERTIA_SSR_ENABLED` if present
4. `.claude/rules/seo.md` — update the `## SSR` section: drop the "Both bundles must compile together" paragraph, replace with "SSR is not used. The Blade SEO shell in `resources/views/partials/seo-shell.blade.php` is the canonical crawler fallback."
5. `CLAUDE.md` — if it mentions SSR, remove
6. Any `supervisord`/deploy config referencing `bootstrap/ssr/ssr.mjs` — none found in source; recheck deploy/

**Blast radius:** Minimal. Inertia in non-SSR mode just renders server-side HTML shell + hydrates client-side. The SEO shell already injects H1/breadcrumbs/internal nav in a `hidden` div for crawlers.

**Tests that should still pass:**
- `tests/Feature/Seo/SeoShellRendersContentTest.php` (SEO shell presence)
- `tests/Feature/Seo/TitleLengthTest.php`
- `tests/Feature/Seo/JsonLdValidityTest.php`

**Commit message suggestion:**
```
Remove SSR build path

Delete the vite SSR entry and bundle. Rely on the Blade SEO shell for
crawler content. Drops one build step, ~1.7 MB of emitted artifacts per
build, and eliminates the Node SSR process from the prod deploy story.
```

---

## Phase 2 — Remove Scribe (api docs)

**Rationale:** No API consumers yet → no docs needed. Scribe is dev-dep maintenance and annotation discipline with zero current value.

**Current footprint (verified):**
- `composer.json` — `"knuckleswtf/scribe": "^5.7"` in require-dev
- `config/scribe.php` — 1 file
- `config/features.php` — `api_docs` block (lines 136–137)
- `app/Http/Middleware/HandleInertiaRequests.php` line 196 — `'apiDocs' => $features['api_docs']`
- `app/Http/Controllers/SeoController.php` line 226 — `if (config('features.api_docs.enabled', false)) { ... }` block (probably excludes `/docs` from sitemap/robots — check before deletion)
- `tests/Feature/ApiDocsTest.php` — 2 tests, the second is a no-op expectation
- `resources/js/types/index.ts` line 35 — `apiDocs: boolean` in features type
- `resources/js/Layouts/DashboardLayout.tsx` line 203 — `{features.apiDocs && (...)}` nav link block
- `resources/js/Pages/Dashboard.tsx` line 163 — `...(features?.apiDocs ? [{ label: 'Read the API docs', href: '/docs' }] : [])`
- `resources/js/Pages/Features/FeatureFlags.tsx` line 32 — entry in FeatureFlags marketing page
- ~9 `.test.tsx` files hard-code `apiDocs: false` in fake props — these compile away when the type field is removed
- `@group Notifications`, `@group User Settings`, `@group API Tokens` PHPDoc annotations on 3 API controllers — Scribe-only, safe to delete but cosmetic
- `README.md`, `CLAUDE.md`, `docs/FEATURE_FLAGS.md` — any `/docs`, `api_docs`, `Scribe` references

**Files deleted**
1. `config/scribe.php`
2. `tests/Feature/ApiDocsTest.php`

**Files edited**
1. `composer.json` — remove `knuckleswtf/scribe` from require-dev; run `composer update --no-interaction` to refresh the lock
2. `config/features.php` — delete the `api_docs` block (lines ~128–137)
3. `app/Http/Middleware/HandleInertiaRequests.php` — remove `'apiDocs' => $features['api_docs']`
4. `app/Http/Controllers/SeoController.php` — remove the `api_docs` block at line 226 (delete the `if` and its body)
5. `resources/js/types/index.ts` — remove `apiDocs: boolean` field
6. `resources/js/Layouts/DashboardLayout.tsx` — remove the `{features.apiDocs && ...}` nav block
7. `resources/js/Pages/Dashboard.tsx` — remove the `...(features?.apiDocs ? ...)` spread
8. `resources/js/Pages/Features/FeatureFlags.tsx` — remove the `api_docs.enabled` row
9. All `.test.tsx` files with `apiDocs: false` literals — remove the line (9 tests found)
10. `CLAUDE.md`, `README.md`, `docs/FEATURE_FLAGS.md` — strip any mentions
11. `app/Http/Controllers/Api/NotificationController.php`, `.../UserSettingsController.php`, `.../TokenController.php` — optional: remove `@group` PHPDoc (harmless if left)

**Blast radius:** Low. `apiDocs` is a boolean; call sites are explicit and finite. Drop the test file and the feature-flag check block; everything else is pure removal.

**Commit message suggestion:**
```
Remove Scribe API docs

Drop knuckleswtf/scribe, config/scribe.php, FEATURE_API_DOCS flag, the
/docs gating in SeoController, and the Inertia features.apiDocs prop with
its nav link and feature page row. No API consumers today; add back
per-project if a public API surfaces.
```

---

## Phase 3 — Remove admin impersonation

**Rationale:** Self-inflicted-wound potential. SSH tinker is faster and safer for a sole op than maintaining a correct impersonation flow (analytics leakage, session handling, CSRF, "can't impersonate admin" rules, etc.).

**Current footprint (verified):**
- `app/Http/Controllers/Admin/AdminImpersonationController.php` (101 LOC)
- `resources/js/Components/admin/ImpersonationBanner.tsx`
- `tests/Feature/Admin/AdminImpersonationTest.php`
- `routes/admin.php` — `Route::post('/users/{user}/impersonate', ...)` (lines 82–85) + `Route::post('/admin/impersonate/stop', ...)` (lines 347–350)
- `app/Enums/AnalyticsEvent.php` lines 62–63 — `ADMIN_IMPERSONATION_STARTED`, `ADMIN_IMPERSONATION_STOPPED` cases
- `app/Models/AuditLog.php` line 23 — `'admin.impersonation'` in some allowlist
- `app/Http/Middleware/HandleInertiaRequests.php` lines 172–174 — shared `impersonating` prop
- `resources/js/types/index.ts` lines 22–24 — `impersonating?` type
- `resources/js/Components/sidebar/sidebar-layout.tsx` lines 10 + 237 — banner import and render
- `resources/js/Layouts/DashboardLayout.tsx` lines 16 + 80 — banner import and render
- `resources/js/hooks/useAdminAction.ts` lines 8 + 62 — `'impersonate'` action branch
- `v-prompts/02-impersonation-session-timeout.md` — cleanup spec, remove
- Snapshot dirs under `.claude/prompt-pack-runs/20260416_205152_a7a5fabd_v-remediation-phase-01-prompts/` — these are stored history, NOT deleted

**Files deleted**
1. `app/Http/Controllers/Admin/AdminImpersonationController.php`
2. `resources/js/Components/admin/ImpersonationBanner.tsx`
3. `tests/Feature/Admin/AdminImpersonationTest.php`
4. `v-prompts/02-impersonation-session-timeout.md`

**Files edited**
1. `routes/admin.php` — remove both impersonation routes + the `use App\Http\Controllers\Admin\AdminImpersonationController;` import
2. `app/Enums/AnalyticsEvent.php` — delete the two cases (lines 62–63). **Check** that no active AuditLog rows reference them before running in prod — for the starter repo this is fine
3. `app/Models/AuditLog.php` — remove `'admin.impersonation'` from whatever allowlist array at line 23
4. `app/Http/Middleware/HandleInertiaRequests.php` — remove the `impersonating` closure (lines 172–174)
5. `resources/js/types/index.ts` — remove the `impersonating?` field from `Auth`
6. `resources/js/Components/sidebar/sidebar-layout.tsx` — remove import + `<ImpersonationBanner />` render
7. `resources/js/Layouts/DashboardLayout.tsx` — remove import + render
8. `resources/js/hooks/useAdminAction.ts` — remove the `'impersonate'` union member and the `type === 'impersonate'` branch
9. `resources/js/Pages/Admin/Users/*` — if any button or action triggers impersonate, delete it (scan before edit)

**Blast radius:** Medium. Routes and controller are self-contained; the shared Inertia prop and two layout banner components need coordinated removal to avoid dead imports.

**Commit message suggestion:**
```
Remove admin impersonation

Delete AdminImpersonationController, ImpersonationBanner, impersonate
route pair, shared Inertia `impersonating` prop, the AnalyticsEvent
cases, and the useAdminAction 'impersonate' branch. Not needed for a
sole-operator workflow; SSH tinker is the correct tool.
```

---

## Phase 4 — Lifecycle / analytics surgery — **REQUIRES SCOPE DECISION**

The original recommendation was "delete the lifecycle stack." Discovery shows the stack is **not a self-contained platform** — it's braided into audit logging, user onboarding, Stripe webhooks, the end-user dashboard, and admin screens. A blanket delete is a several-day rewrite and will break ~40 tests.

Here is the honest decomposition and three scope options. **Pick one before I proceed.**

### What actually lives inside "the lifecycle stack"

| Piece | LOC | Used by | Coupling |
|---|---|---|---|
| `AnalyticsEvent` enum | 104 cases | AuditService, 15+ controllers, routes | **Very high** — it's the audit event vocabulary, misnamed |
| `AuditService` | 173 | Everywhere | **Keep** — this is the audit log writer |
| `LifecycleService` (state machine) | 149 | RegisteredUserController, OnboardingController, StripeWebhookController | **Medium** — runs on signup/onboarding/payment-fail |
| `LifecycleStage` enum | — | User model, AdminDashboardController, LifecycleService | **Medium** — state values |
| `UserStageHistory` model | — | User::stageHistory(), AdminUsersController | Medium |
| `CustomerHealthService` | 384 | **End-user Dashboard**, AdminDashboardController, LeadScoringService, AdminHealthAlertCommand | **High** — removal breaks the user-facing dashboard |
| `EngagementScoringService` | 196 | AdminUsersController only | Low |
| `LeadScoringService` | — | `users:qualify-leads` command, CustomerHealthService | Low |
| `CohortService` | — | Admin screens | Low |
| `ProductAnalyticsService` | — | AdminProductAnalyticsController | Low |
| `AnalyticsGateway` | 148 | A thin wrapper that fans out to AuditService + optional external sink | Low |
| `DispatchAnalyticsEvent` job | — | StripeWebhookController fires 7 event types through this | Medium |
| `CaptureUtmParameters` middleware | — | Web middleware + RegisteredUserController/SocialAuthController read the captured cookies | Medium |
| `analytics-thresholds.php` config | — | AdminBillingController, scoring services | Low |
| `EmailSendLog` model | — | **All lifecycle emails AND billing dunning** (dedup) | **High** — shared with billing |
| `NpsResponse` model + controller/admin | — | User-submitted NPS, admin inbox | Low |
| `Feedback` model + `FeedbackController` + admin | — | User-submitted bug feedback, admin inbox | Low |
| 7 non-welcome lifecycle commands (`send-*`) | — | Scheduled in routes/console.php | Low, well-isolated |

### Scope options

#### Option A — Aggressive (matches original recommendation literally)

Delete every service/enum/model/middleware on the list above. Rewrite callers:
- Inline `DashboardController` to compute a simple "has_subscription" and recent activity without `CustomerHealthService`.
- Strip the lifecycle-stage block from `AdminDashboardController`.
- Strip `UserStageHistory` block from `AdminUsersController`.
- Delete `LifecycleService::transition()` calls from `RegisteredUserController`, `OnboardingController`, `StripeWebhookController`.
- Replace `AnalyticsEvent` enum usage across ~40 files with plain string constants or a much smaller enum (audit events only). **This is the big swing** — it's a 15-file rewrite.
- Delete scheduled lifecycle commands from `routes/console.php`.
- Drop migrations: `feedback_submissions`, `nps_responses`, `add_lifecycle_stage_to_users`, `user_stage_history`, `add_score_columns_to_users`, `add_lead_score_to_users`, `email_send_log` (careful: `email_send_log` is also used by billing dunning — need to either keep or replace billing dedup with something else).
- Break or delete ~40 tests.

**Effort:** 1–2 days.
**Risk:** Breaks registration/onboarding/Stripe-payment-fail flows on day 1; must be replaced with stubs first.
**Outcome:** Closest to original recommendation. Starter loses ~2,000 LOC and gains simplicity at the cost of churn.

#### Option B — Moderate (recommended)

Keep the state machine, delete the marketing-analytics platform:

**Keep (rename where misleading):**
- `AuditService`, `AuditLog` model — untouched.
- Rename `AnalyticsEvent` → `AuditEvent` enum. Drop the ~20 cases that are purely marketing-analytics (`ONBOARDING_*` beyond completed, `FEEDBACK_*`, `NPS_*`, `LIFECYCLE_*`, etc. — choose cases one by one). ~80 cases remain for audit.
- `LifecycleService` + `LifecycleStage` enum + `UserStageHistory` — **keep as a lightweight state machine**. Cheap, useful, already wired to signup/onboarding/Stripe.
- `EmailSendLog` — **keep** (billing dunning needs it).
- `NpsResponse`, `Feedback` — **keep** (user-initiated feedback; useful regardless of scoring stack).

**Delete:**
- `CustomerHealthService`, `EngagementScoringService`, `LeadScoringService`, `CohortService`, `ProductAnalyticsService`, `AnalyticsGateway`, `SessionDataMigrationService`.
- `DispatchAnalyticsEvent` job — replace call sites with direct `AuditService->log()` / `logProductEvent()`.
- `CaptureUtmParameters` middleware + all UTM attribution in registration and social-auth.
- `analytics-thresholds.php` config.
- All 7 non-welcome lifecycle email commands (`SendDunningReminders`, `SendReEngagementEmails`, `SendTrialNudges`, `SendWinBackEmails`, `SendOnboardingReminders`, `EnforceGracePeriod`, `CheckExpiredTrials`, `QualifyLeads`, `ComputeUserScores`) — plus their notifications and tests.
- `AdminProductAnalyticsController` + route + React page.
- Score columns on users: `add_score_columns_to_users`, `add_lead_score_to_users` migrations (drop columns in a second migration; don't delete the prior migration file).
- Scheduled entries in `routes/console.php` for deleted commands.
- Related tests: all under `tests/Unit/Services/*Scoring*`, `tests/Unit/Services/LifecycleServiceTest`, `tests/Feature/Commands/Send*`, `tests/Feature/Console/*`, `tests/Feature/Admin/AdminProductAnalyticsTest.php`, `tests/Feature/Middleware/CaptureUtmParametersTest.php`, related notification tests.

**Fixups (must not be skipped):**
- `DashboardController`: stop injecting `CustomerHealthService`; compute a simpler state inline. Keep the billing summary.
- `AdminDashboardController`: remove the health block and the `LifecycleStage::funnelOrder()` funnel chart; leave user/subscription/audit stats.
- `AdminUsersController`: remove engagement score column from index and detail view.
- `RegisteredUserController`: keep `LifecycleService::transition($user, TRIAL/VISITOR, 'registration')` (cheap, useful). Remove `SessionDataMigrationService` injection. Remove UTM persistence block.
- `SocialAuthController`: remove UTM persistence block.
- `StripeWebhookController`: keep `LifecycleService::transition($user, AT_RISK, 'invoice_payment_failed')` (useful). Replace all `DispatchAnalyticsEvent` dispatches with `$this->auditService->log(AuditEvent::X, [...])` calls. Delete the `dispatchWebhookAnalyticsEvent` helper.
- `OnboardingController`: keep the transition. `logProductEvent` may need to be inlined to AuditService.

**Effort:** half a day to a day.
**Risk:** Moderate. The enum rename is the biggest churn but it's mechanical (search/replace + PHPStan).
**Outcome:** Starter loses ~1,500 LOC and 25+ test files. Audit logging, billing, onboarding, and dashboard still work. The state machine survives. The "marketing analytics platform" is gone.

#### Option C — Minimal (just delete the email commands + product-analytics admin page)

Keep everything except:
- The 8 non-welcome lifecycle email commands (`SendDunningReminders`, `SendReEngagementEmails`, `SendTrialNudges`, `SendWinBackEmails`, `SendOnboardingReminders`, `EnforceGracePeriod`, `CheckExpiredTrials`, `QualifyLeads`).
- `ComputeUserScores` command.
- Their tests.
- The scheduled entries in `routes/console.php`.
- `AdminProductAnalyticsController` + route + React page + test.

**Effort:** ~1 hour.
**Risk:** Very low.
**Outcome:** Starter stops sending emails you don't have users for, removes one admin page. The underlying services stay, ready to use when you need them. This is "defer," not "cut."

### Recommendation

**Option B.** It matches the *spirit* of the original recommendation ("delete the marketing-analytics platform") without breaking flows that legitimately use the lifecycle-state concept. Option A's `AnalyticsEvent` → audit-event split is correct in principle but a multi-day project for a rename, and you'd end up with something Option B already gives you.

If you want to get Phase 4 out of the critical path entirely, do Option C now as a holding action, and revisit Option B in a dedicated sitting.

---

## Ordering and rollback

Recommended execution order:

1. **Phase 1 (SSR)** — smallest, safest, pure-delete. Independent.
2. **Phase 2 (Scribe)** — small, isolated, needs a `composer update`. Independent.
3. **Phase 3 (Impersonation)** — medium, touches layouts. Depends on AnalyticsEvent only for removing 2 cases; fine to run before Phase 4.
4. **Phase 4 (Lifecycle)** — largest. Only start after Phases 1–3 are green.

Each phase is one commit. If something breaks, `git revert` is the rollback — no migrations are destructive in Phases 1–3. Phase 4 Option A/B drops columns and tables; do those in a side branch first, test, then merge.

---

## What I need from you before I start

Confirm:

1. **Phase 4 scope:** A / B / C. (Default if you don't pick: C — minimal — as it's non-destructive and fast.)
2. **NPS + Feedback:** keep as user-initiated features (my assumption in Option B), or remove?
3. **Commit style:** one commit per phase (default), or squash Phases 1–3 into a single "sole-op trim" commit?
4. **Branch strategy:** work directly on current branch, or create `chore/sole-op-trim` and land via PR?
