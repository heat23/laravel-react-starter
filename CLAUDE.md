# Laravel React Starter Template

**Stack:** Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4

**This is a production-ready starter template, not scaffolding.** Every feature is a complete, tested implementation.

## AI Development Safeguards

**Workflow docs** (follow for all tasks):
- [docs/PLANNING_CHECKLIST.md](docs/PLANNING_CHECKLIST.md) — before writing code
- [docs/IMPLEMENTATION_GUARDRAILS.md](docs/IMPLEMENTATION_GUARDRAILS.md) — during implementation (TDD, run tests after each change, PHPStan after PHP changes)
- [docs/TESTING_GUIDELINES.md](docs/TESTING_GUIDELINES.md) — test standards, verification, and edge case checklist
- [docs/DEBUGGING_GUIDE.md](docs/DEBUGGING_GUIDE.md) — test failure diagnosis protocol
- [docs/AI_PROMPT_TEMPLATES.md](docs/AI_PROMPT_TEMPLATES.md) — structured request templates

**Reference docs:** [docs/FEATURE_FLAGS.md](docs/FEATURE_FLAGS.md) (dependency graph, gating patterns)

**Project-specific rules (beyond global CLAUDE.md):**
- Contract tests in `tests/Contracts/` — do NOT modify without user approval
- Full quality gate: `bash scripts/test-quality-check.sh` (or manually: `php artisan test --parallel && npm test && vendor/bin/phpstan analyse && vendor/bin/pint --test && npm run lint && npm run build`)
- Defense layers: pre-commit hooks (`.husky/pre-commit`), CI gates (`.github/workflows/ci.yml`), mutation testing (`infection`), ADRs (`docs/adr/`)

## Customization via Feature Flags

Configure your app by toggling features in `config/features.php` (or `.env`). 11 feature flags control major subsystems:

**Common configurations:**
- **SaaS with billing:** Enable `billing`, `webhooks`, `two_factor`, `api_tokens`
- **Internal tool:** Enable `two_factor`, `api_tokens`, `notifications`; disable `billing`, `social_auth`
- **Simple MVP:** Enable only `email_verification`, `user_settings`; disable premium features

**Safe to toggle:** Feature-gated routes don't register when disabled. Database tables remain but stay empty. UI elements conditionally render.

## Feature Flags

Check `config/features.php` and `.env` before implementing. Features default off unless noted:

| Flag | Env Var | What it enables |
|------|---------|-----------------|
| `billing.enabled` | `FEATURE_BILLING` | Stripe Cashier, pricing page, billing portal |
| `social_auth.enabled` | `FEATURE_SOCIAL_AUTH` | Google/GitHub OAuth (auto-detected by client ID presence) |
| `email_verification.enabled` | `FEATURE_EMAIL_VERIFICATION` | Email verification flow (default: true) |
| `api_tokens.enabled` | `FEATURE_API_TOKENS` | Sanctum token management UI (default: true) |
| `user_settings.enabled` | `FEATURE_USER_SETTINGS` | Theme/timezone persistence (default: true) |
| `notifications.enabled` | `FEATURE_NOTIFICATIONS` | In-app notification system |
| `onboarding.enabled` | `FEATURE_ONBOARDING` | Welcome wizard for new users |
| `api_docs.enabled` | `FEATURE_API_DOCS` | Scribe interactive API docs |
| `two_factor.enabled` | `FEATURE_TWO_FACTOR` | TOTP 2FA authentication |
| `webhooks.enabled` | `FEATURE_WEBHOOKS` | Incoming/outgoing webhooks |
| `admin.enabled` | `FEATURE_ADMIN` | Admin panel: user management, health monitoring, audit logs, config viewer, system info |

**Disabling features:** Set env var to `false`. Feature-gated routes won't register, middleware won't apply, UI elements won't render. Database tables remain (safe to leave empty).

See [docs/FEATURE_FLAGS.md](docs/FEATURE_FLAGS.md) for dependency graph, testing patterns, gating approaches, and adding new flags.

## Environments

| Env | APP_ENV | Mail | Analytics | Sentry |
|-----|---------|------|-----------|--------|
| Local | `local` | Mailpit (:1025, view at :8025) | Disabled | Disabled |
| Preview | `preview` | SMTP | Optional | Enabled |
| Production | `production` | SMTP | `GOOGLE_ANALYTICS_ID` | `SENTRY_LARAVEL_DSN` |

