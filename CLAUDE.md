# Laravel React Starter Template

**Stack:** Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4

**This is a production-ready starter template, not scaffolding.** Every feature is a complete, tested implementation.

## 🛡️ AI Development Safeguards

**IMPORTANT:** When using AI assistants for development, follow the safeguards in [docs/AI_DEVELOPMENT_SAFEGUARDS.md](docs/AI_DEVELOPMENT_SAFEGUARDS.md) to prevent regressions.

**Quick Reference:**
- ✅ Pre-commit hooks enforce quality gates (`.husky/pre-commit`)
- ✅ PHPStan static analysis catches bugs early (`vendor/bin/phpstan analyse`)
- ✅ Contract tests protect critical behavior (`tests/Contracts/`)
- ✅ Testing guidelines MANDATORY for AI ([docs/TESTING_GUIDELINES.md](docs/TESTING_GUIDELINES.md))
- ✅ Architectural decisions documented ([docs/adr/](docs/adr/))

**Before claiming any work complete:**
```bash
bash scripts/test-quality-check.sh  # Runs all quality gates
```

## Customization via Feature Flags

Configure your app by toggling features in `config/features.php` (or `.env`). 10 feature flags control major subsystems:

**Common configurations:**
- **SaaS with billing:** Enable `billing`, `webhooks`, `two_factor`, `api_tokens`
- **Internal tool:** Enable `two_factor`, `api_tokens`, `notifications`; disable `billing`, `social_auth`
- **Simple MVP:** Enable only `email_verification`, `user_settings`; disable premium features

**Safe to toggle:** Feature-gated routes don't register when disabled. Database tables remain but stay empty. UI elements conditionally render.

See "Feature Flags" section below for what each flag controls.

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

**What each flag controls:**
- `billing`: BillingService, SubscriptionController, PricingController, pricing page, billing portal, Stripe webhooks, CheckIncompletePayments command
- `social_auth`: SocialAuthController, SocialAccount model, Google/GitHub OAuth flows
- `email_verification`: Email verification routes, SendEmailVerificationNotification listener, middleware
- `api_tokens`: TokenController, API token CRUD UI in settings
- `user_settings`: UserSettingsController, theme/timezone persistence
- `notifications`: NotificationController, in-app notification UI
- `onboarding`: OnboardingController, welcome wizard flow
- `api_docs`: Scribe-generated API documentation
- `two_factor`: TwoFactorController, TOTP setup/verification, recovery codes
- `webhooks`: WebhookService, WebhookEndpoint/Delivery/Incoming models, signature verification

**Disabling features:** Set env var to `false`. Feature-gated routes won't register, middleware won't apply, UI elements won't render. Database tables remain (safe to leave empty).

### Feature Flag Dependency Graph

**Hard Dependencies (will break if dependency disabled):**
- `onboarding` → requires `user_settings` (stores completion timestamp in user_settings table)
- `billing` → requires `webhooks` for Stripe webhooks (auto-enabled in routes/api.php)
- `two_factor` → requires `user_settings` for enrollment preference (optional fallback exists)
- `api_docs` → requires `api_tokens` (documents token endpoints)

**Soft Dependencies (graceful degradation):**
- `notifications` + `webhooks` = webhook delivery notifications (webhook failures still logged to database)
- `billing` + `email_verification` = prevents subscriptions from unverified users (check in SubscriptionController)
- `social_auth` + `email_verification` = OAuth accounts start pre-verified (handled in SocialAuthService)

**Testing Feature Flag Combinations:**
When adding a new feature-gated feature, test these scenarios:
1. Feature ON, dependency OFF → should fail gracefully or show "requires X feature" message
2. Feature ON, dependency ON → full functionality
3. Feature OFF → routes don't register, nav links hidden, API returns 404

**Adding a New Feature Flag:**
1. Add to `config/features.php` with env var and `enabled` key
2. Document in this section "What each flag controls"
3. Add dependency to this graph if applicable
4. Gate routes with `if (config('features.X.enabled'))` in routes files
5. Gate nav links with `{features.X && ...}` in TSX
6. Add test: `it('route returns 404 when feature disabled')`

## Environments

