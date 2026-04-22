# Code Review — Laravel React Starter (Solo-Operator Lens)

**Scope**: Hot-spot audit, maintainability & architecture focus.
**Date**: 2026-04-22
**Hot spots reviewed**: BillingService + billing controllers, webhook subsystem (incoming + outgoing), admin panel + authorization, auth flows (Breeze + 2FA + social), cross-cutting architecture (feature flags, scoring services, frontend structure).

---

## Bottom line

This starter is **well-engineered but over-scoped for a solo operator**. The core (auth, billing, Cashier integration, audit logging, cache enum) is solid. But surface area has grown past the point where one person can keep it all in their head: 20 services, 26 admin controllers, 11 feature flags, three overlapping scoring services, and a 571-line `SubscriptionController`. Most findings below are "this will bite you in six months when you try to refactor" rather than "this is broken now."

The two things that would actually hurt you operationally are both in billing: one real rule violation (retention-coupon bypass, see C1) and plan-tier magic strings scattered across 19 call sites (H3). Fix those first.

Verdict: **Approve with changes** — ship on this foundation, but work through the Critical and High items before you start modifying billing or adding new webhook providers.

---

## Verified findings

All line numbers below were confirmed against the actual source, not inferred.

### Critical

#### C1 — Retention coupon bypasses `BillingService` (rule violation + race risk)
- **File**: `app/Http/Controllers/Billing/SubscriptionController.php:496`
- **Symptom**: `$subscription->applyCoupon($couponId)` is called directly on a Cashier `Subscription` from inside the controller. No Redis lock, no eager load of `owner` / `items.subscription`. Your own `.claude/rules/billing.md` is explicit: *"All subscription mutations MUST use `BillingService` methods — direct Cashier calls will cause race conditions."*
- **Why it matters**: Under concurrent retention-save flows (user clicks "apply discount" and a Stripe webhook hits `subscription.updated` at the same moment) you can corrupt subscription state, and if `owner` isn't loaded you get the documented `Attempt to read property "stripe_id" on null` error.
- **Fix**: Add `BillingService::applyRetentionCoupon(User $user, string $couponId): void` that wraps the call in `withLock()` and eager-loads the standard relations. Replace the controller call. Add a PHPStan or architectural test that forbids calling Cashier subscription mutation methods outside `BillingService`.

#### C2 — 18 agent/audit artifacts are tracked in git
- **Files** (tracked, not just ignored): `BILLING_REVIEWED_*.md`, `audit-admin-results_*.json`, `audit-analytics-results_*.json`, `audit-full-results_*.json`, `audit-growth-results_*.json`, `audit-gtm-results_*.json`, `audit-launch-results_*.json`, `audit-messaging-results_*.json`, `audit-sales-pricing-results_*.json`, `audit-seo-results_*.json`. Plus 200+ untracked siblings (`AGENT_REVIEW_*.md`, `IMPLEMENTATION_REPORT_*.md`, `PRE_FLIGHT_REPORT_*.md`, `VERIFY_DONE_REPORT_*.md`) cluttering the working tree.
- **Symptom**: Every `git status`, every IDE file tree, every `grep` against the repo is noisy. For a solo operator this is the single highest-friction issue — you'll lose minutes a day forever.
- **Fix**: `git rm --cached <file>` for the 18 tracked files, commit. The `.gitignore` already has the patterns (added after the files were committed), so new artifacts will be ignored. Then `rm` the 200+ untracked artifacts from the working tree, or move them to an `_archive/` directory that's also ignored.

### High

#### H1 — `SubscriptionController` is 571 lines doing five jobs
- **File**: `app/Http/Controllers/Billing/SubscriptionController.php`
- **Symptom**: Checkout, subscription lifecycle (cancel/resume/swap), payment method updates, coupon/retention logic, and portal redirection live in one file. Eager-loading boilerplate (`$user->loadMissing('subscriptions.items')`) is copy-pasted at the top of ~10 methods.
- **Fix**: Split into `SubscriptionCheckoutController`, `SubscriptionLifecycleController`, `PaymentMethodController`, `RetentionController`. Move the `loadMissing` pattern into a `WithBillingContext` trait or a route middleware. Aim for <250 lines per controller.