## Architecture

**Models:** User, UserSetting (key-value), SocialAccount (OAuth), AuditLog, FeatureFlagOverride (flag overrides with reason/changed_by), WebhookEndpoint, WebhookDelivery, IncomingWebhook

**Vendor Models (not in `app/Models/`):** TwoFactorAuthentication (`Laragear\TwoFactor\Models\TwoFactorAuthentication` — referenced via User trait, do not create a local model)

**Services:**
- `AuditService` — activity logging
- `BillingService` — Redis-locked Stripe subscription mutations (CRITICAL: see Gotchas)
- `PlanLimitService` — enforce subscription limits (projects, items, tokens)
- `SessionDataMigrationService` — migrate guest session data on login
- `SocialAuthService` — OAuth provider abstraction
- `WebhookService` — outgoing webhook dispatch with HMAC signing
- `IncomingWebhookService` — process/validate incoming webhooks
- `AdminBillingStatsService` — admin billing dashboard stats/charts
- `FeatureFlagService` — flag resolution with DB overrides (per-user > global > config)
- `HealthCheckService` — health checks (DB/cache/queue/disk)
- `CacheInvalidationManager` — centralized admin cache invalidation (billing, tokens, webhooks, 2FA, social auth, per-user)

**Notifications:** `PaymentFailedNotification`, `IncompletePaymentReminder`, `RefundProcessedNotification`

**Listeners:** `SendEmailVerificationNotification` (queued, overrides framework default)

**Helpers:** `features.php` (`feature_enabled()` helper), `QueryHelper` (query scoping utilities)

**Support:** `CsvExport` (CSV generation for admin data export)

**Providers:** `AppServiceProvider` (rate limits, model config, policy registration), `EventServiceProvider` (email verification listener binding)

**Billing (Production-Grade):**
- `BillingService` — Redis-locked subscription mutations (create, cancel, resume, swap)
  - **CRITICAL:** Uses Redis locks (35s timeout) to prevent concurrent Stripe API calls
  - **CRITICAL:** Requires eager loading before Cashier methods (see Critical Gotchas > Billing)
  - All operations wrapped in DB transactions for atomicity
- Plan tiers: free, pro, team (3-50 seats), enterprise (custom)
- Incomplete payment tracking: `subscriptions:check-incomplete` command sends reminders at 1h/12h

**Webhooks:**
- `WebhookService` — Outgoing webhooks with HMAC-SHA256 signing, async dispatch via `DispatchWebhookJob`
- `IncomingWebhookService` — Process GitHub/Stripe webhooks with signature verification via `VerifyWebhookSignature` middleware
- Models: `WebhookEndpoint`, `WebhookDelivery`, `IncomingWebhook`

**Two-Factor Authentication:**
- Via `laragear/two-factor` package (TOTP + recovery codes)
- `TwoFactorChallengeController` handles verification
- Feature-gated via `two_factor.enabled`

**Tenancy:** Single-tenant. Do not add account/org/workspace scoping unless explicitly requested.

**Routes:**
- `routes/web.php` — pages (feature-gated with `if (config('features.*.enabled'))`)
- `routes/admin.php` — admin panel (loaded from web.php when `admin.enabled`), middleware: `['auth', 'verified', 'admin', 'throttle:60,1']`
- `routes/auth.php` — auth (Breeze + social auth + email verification)
- `routes/api.php` — Sanctum-protected API (user, settings, tokens)
- Health check: `/up` (Laravel built-in, configured in `bootstrap/app.php`) + `/health` (custom `HealthCheckController` with token/IP/local auth)

**Config files of note:**
- `config/features.php` — feature flags with env var defaults
- `config/plans.php` — plan tier definitions (free, pro, team, enterprise)
- `config/pagination.php` — centralized pagination sizes (admin, API, billing, export — never hardcode page sizes)
- `config/security.php` — CSP and security header configuration
- `config/webhooks.php` — webhook provider secrets
- `config/health.php` — health check auth modes (token, IP allowlist, local-only)

## Decision-Making Frameworks

### When to Create a Service Class