| Env | APP_ENV | Mail | Analytics | Sentry |
|-----|---------|------|-----------|--------|
| Local | `local` | Mailpit (:1025, view at :8025) | Disabled | Disabled |
| Preview | `preview` | SMTP | Optional | Enabled |
| Production | `production` | SMTP | `GOOGLE_ANALYTICS_ID` | `SENTRY_LARAVEL_DSN` |

## Architecture

**Models:** User, UserSetting (key-value), SocialAccount (OAuth), AuditLog, WebhookEndpoint, WebhookDelivery, IncomingWebhook, TwoFactorAuthentication (via Laragear)

**Services:**
- `AuditService` — activity logging
- `BillingService` — Redis-locked Stripe subscription mutations (CRITICAL: see Gotchas)
- `PlanLimitService` — enforce subscription limits (projects, items, tokens)
- `SessionDataMigrationService` — migrate guest session data on login
- `SocialAuthService` — OAuth provider abstraction
- `WebhookService` — outgoing webhook dispatch with HMAC signing
- `IncomingWebhookService` — process/validate incoming webhooks

**Billing (Production-Grade):**
- `BillingService` — Redis-locked subscription mutations (create, cancel, resume, swap)
  - **CRITICAL:** Uses Redis locks (35s timeout) to prevent concurrent Stripe API calls
  - **CRITICAL:** Must eager load `owner` + `items.subscription` before Cashier methods
  - All operations wrapped in DB transactions for atomicity
- Plan tiers: free, pro, team (3-50 seats), enterprise (custom)
- Incomplete payment tracking: `CheckIncompletePayments` command sends reminders at 1h/12h

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
- `routes/auth.php` — auth (Breeze + social auth + email verification)
- `routes/api.php` — Sanctum-protected API (user, settings, tokens)
- Health check: `/up` (configured in `bootstrap/app.php`)

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

**Rule:** Always use policies for resource authorization (user can view/update/delete specific resource).

```php
// ❌ Bad: Manual role checks in controller
if (auth()->user()->is_admin || $project->user_id === auth()->id()) {
    $project->delete();
}

// ✅ Good: Policy with clear rules
// In ProjectPolicy:
public function delete(User $user, Project $project): bool
{
    return $user->is_admin || $project->user_id === $user->id;
}

// In Controller:
$this->authorize('delete', $project);
```

**When NOT to use policies:**
- Feature flag checks (use `abort_unless(feature_enabled('X'))` in controller constructor)
- Role-based route protection (use middleware: `->middleware('admin')`)
- Global permissions not tied to a resource (use Gate::define in AuthServiceProvider)

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

**Note:** No Jobs directory currently exists. Create `app/Jobs/{Domain}/` when first needed.

### When to Create a FormRequest vs Inline Validation

**Rule:** ALWAYS use FormRequest. Never inline `$request->validate()` in controllers.

**Reasons:**
- Keeps controllers thin (single responsibility)
- Centralizes authorization + validation logic
- Reusable across multiple controller methods
- Easier to test in isolation

```php
// ❌ Bad: Inline validation
public function update(Request $request, User $user)
{
    $request->validate(['name' => 'required|max:255']);
    // ...
}

// ✅ Good: FormRequest
public function update(UpdateUserRequest $request, User $user)
{
    $user->update($request->validated());
    // ...
}
```

## Performance Budgets

### Query Count Limits (enforce in tests)

**Per-request budgets:**
- Dashboard page: ≤5 queries (user + settings + cached stats)
- User index (admin): ≤3 queries per page (users + pagination count + audit log latest)
- Detail pages with relationships: ≤8 queries (model + 3 relationships + cache checks)
- API endpoints: ≤4 queries (auth + main query + optional related)

**When to eager load:**
- ✅ Always: accessing `$model->relationship->property` in Blade/Inertia props
- ✅ Always: looping over collection and accessing relationships
- ✅ Always: before calling Cashier methods (`->load('owner', 'items.subscription')`)
- ✅ Conditionally: if feature flag might show data

**How to verify in tests:**
```php
it('user index page has no N+1 queries', function () {
    DB::enableQueryLog();

    $admin = User::factory()->admin()->create();
    User::factory()->count(20)->create(); // create 20 users

    $this->actingAs($admin)->get('/admin/users');

    $queries = DB::getQueryLog();
    expect(count($queries))->toBeLessThanOrEqual(5); // allow user auth + main query + count
});
```