#### H2 — `StripeWebhookController` is a 321-line god object
- **File**: `app/Http/Controllers/Billing/StripeWebhookController.php`
- **Symptom**: 8 hardcoded `handleCustomerSubscription*` / `handleInvoice*` methods each mixing DB writes, notifications, analytics dispatches, and cache invalidation. Adding a new event type means another 40-line method here.
- **Fix**: Dispatcher + per-event handler classes (`app/Webhooks/Stripe/SubscriptionCreatedHandler.php` etc.). Register them in a `StripeEventMap` array. The controller becomes ~30 lines: verify signature (Cashier does this), look up handler, dispatch. Each handler is independently testable and the map is self-documenting.

#### H3 — Plan tiers are magic strings, not an enum
- **Files**: `app/Services/BillingService.php`, `app/Services/PlanLimitService.php`, `app/Services/AdminBillingStatsService.php`, controllers — `'free'` / `'pro'` / `'team'` / `'enterprise'` appear as string literals across ~19 sites.
- **Symptom**: No compiler help when you rename or add a tier. Typos silently degrade to "free". The billing rules file mentions tiers but nothing prevents drift.
- **Fix**: `App\Enums\PlanTier` enum with cases and a `fromStripePriceId(string): self` factory. Replace all magic strings. PlanLimitService methods accept `PlanTier`, not `string`. This also gives you a natural home for `tier->canUpgradeTo()` that's currently implicit.

#### H4 — No webhook provider abstraction
- **Files**: `app/Http/Middleware/VerifyWebhookSignature.php`, `app/Http/Controllers/Webhook/IncomingWebhookController.php`, `config/webhooks.php`
- **Symptom**: Adding a provider requires touching a `match()` in the middleware, a `match()` in the controller, and a new config entry. Signature extraction and event parsing are hardcoded per provider. Stripe uses a completely separate code path (`StripeWebhookController` via Cashier) so any shared patterns diverge.
- **Fix**: `WebhookProvider` interface with `verify(Request $request): bool` and `parseEvent(array $payload): IncomingEvent`. Each provider (`GithubProvider`, `CustomProvider`) is a class registered in `config/webhooks.php`. Middleware resolves the provider by route parameter, calls `verify()`. Doing this now costs a day; doing it after the third provider costs a week.

#### H5 — Three overlapping scoring services with no shared base
- **Files**: `app/Services/EngagementScoringService.php` (196 lines), `app/Services/CustomerHealthService.php` (384 lines), `app/Services/LeadScoringService.php` (59 lines — thin combiner of the other two)
- **Symptom**: Each service independently queries login recency, feature adoption, webhook-endpoint counts. `LeadScoringService` exists only because the other two don't share a proper interface. Batch methods require external callers to remember priming order or you silently get N+1.
- **Fix**: Extract `UserActivityMetrics` value object + `ScoreCalculator` base. `EngagementScoringService` and `CustomerHealthService` inherit/compose. Delete `LeadScoringService` or reduce it to a 10-line composition. Add a `ScoringPipeline` helper so batch use cases don't have to remember `prime*` calls.

#### H6 — Admin pagination/filter/sort logic duplicated across 7 controllers
- **Files** (all implement allowed-sorts arrays + direction validation + per-page lookup): `AdminSessionsController`, `AdminUsersController`, `AdminAuditLogController`, `AdminFeedbackController`, `AdminContactSubmissionsController`, `AdminEmailSendLogController`, `AdminNpsResponsesController`
- **Symptom**: Same 15–25 lines of boilerplate in each. Drift already: `AdminFeedbackController:31-36` validates sort inline; others use Form Requests.
- **Fix**: `AdminListQuery` trait with `applyListParams(Builder $q, array $allowedSorts, string $default): LengthAwarePaginator`. Standardize on Form Request `->in([...])` for the allowed-sorts list so validation lives in one place.

#### H7 — `FeatureFlagService` at 496 lines violates SRP
- **File**: `app/Services/FeatureFlagService.php`
- **Symptom**: One class resolves route dependencies, global overrides, per-user overrides, caching, validation, and DB lookups. Setting up a unit test requires cache + DB mocks.
- **Fix**: Split into `FeatureFlagResolver` (thin orchestrator, public API), `FeatureFlagOverrideStore` (DB + cache), `FeatureFlagValidator` (naming / constant validation). Each is <150 lines and independently testable.

### Medium