Create a dedicated Service when **ANY** of these conditions apply:
1. Logic involves external API calls (Stripe, GitHub, etc.)
2. Logic requires distributed locking (Redis locks)
3. Logic wraps database transactions across multiple models
4. Logic is reusable across 2+ controllers/jobs/commands
5. Logic involves complex state machines or multi-step processes
6. Logic requires extensive mocking/stubbing in tests

Keep logic in Controller when **ALL** of these are true:
- Single model CRUD operations
- No external dependencies
- Validation handled by Form Request
- No transaction coordination needed
- Less than 30 lines of business logic

**Examples from this codebase:**
- ✅ Service: `BillingService` (Redis locks + Stripe API + transactions)
- ✅ Service: `WebhookService` (external HTTP calls + HMAC signing)
- ✅ Controller: `OnboardingController` (single setting update)
- ⚠️  Gray area: `ProfileController` (consider service if adding photo upload to S3)

### When to Create a Policy vs Manual Auth Checks

**Rule:** Always use policies for resource authorization (`$this->authorize('delete', $project)`).

**When NOT to use policies:**
- Feature flag checks → `abort_unless(feature_enabled('X'))` in controller
- Role-based route protection → middleware: `->middleware('admin')`
- Global permissions not tied to a resource → `Gate::define` in AuthServiceProvider

### When to Create a Job vs Execute Synchronously

Create a Job when:
- External API call that can be retried (Stripe, email sending)
- Long-running operation (>5 seconds)
- Rate-limited operation that needs queuing
- Operation that should survive request timeout

Execute synchronously when:
- User needs immediate feedback (form submission response)
- Operation is fast (<1 second)
- Failure requires user action (payment declined)

**Existing Jobs** (flat in `app/Jobs/`): `PersistAuditLog`, `CancelOrphanedStripeSubscription`, `DispatchWebhookJob`

## Performance Budgets

### Query Count Limits (enforce in tests)

**Per-request budgets:**
- Dashboard page: ≤5 queries (user + settings + cached stats)
- User index (admin): ≤3 queries per page (users + pagination count + audit log latest)
- Detail pages with relationships: ≤8 queries (model + 3 relationships + cache checks)
- API endpoints: ≤4 queries (auth + main query + optional related)

**Eager loading:** Always eager load before accessing relationships in loops or Inertia props. Verify with `DB::enableQueryLog()` + query count assertions in tests. For Cashier-specific rules, see Critical Gotchas > Billing.

### Cache Strategy

**When to cache (all cached with AdminCacheKey enum):**
- Admin dashboard stats (5min TTL)
- Billing tier distribution (5min TTL)
- Chart data that aggregates historical records (1hr TTL)
- Feature flag global overrides (5min TTL, flushed on change)

**When NOT to cache:**
- User-specific current state (subscription status, unread count)
- Data that changes on every request (audit logs, real-time notifications)
- Small lookup tables that fit in opcache (< 100 rows)

**Cache invalidation checklist** (when adding mutations):
- If mutation affects admin dashboard counts → `Cache::forget(AdminCacheKey::DASHBOARD_STATS->value)`
- If mutation affects billing stats → invalidate `BILLING_STATS` + `BILLING_TIER_DIST`
- If mutation affects webhooks/tokens/2FA stats → invalidate respective enum key
- If global feature flag override changes → `AdminCacheKey::flushAll()`

## How to Add a New Feature