**Common N+1 patterns to avoid:**
```php
// ❌ Bad: N+1 in loop
foreach ($users as $user) {
    $tier = $user->subscription->tier; // lazy loads for each user
}

// ✅ Good: eager load
$users = User::with('subscription')->get();
foreach ($users as $user) {
    $tier = $user->subscription->tier;
}

// ❌ Bad: N+1 in Inertia props
return Inertia::render('Users/Index', [
    'users' => $users, // User model with subscription relationship not loaded
]);

// ✅ Good: eager load before Inertia
return Inertia::render('Users/Index', [
    'users' => $users->load('subscription'),
]);
```

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
php artisan CheckIncompletePayments  # Find failed payments, send reminders
php artisan PruneAuditLogs           # Delete old audit logs
scripts/init.sh        # First-time setup (configure project name, features)
```

## Conventions

**Backend:**
- Form Requests for validation (never inline `$request->validate()`)
- Services for business logic, controllers stay thin
- External API calls in Jobs only (create `app/Jobs/` when needed — not yet created)
- Constructor injection for dependencies
- Custom exceptions in `app/Exceptions/` when needed (currently uses Laravel defaults)

**Frontend:**
- UI primitives in `Components/ui/` (Radix + CVA + `cn()` from `lib/utils`)
- Theme via CSS variables (semantic tokens like `bg-background`, `text-foreground`)
- Forms: Inertia `useForm()` hook
- Icons: Lucide React only
- Shared props via `usePage()`, feature-gated UI via `features` prop
- Custom hooks in `hooks/`: `useMobile`, `useFormValidation`, `useTimezone`, `useUnsavedChanges`
- Shared Inertia props must stay minimal (auth summary + feature flags + flash). Never send whole Eloquent models — use explicit arrays.

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

**Migrations:**
- Always check before adding/dropping columns: `Schema::hasColumn()`
- New columns on existing tables: nullable or with default (never bare NOT NULL)
- Foreign keys: `->constrained()->cascadeOnDelete()` (auto-indexed)
- Feature-conditional migrations: only for whole-table creation (`Schema::hasTable` check). Never gate column additions/removals on feature flags — causes schema drift.

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

## Security Infrastructure

Already implemented — verify before duplicating:
- Rate limiting: registration (5/min), login (5 attempts, IP+email), password reset (3/min), email verification (6/min), API settings (30/min), tokens (20/min), webhooks (30/min), export (10/min), Stripe webhook (120/min)
- CSRF via Sanctum middleware
- Session regeneration on login
- Configurable remember-me duration (`REMEMBER_ME_DAYS` env)
- Audit logging via `AuditService` (login, logout, registration with IP + user agent)
- Custom queued `SendEmailVerificationNotification` listener (overrides framework default via `EventServiceProvider::configureEmailVerification()`)

## Critical Gotchas

**Billing (DO NOT MODIFY WITHOUT READING):**
- All subscription mutations MUST use `BillingService` methods — direct Cashier calls will cause race conditions
- Always eager load `$subscription->load('owner', 'items.subscription')` before Cashier methods to prevent lazy loading violations
- Redis locks prevent concurrent operations — if lock acquisition fails, operation is rejected with `ConcurrentOperationException`
- Team/Enterprise tiers have seat constraints (min 1, max 50 for team) — validate before subscription creation

**Webhook Signature Verification:**
- Incoming webhooks use HMAC-SHA256 with provider-specific secrets (`config/webhooks.php`)
- Stripe webhook route excluded from CSRF (signature verification replaces it)
- Outgoing webhooks use same HMAC scheme for user endpoints

**Feature Flag Dependencies:**
- Email verification is default-ON (middleware checks `config('features.email_verification.enabled', true)`)
- Social auth auto-detects providers by env var presence (GOOGLE_CLIENT_ID/GITHUB_CLIENT_ID)
- Two-factor setup only shows in settings if `two_factor.enabled`

**Migration Patterns:**
- Never gate column additions/removals on feature flags (causes schema drift)
- Feature-conditional migrations only for whole-table creation (`Schema::hasTable` check)

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
- Trusted proxies not configured — add `TrustProxies` middleware if deploying behind load balancer/CDN