#### M1 — `users.update` is admin-only, but `toggleAdmin` / `toggleActive` are super_admin-only
- **File**: `routes/admin.php:58` (update) vs `:62-67` (toggles)
- **Symptom**: A regular admin can edit another admin's name/email/timezone, but can't toggle their admin status. Inconsistent without comment. May be intentional, but it's the kind of thing you'll forget why in a year.
- **Fix**: Either promote `users.update` to `super_admin`, or add a code comment + ADR explaining the delegation model. Document in `routes/admin.php` at the top of the users group.

#### M2 — Admin-count race window in `toggleAdmin`
- **File**: `app/Http/Controllers/Admin/AdminUsersController.php:204`
- **Symptom**: `User::where('is_admin', true)->whereNull('deleted_at')->count() <= 2` is not inside a transaction with a row lock. With three admins and two simultaneous "remove admin" requests against two different users, both checks pass and you end up with one admin. Low-probability solo-operator edge case, but the fix is cheap.
- **Fix**: Wrap in `DB::transaction()` + `->lockForUpdate()` on the count query. Or just hard-floor at a minimum via a unique constraint-ish check (e.g., model observer).

#### M3 — 2FA challenge flow leaks `login.id` on abandonment
- **File**: `app/Http/Controllers/Auth/AuthenticatedSessionController.php:45-56` and `TwoFactorChallengeController`
- **Symptom**: When 2FA is required, user is logged out and `login.id` / `login.remember` are written to session. If the user closes the browser before completing 2FA, those keys persist until session GC.
- **Fix**: Add TTL on the 2FA-pending session keys (15 min), cleared on `TwoFactorChallengeController::store()` success or explicit abandon. The current behavior is not insecure (they're still gated by `Auth::loginUsingId` validity), but it's untidy state you'll trip over debugging auth issues.

#### M4 — 2FA can be enrolled/used with unverified email
- **File**: `app/Http/Controllers/Auth/TwoFactorChallengeController.php`
- **Symptom**: The 2FA challenge doesn't check `email_verified_at`. User with unverified email can enroll and use 2FA.
- **Fix**: Add a `verified` middleware to the 2FA *management* routes (not the challenge itself). Or reject enrollment in the enrollment controller if `! $user->hasVerifiedEmail()`. The challenge endpoint itself needs no change.

#### M5 — Two near-identical webhook prune commands
- **Files**: `app/Console/Commands/PruneOldWebhookDeliveries.php`, `PruneStaleWebhookDeliveries.php`
- **Symptom**: One marks pending-as-abandoned, the other deletes terminal rows. No shared helpers. Naming doesn't tell you which does which.
- **Fix**: Single `webhooks:prune` command with `--mark-stale-after=1h` and `--delete-older-than=90d` flags, or keep two but rename to `webhooks:mark-abandoned` and `webhooks:delete-old` so intent is obvious.

#### M6 — Cache invalidation is manual and scattered
- **Files**: `app/Services/BillingService.php`, `AdminBillingStatsService.php`, admin controllers
- **Symptom**: `Cache::forget(AdminCacheKey::X->value)` calls sprinkled across call sites. Easy to miss one. Your own CLAUDE.md calls this out as a "known bug class."
- **Fix**: `AdminCacheInvalidator` service with semantic methods (`onSubscriptionChanged()`, `onUserCountChanged()`). Inject into `BillingService` etc. — mutations trigger invalidation automatically. Consider Laravel cache tags once you're on Redis in prod (you are).

#### M7 — `PlanLimitService` calls `session()->flash()` from a service
- **File**: `app/Services/PlanLimitService.php` (~line 141)
- **Symptom**: A service reaching into session state means it fails silently when called from a queue job or console command. Also makes it untestable without an HTTP context.
- **Fix**: Return a structured `UpgradeRequired` DTO / result object. Let controllers flash the message. Services shouldn't know about sessions.

#### M8 — Frontend `resources/js/Pages/` mixes marketing and app pages
- **Files**: `resources/js/Pages/{Compare,Guides,Blog,Roadmap,Changelog,Features,About,Contact}/*` alongside `Pages/{Admin,Settings,Dashboard,Auth,Billing}/*`
- **Symptom**: 40+ pages at one level with no separation of marketing vs authenticated-app surface. IDE navigation and "find all usages" queries get noisy.
- **Fix**: `Pages/Public/*` for marketing, `Pages/App/*` for authenticated. Zero logic change — just moves and an Inertia `component` prefix adjustment.

### Low

#### L1 — No resource/presenter layer for admin Inertia responses
- **File**: `app/Http/Controllers/Admin/AdminUsersController.php:89-100` (and peers)
- **Symptom**: User-shape transformations (dates, counts, computed fields) duplicated across `index`, `show`, related admin pages.
- **Fix**: `AdminUserResource` class (JsonResource or plain array DTO). One place to add a new field, no drift.

#### L2 — `routes/web.php` mixes runtime and boot-time feature gating
- **File**: `routes/web.php` (236 lines)
- **Symptom**: Some routes are wrapped in `if (config('features.X.enabled'))` at boot; others register always and check inside the controller. Hard to predict which routes 404 vs return a `FeatureDisabledException`.
- **Fix**: Pick one. Your own `testing.md` notes that boot-time gating is harder to test. Prefer runtime gating via a `feature:X` middleware.

#### L3 — `IncomingWebhookController` stores events but doesn't dispatch handlers
- **File**: `app/Http/Controllers/Webhook/IncomingWebhookController.php` + `app/Services/IncomingWebhookService.php` (49 lines)
- **Symptom**: Generic (non-Stripe) webhook path stores the payload and returns 200 with no handler dispatch. Either unfinished or intentionally audit-only — unclear.
- **Fix**: Dispatch an event (`IncomingWebhookReceived`) after store, or document in the rules file that this path is audit-only by design. Don't leave it ambiguous.

#### L4 — `routes/admin.php` is 350 lines in one file
- **File**: `routes/admin.php` (18KB, 350 lines)
- **Symptom**: All 26 admin controllers' routes in a single file with nested `->group()` calls. Readable today, hard to navigate in a year.
- **Fix**: Split by domain: `routes/admin/users.php`, `routes/admin/billing.php`, `routes/admin/webhooks.php`, etc. Load from `routes/admin.php`. Same group protection, cleaner mental map.

---

## What looks genuinely good — don't refactor these

- **`AuditService` + `AnalyticsEvent` enum** — Clean separation of concerns, good naming, used consistently.
- **`AdminCacheKey` enum + `CacheInvalidationManager` approach** — The enum is right; the inconsistency is only in how invalidation is *called*.
- **`EnsureIsAdmin` / `EnsureIsSuperAdmin` two-tier middleware** — Right abstraction.
- **Rule files in `.claude/rules/`** — These are the kind of guardrails that pay back 10x. Keep adding them; just make sure code matches them (see C1).
- **Test discipline** — 37K LOC tests to 20K LOC app is a healthy ratio. Pest + Vitest + Playwright three-tier is well-chosen.
- **Feature flag concept** (not the 496-line implementation) — Right instinct for a starter; execution just needs splitting.
- **Cashier eager-loading pattern documented in `billing.md`** — The rule itself is right, one call site just doesn't follow it (C1).

---

## Suggested sequence

Work through in this order. Each bucket is one "sitting." Do not combine billing refactors with webhook refactors — keep blast radius small.

**Sitting 1 (30 min) — hygiene**
- C2: `git rm --cached` the 18 tracked artifacts. `rm` the 200+ untracked. Commit.

**Sitting 2 (2–4 hrs) — billing correctness**
- C1: Move retention coupon into `BillingService`. Add architectural test.
- H3: Introduce `PlanTier` enum. Replace magic strings.

**Sitting 3 (1 day) — controller slimming**
- H1: Split `SubscriptionController` four ways.
- H6: `AdminListQuery` trait. Delete duplicated sort/filter boilerplate.

**Sitting 4 (1 day) — webhook architecture**
- H2: Stripe dispatcher + handlers pattern.
- H4: `WebhookProvider` interface.
- L3 + M5: Clarify/consolidate pruning and dispatch.

**Sitting 5 (half day) — service layer**
- H5: Scoring service base class.
- H7: Split `FeatureFlagService`.
- M7: Remove `session()->flash()` from service.

Everything else (Medium/Low) is background maintenance — tackle as you touch those files.

---

## Methodology notes

- All Critical and High findings had their file paths and line numbers verified against the source, not inferred from agent summaries.
- Four parallel agents covered billing, webhooks, admin+auth, and cross-cutting architecture respectively.
- Focus was explicitly maintainability/architecture per your answer to the scope question. Security, performance, and accessibility were only flagged when they intersected with a maintainability issue.
- I did not run tests or PHPStan. All findings are from static reading. Before making changes, run your quality gate (`bash scripts/test-quality-check.sh`) to establish a clean baseline.