1. **Migration:** `php artisan make:migration create_{table}_table` — follow existing migration patterns in `database/migrations/`
2. **Model:** `php artisan make:model {Name}` — add to User relationship if user-owned
3. **Factory:** Create in `database/factories/` — required for any new model to keep tests easy
4. **Form Request:** Create in `app/Http/Requests/` — always implement `authorize()` and `rules()`
5. **Controller:** Create in `app/Http/Controllers/` — use constructor injection, Form Requests, policy auth
6. **Policy (if user-owned):** Create in `app/Policies/` — register in `AppServiceProvider` if needed. Only `UserPolicy` exists currently.
7. **Page:** Create in `resources/js/Pages/` — use `usePage()` for shared props, `useForm()` for forms
8. **Route:** Add to `routes/web.php` — wrap in feature flag if optional, always name routes. Add API route to `routes/api.php` if applicable.
9. **Feature flag (if optional):** Add to `config/features.php` with env var default
10. **Tests:** Write Pest tests in `tests/Feature/` and `tests/Unit/` — see `tests/` structure
11. **Nav links:** Update navigation components to show new feature (gate with `{features.X && ...}` if feature-flagged)
12. **TypeScript types:** Add Inertia page props type definitions in `resources/js/types/`
13. **Review checklist (run mentally before claiming done):**
    - **Query count budget:** Does page meet query count budget? Add `DB::enableQueryLog()` test.
    - **Accessibility:** Can you complete the flow with keyboard only? (no mouse)
    - **Soft-delete sweep:** Does any code access `->user->`, `->owner->`, or other relationships without `?->` where the related model uses `SoftDeletes`? Add `withTrashed()` to admin-facing queries.
    - **Middleware audit:** If a route is outside its normal middleware group, does it have the right (and ONLY the right) middleware? Especially: don't put `verified` on routes that unverified users need.
    - **Cache invalidation:** If the feature mutates data that feeds an admin dashboard or cached stats, does it call `Cache::forget()` on the relevant `AdminCacheKey`?
    - **Async contract:** If a function is passed as `onConfirm` to a dialog or awaited anywhere, does it return a `Promise` that resolves after the server responds (not after the fire-and-forget call)?
    - **Nav/URL prefix collisions:** If adding a new nav item or route, does `startsWith` matching cause false positives with parent routes?
    - **Local state vs URL params:** If a component uses both `useState` and URL-based filters, does `clearFilters` reset ALL local state?

## Error Recovery & Test Quality

Test failures? Follow the diagnosis protocol in [docs/DEBUGGING_GUIDE.md](docs/DEBUGGING_GUIDE.md).

Edge case coverage checklist (soft-deleted relationships, null relationships, unverified users, feature disabled, concurrent operations, empty collections, pagination, authorization): see [docs/TESTING_GUIDELINES.md](docs/TESTING_GUIDELINES.md).

## Commands

```bash
composer dev           # Start Laravel + Vite + Queue
php artisan serve      # Laravel only
npm run dev            # Vite dev server
php artisan test       # Run tests (Pest, parallel)
npm test               # Vitest frontend tests
npm run test:e2e       # Playwright E2E tests
npm run build          # Production build
npm run lint           # ESLint
composer audit         # Security audit (fails on vulnerabilities)
npm audit              # JS vulnerabilities (reports but doesn't block)
php artisan subscriptions:check-incomplete  # Find failed payments, send reminders
php artisan audit:prune                     # Delete old audit logs (--days=N)
php artisan webhooks:prune-stale            # Mark orphaned webhook deliveries as abandoned (--hours=N)
scripts/init.sh        # First-time setup (configure project name, features)
```

## Conventions

**Backend:**
- Form Requests for validation (never inline `$request->validate()`)
- Services for business logic, controllers stay thin
- External API calls in Jobs only (`app/Jobs/` — 3 jobs exist)
- Constructor injection for dependencies
- Custom exceptions in `app/Exceptions/`: `ConcurrentOperationException` (billing lock failures), `SubscriptionException` (billing error responses)

**Frontend:**
- UI primitives in `Components/ui/` (Radix + CVA + `cn()` from `lib/utils`)
- Theme via CSS variables (semantic tokens like `bg-background`, `text-foreground`)
- Forms: Inertia `useForm()` hook
- Icons: Lucide React only
- Shared props via `usePage()`, feature-gated UI via `features` prop
- Custom hooks in `hooks/`: `useMobile`, `useFormValidation`, `useTimezone`, `useUnsavedChanges`, `useAdminAction`, `useAdminFilters`, `useAdminKeyboardShortcuts`, `useFlashToasts`, `useNavigationState`
- Shared Inertia props must stay minimal (auth summary + feature flags + flash). Never send whole Eloquent models — use explicit arrays.

**Frontend State Management:**

*Decision Tree: Where to Store State*

- **URL Params** (shareable/bookmarkable): Pagination (`?page=2`), filters (`?status=active`), search (`?q=john`), sort order
- **React useState** (ephemeral UI): Dialog open/closed, form validation errors (before submission), hover/focus state
- **Inertia Props** (server-driven): Current user auth, feature flags, flash messages, paginated data
- **localStorage** (UI preferences NOT in user_settings): Sidebar collapsed, table column widths, last visited tab
- **user_settings table** (sync across devices): Theme (light/dark), timezone, notification preferences

*Rule:* Use a single source of truth for filters — all URL params or all useState, never mixed. `clearFilters` must reset ALL state.

*Inertia Router Fire-and-Forget Behavior (CRITICAL):*
`router.post()`, `router.patch()`, `router.delete()` return immediately, NOT a Promise.

```tsx
// ❌ Bad: Awaiting Inertia router calls
async function deleteUser(id: number) {
    setLoading(true);
    await router.delete(`/users/${id}`); // Returns immediately! await does nothing
    setLoading(false); // Executes before server response
}

// ✅ Good: Use onSuccess callback
function deleteUser(id: number) {
    setLoading(true);
    router.delete(`/users/${id}`, {
        onSuccess: () => setLoading(false),
        onError: () => setLoading(false),
    });
}

// ✅ Better: Use LoadingButton component
<LoadingButton onClick={() => router.delete(`/users/${id}`)}>
    Delete
</LoadingButton>
```

**Accessibility (WCAG 2.1 Level AA Required):**
- Keyboard-navigable: all interactive elements focusable via Tab, visible focus ring, dialogs trap focus, Esc to close
- Semantic HTML: `<button>` for actions, `<a>` for navigation, `<label>` for inputs, heading hierarchy
- ARIA: `aria-label` on icon-only buttons, `aria-describedby` for errors, `aria-live="polite"` for toasts
- Contrast: ≥4.5:1 normal text, ≥3:1 large text/interactive elements, never color-alone
- Verify: complete flow with keyboard only, all images have alt text, loading states announced
- Existing accessible components: `Button`, `Dialog`, `Toast`, `LoadingButton` (Radix-based)

**Testing:**
- Framework: Pest (not PHPUnit) — use `it()` / `test()` syntax
- Parallel execution: `php artisan test --parallel`
- Frontend: Vitest + @testing-library/react (`npm test`)
- Database: SQLite in-memory for tests
- All auth pages have `.test.tsx` counterparts
- E2E: Playwright (`tests/e2e/`) — auth smoke tests
- **Test quality rules (IMPORTANT):**
  - Assert user-visible behavior, not implementation details — check redirect destinations, session flash content, and final DB state, not just that a mock was called
  - Every test comment must be accurate — if a comment says "route doesn't have X", verify it. Wrong comments hide bugs.
  - Inertia router calls (`router.patch`, `router.post`) are fire-and-forget — when testing hooks/components that wrap them, mock with `onSuccess` callback invocation to simulate real async behavior
  - For every mutation test, verify both the success path AND the final state (e.g., `$user->fresh()->is_admin` after toggle)
  - Edge case coverage required: soft-deleted users, unverified users, null/missing relationships, concurrent operations
- **Boot-time route registration limitation:**
  - Routes conditionally registered at boot time (e.g., `if (config('features.billing.enabled'))` in route files) cannot be tested for both enabled/disabled states in the same test suite
  - Feature flags set in `phpunit.xml` determine which routes are registered at application boot
  - Tests can verify route behavior when enabled (route exists) OR when disabled (route returns 404), but not both
  - Example: BillingFeatureFlagTest is skipped because billing routes are enabled in phpunit.xml
  - Workaround: Test route-specific logic (controllers, middleware) in unit tests; only test route registration in integration tests matching the phpunit.xml config

**Migrations:** Follow global CLAUDE.md > Database Safety rules (nullable columns, constrained FKs, two-phase deploys, `Schema::hasColumn()` checks). Additionally:
- Feature-conditional migrations: only for whole-table creation (`Schema::hasTable` check). Never gate column additions/removals on feature flags — causes schema drift.

**Code Organization (File Placement):**

| Type | Location | Naming |
|------|----------|--------|
| Controllers | `app/Http/Controllers/{Domain}/` | CRUD: `{Resource}Controller`, Single-action: `{Verb}{Noun}Controller` |
| Models | `app/Models/` (flat) | `{Name}.php` |
| Services | `app/Services/` (flat) | `{Domain}Service.php` (never `UserService` — keep model logic in model) |
| Form Requests | `app/Http/Requests/{Domain}/` | `{Action}Request.php` (e.g., `AdminFeatureFlagRequest`) |
| Exceptions | `app/Exceptions/` | Domain-specific: `ConcurrentOperationException`, `SubscriptionException` |
| Middleware | `app/Http/Middleware/` | Descriptive: `EnsureOnboardingCompleted` not `CheckOnboarding` |
| Enums | `app/Enums/` | `{Name}.php` (e.g., `AdminCacheKey`) |
| Jobs | `app/Jobs/` (flat) | `{Name}.php` |
| Commands | `app/Console/Commands/` | Signature: `{domain}:{action}` |
| Policies | `app/Policies/` | `{Resource}Policy.php` (register in `AppServiceProvider`) |
| Pages | `resources/js/Pages/{Domain}/` | `{Name}.tsx` |
| Components | `resources/js/Components/` | Shared: `{name}.tsx`, UI primitives: `ui/{name}.tsx` |
| Notifications | `app/Notifications/` | `{Event}Notification.php` |
| Listeners | `app/Listeners/` | `{Action}.php` |
| Helpers | `app/Helpers/` | `{name}.php` (autoloaded via composer) |
| Support | `app/Support/` | `{Name}.php` (e.g., `CsvExport`) |
| Providers | `app/Providers/` | `{Name}ServiceProvider.php` |
| Tests | Mirror app structure | `tests/Feature/{Domain}/`, `tests/Unit/{Domain}/` |

Controller subdirectories: `Admin/`, `Api/`, `Auth/`, `Billing/`, `Settings/`, `Webhook/`

## Key Tables

- `users` — core user data + optional Stripe columns
- `social_accounts` — OAuth credentials (feature-gated)
- `user_settings` — key-value preferences
- `personal_access_tokens` — Sanctum API tokens
- `audit_logs` — activity tracking with IP/user agent
- `webhook_endpoints` — user-configured webhook destinations
- `webhook_deliveries` — outgoing webhook attempt history
- `incoming_webhooks` — received webhooks (GitHub/Stripe)
- `two_factor_authentications` — TOTP secrets + recovery codes
- Stripe tables: `customers`, `subscriptions`, `subscription_items` (Cashier)
- `feature_flag_overrides` — global/per-user feature flag overrides with reason + changed_by

## Security Infrastructure

Already implemented — verify before duplicating:
- Rate limiting: registration (5/min), login (10/min, IP+email), password reset (3/min), email verification (6/min), API read (60/min), API settings write (30/min), tokens (20/min), webhooks (30/min), export (10/min), Stripe webhook (120/min), admin mutations (10/min)
- CSRF via Sanctum middleware
- Session regeneration on login
- Configurable remember-me duration (`REMEMBER_ME_DAYS` env)
- Audit logging via `AuditService` (login, logout, registration with IP + user agent)
- Custom queued `SendEmailVerificationNotification` listener (overrides framework default via `EventServiceProvider::configureEmailVerification()`)
- Security headers via `SecurityHeaders` middleware — X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS (production), CSP (via `config/security.php`)
- Request tracing via `RequestIdMiddleware` — generates/accepts X-Request-Id, shares with logging + Sentry
- Rate limit headers via `RateLimitHeaders` middleware — adds X-RateLimit-Reset on throttled API responses
- Webhook signature verification via `VerifyWebhookSignature` middleware — HMAC-SHA256 validation on incoming webhooks
- Subscription enforcement via `EnsureSubscribed` middleware — gates billing-required routes
- Admin access via `EnsureIsAdmin` middleware — gates admin panel routes (aliased as `admin`)
- Onboarding flow via `EnsureOnboardingCompleted` middleware — redirects incomplete onboarding
- Inertia shared data via `HandleInertiaRequests` middleware — shares auth, features, flash props
- Plan tier definitions in `config/plans.php` — free, pro, team (3-50 seats), enterprise

## Critical Gotchas

**Billing (DO NOT MODIFY WITHOUT READING):**

- **Why eager loading is required:** Cashier methods like `cancel()` and `swap()` internally access `$subscription->owner` and nested `$subscription->items->subscription` relationships. Without eager loading, each call triggers lazy loading queries, causing N+1 problems and potential race conditions.

- **Detection rule:** If you're calling ANY Cashier method (`cancel`, `resume`, `swap`, `updateQuantity`, `noProrate`, `anchorBillingCycleOn`), you MUST eager load first: `$subscription->load('owner', 'items.subscription')`

- **Error symptom:** `Attempt to read property "stripe_id" on null` when calling `->cancel()` means `owner` wasn't loaded.

- **Pattern to follow:** See `app/Services/BillingService.php` lines 68-70 for correct eager loading pattern.

- **Redis locks:** All subscription mutations MUST use `BillingService` methods — direct Cashier calls will cause race conditions. Redis locks (35s timeout) prevent concurrent operations. If lock acquisition fails, operation is rejected with `ConcurrentOperationException`.

- **Seat constraints:** Team/Enterprise tiers have min 1, max 50 seats for team tier — validate before subscription creation.

**Webhook Signature Verification:**
- **Incoming:** `VerifyWebhookSignature` middleware validates `X-Webhook-Signature` header using HMAC-SHA256 with provider-specific secrets from `config/webhooks.php)`. Signature format: `sha256=<hex-digest>` where digest = `hash_hmac('sha256', $rawPayload, $secret)`. Each provider (GitHub, Stripe, custom) has its own secret key.
- **Outgoing:** `DispatchWebhookJob` signs payloads with the endpoint's stored secret using same HMAC-SHA256 scheme, sent in `X-Webhook-Signature` header. Recipients verify: `hash_equals(hash_hmac('sha256', $body, $secret), $receivedSignature)`.
- **Stripe:** Uses its own signature scheme via Cashier (not our middleware). Stripe webhook route excluded from CSRF since Cashier verifies the Stripe signature internally.
- **Adding a new provider:** Add secret to `config/webhooks.php`, create handler in `IncomingWebhookService`, register route in `routes/api.php` with `verify-webhook` middleware.

**Feature Flag Dependencies:**
- Email verification is default-ON (middleware checks `config('features.email_verification.enabled', true)`)
- Social auth auto-detects providers by env var presence (GOOGLE_CLIENT_ID/GITHUB_CLIENT_ID)
- Two-factor setup only shows in settings if `two_factor.enabled`

**Health Check Auth:**
- `/health` endpoint supports 3 modes: token-based, IP allowlist, local-only
- Configure in `config/health.php` — default is local-only in production

**Admin Cache (`AdminCacheKey`):**
- Dashboard stats are cached with 5-min TTL (`AdminCacheKey::DEFAULT_TTL`)
- Any mutation that changes user count, subscription state, token count, or webhook stats MUST call `Cache::forget(AdminCacheKey::RELEVANT_KEY->value)` — stale admin dashboards are a known bug class
- User mutations (toggle admin, deactivate, restore) invalidate `DASHBOARD_STATS`
- Billing mutations (subscribe, cancel, resume, swap) must invalidate `BILLING_STATS` and `BILLING_TIER_DIST`
- Token/webhook CRUD must invalidate their respective cache keys

**Relationship Loading with SoftDeletes:**
- When loading relationships where the related model uses `SoftDeletes`, use `->load(['relation' => fn ($q) => $q->withTrashed()])` if the display context needs to show deleted records (e.g., admin views)
- Always use null-safe operator (`?->`) with fallback when accessing relationship properties that could be null: `$model->owner?->name ?? '[Deleted User]'`

**Impersonation:**
- Stop-impersonation route must NOT use `verified` middleware — the impersonated user may be unverified
- The route is intentionally outside the admin middleware group because the impersonated user is not an admin

## CI/CD

GitHub Actions (`.github/workflows/ci.yml`):
- PHP tests with PCOV coverage (MySQL 8.0, 4 parallel workers)
- JS tests with Vitest
- Build verification (TypeScript + ESLint + production build)
- Code quality: Laravel Pint
- Security: `composer audit` + `npm audit` (npm audit uses `continue-on-error` — reports but doesn't block. Tighten before production launch.)

Note: Local tests use SQLite in-memory, CI uses MySQL 8.0.

## Deployment

- `deploy/` — nginx gzip + static cache configs, supervisor config
- `scripts/` — `vps-setup.sh`, `vps-verify.sh`, `setup-horizon.sh`, `init.sh`
- No Docker/containerization (VPS-based deployment)
- Trusted proxies configured via `TRUSTED_PROXIES` and `TRUSTED_PROXY_HEADERS` env vars in `bootstrap/app.php`
