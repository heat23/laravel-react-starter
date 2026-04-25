# Laravel React Starter Template

Laravel 12 + Inertia.js v2 + React 18 + TypeScript + Tailwind CSS v4. Production-ready starter for SaaS and free apps. Every feature is fully implemented and tested — not scaffolding.

## Stack (authoritative versions)

PHP 8.4 | Laravel 12 | Inertia v2 | React 18 | Cashier 16 | Sanctum 4 | Pest 4 | Vitest | Tailwind v4 | Larastan 3 | Pint 1 | Ziggy 2 | Breeze 2 | Boost 2

## Project-Specific Workflow Docs

Load on demand. Do not read unless task matches:
- `docs/AI_WORKFLOW.md` — planning checklist, TDD cycle, defense layers
- `docs/TESTING_GUIDELINES.md` — test standards, edge case checklist
- `docs/DEBUGGING_GUIDE.md` — test failure diagnosis protocol
- `docs/AI_PROMPT_TEMPLATES.md` — structured request templates
- `docs/FEATURE_FLAGS.md` — dependency graph
- `docs/adr/` — architecture decision records (read before contradicting an ADR)

## Hard Rules (project-specific, beyond global CLAUDE.md)

- Contract tests in `tests/Contracts/` — DO NOT modify without user approval
- Quality gate: `bash scripts/test-quality-check.sh` (runs Pest + Vitest + PHPStan + Pint + ESLint + build)
- Mutation testing config in `infection.json5` (minMsi=50, minCoveredMsi=65)
- Path-scoped rules in `.claude/rules/` auto-load when touching matching files (billing, webhooks, testing, accessibility, frontend, lifecycle, migrations, seo)
- Tenancy: SINGLE-TENANT. Never add account/org/workspace scoping.
- Soft deletes: HARD deletes by default. Project may override per-model (e.g., `Site`).
- Feature flag dependencies are runtime-enforced by `FeatureFlagService::resolve()` — disabling a parent disables dependents and emits `Log::warning`.

## Discovery Commands (use these instead of memorizing lists)

```bash
php artisan list                      # All artisan commands
php artisan route:list                # All registered routes
php artisan config:show features      # Current feature flag state
ls config/                            # Configuration files
ls app/Services/                      # Service classes
ls app/Enums/                         # Enums (PlanTier, AdminCacheKey, AuditEvent, LifecycleStage)
ls .claude/rules/                     # Path-scoped rule files
```

## Common Operations

```bash
composer dev                          # Start Laravel + Vite + Queue (dev)
php artisan test --parallel           # Pest parallel
php artisan test --compact --filter=X # Targeted test
npm test                              # Vitest
npm run build                         # Production build
npm run lint                          # ESLint
vendor/bin/pint --dirty --format agent  # Format changed PHP only
vendor/bin/phpstan analyse            # Static analysis
bash scripts/test-quality-check.sh    # Full quality gate (run once at end)
```

## Feature Flags (see `config/features.php` for source of truth)

Toggle subsystems via env vars. Disabled features: routes don't register, middleware skipped, UI hidden, DB tables remain.

| Flag | Env Var | Default |
|------|---------|---------|
| `billing.enabled` | `FEATURE_BILLING` | false |
| `billing.tax_enabled` | `FEATURE_BILLING_TAX` + `BILLING_TAX_CONFIRM_COMPLIANT` (BOTH required) | false |
| `social_auth.enabled` | `FEATURE_SOCIAL_AUTH` | false |
| `email_verification.enabled` | `FEATURE_EMAIL_VERIFICATION` | true |
| `api_tokens.enabled` | `FEATURE_API_TOKENS` | true |
| `user_settings.enabled` | `FEATURE_USER_SETTINGS` | true |
| `notifications.enabled` | `FEATURE_NOTIFICATIONS` | false |
| `onboarding.enabled` | `FEATURE_ONBOARDING` | true (hard dep: `user_settings`) |
| `two_factor.enabled` | `FEATURE_TWO_FACTOR` | false |
| `webhooks.enabled` | `FEATURE_WEBHOOKS` | false (Stripe webhooks always registered, independent) |
| `admin.enabled` | `FEATURE_ADMIN` | true |
| `indexnow.enabled` | `FEATURE_INDEXNOW` | false |

**Service layer:** `FeatureFlagValidator` (validation) + `FeatureFlagOverrideStore` (DB/cache) + `FeatureFlagService` (orchestrator, ≤200 LOC).

**Email verification middleware** checks `config('features.email_verification.enabled', true)` — default ON.
**Social auth** auto-detects providers via `GOOGLE_CLIENT_ID`/`GITHUB_CLIENT_ID` env presence.

## Routes Layout

`routes/web.php` is a thin orchestrator requiring per-domain files:

| File | Purpose | Auth |
|------|---------|------|
| `marketing.php` | Public/SEO surface (/, /compare/*, /features/*, /guides/*, /blog/*, /changelog, /roadmap) | none |
| `app.php` | Authenticated user surface (dashboard, profile, settings, billing, onboarding, NPS, export) | auth |
| `dev.php` | Infra: /health, /robots.txt, /sitemap.xml, /llms.txt, IndexNow key | mixed |
| `admin.php` | Admin panel (loaded only when `admin.enabled`) | `auth, verified, admin, throttle:60,1` |
| `auth.php` | Breeze + social auth + email verification | guest/auth |
| `api.php` | Sanctum-protected API (user, settings, tokens) | sanctum |

Health: `/up` (Laravel built-in) + `/health` (custom `HealthCheckController`, 3 modes: token / IP allowlist / local-only; default local-only in prod, see `config/health.php`).

## Two-Factor Authentication

`laragear/two-factor` package (TOTP + recovery codes). Vendor model: `Laragear\TwoFactor\Models\TwoFactorAuthentication` — referenced via User trait. **Do not create a local `TwoFactorAuthentication` model.** Verification handled by `TwoFactorChallengeController`. Gated by `two_factor.enabled`.

## Decision Frameworks

### Service class vs Controller logic
**Service** when ANY apply: external API calls, Redis locks, multi-model transactions, reused 2+ times, complex state machines, heavy mocking. **Controller** when ALL apply: single-model CRUD, no external deps, Form Request validates, no transactions, <30 LOC.

### Authorization
- Resource auth → Policy (`$this->authorize('delete', $project)`)
- Feature flag → `abort_unless(feature_enabled('X'))` in controller
- Role-based route → middleware (`->middleware('admin')`)
- Global non-resource permission → `Gate::define` in `AuthServiceProvider`

### Job vs sync
**Job:** retryable, >5s, rate-limited, must survive request timeout. **Sync:** user needs immediate feedback, <1s, failure requires user action.

## Performance Budgets

| Surface | Max queries |
|---------|-------------|
| Dashboard | 5 |
| Admin user index | 3/page |
| Detail pages | 8 |
| API endpoints | 4 |

Enforce with `DB::enableQueryLog()` test assertions, not microtime checks.

## Cache Strategy (`AdminCacheKey` enum)

| Cache | TTL |
|-------|-----|
| Admin dashboard stats | 5min |
| Billing tier distribution | 5min |
| Chart aggregations | 1hr |
| Feature flag global overrides | 5min (flushed on change) |

**Do NOT cache:** user-specific current state, per-request data, lookups <100 rows.

**Invalidation:** Always go through `CacheInvalidationManager` (e.g. `invalidateDashboardStats()`, `invalidateIndexNow()`). Never call `Cache::forget(AdminCacheKey::*)` directly outside this service. Stale admin dashboards are a known bug class.

| Mutation | Invalidate |
|----------|------------|
| User count / subscription change | `DASHBOARD_STATS` |
| Billing mutation | `BILLING_STATS` + `BILLING_TIER_DIST` |
| Webhooks/tokens/2FA stats | respective enum key |
| Global feature flag override change | `AdminCacheKey::flushAll()` |

## Critical Gotchas

**Cashier eager loading:** Methods like `cancel()`, `swap()` access `$subscription->owner` and `items.subscription` internally. ALWAYS eager load before calling: `$subscription->load('owner', 'items.subscription')`. Symptom of failure: `Attempt to read property "stripe_id" on null`. Always go through `BillingService` (Redis-locked, 35s timeout).

**Soft-deleted relationships:** Admin views must use `?->` with fallback (`$model->owner?->name ?? '[Deleted User]'`) and load with trashed: `->load(['owner' => fn ($q) => $q->withTrashed()])`.

**Inertia router fire-and-forget:** `router.post/patch/delete` return immediately, NOT a Promise. Awaiting them does nothing. Use `onSuccess`/`onError` callbacks or `LoadingButton`. See ADR-0004.

**Boot-time route registration:** Routes gated by `if (config('features.X.enabled'))` cannot be tested for both states in one suite — `phpunit.xml` determines which routes register at boot. Test controllers/middleware in unit tests instead.

**Stripe webhooks** use Cashier's signature scheme (NOT the project's `VerifyWebhookSignature` middleware). Stripe events: `/stripe/webhook` → `StripeWebhookController` → `StripeEventMap` → `app/Webhooks/Stripe/Handlers/`.

**Plan tier:** Use `App\Enums\PlanTier` (Free, Pro, ProTeam, Team, Enterprise). Never raw strings (`'pro'`, `'team'`). Serialize with `->value` for Inertia/JSON.

## Add-A-Feature Flow

Migration → Model + Factory → Form Request → Controller → Policy (if user-owned) → Inertia Page (`resources/js/Pages/`) → Route (feature-gate if optional) → Tests → Nav links → TypeScript types.

**Pre-completion checklist:**
- Query budget met (`DB::enableQueryLog()` assertion)
- Keyboard-only flow works
- Soft-delete sweep: `?->` + `withTrashed()` for admin queries
- Middleware audit: no spurious `verified` on routes unverified users need
- Cache invalidation called on relevant mutation
- Inertia callbacks use `onSuccess`/`onError`, not `await`
- `clearFilters` resets ALL state (URL params + local)
- No `startsWith` collisions in nav active-state matching

## Environments

| Env | APP_ENV | Mail | Analytics | Sentry |
|-----|---------|------|-----------|--------|
| Local | `local` | Mailpit (:1025, UI :8025) | off | off |
| Preview | `preview` | SMTP | optional | on |
| Production | `production` | SMTP | `GOOGLE_ANALYTICS_ID` | `SENTRY_LARAVEL_DSN` |

## CI/CD & Deployment

GitHub Actions (`.github/workflows/ci.yml`): Pest+PCOV (MySQL 8.0, 4 parallel workers), Vitest, build, Pint, `composer audit`, `npm audit`. Local tests: SQLite in-memory; CI: MySQL 8.0.

Deployment: VPS-based (no Docker). `deploy/` (nginx + supervisor configs), `scripts/vps-setup.sh`, `scripts/setup-horizon.sh`. Trusted proxies via `TRUSTED_PROXIES` env.

===

<laravel-boost-guidelines>

## Boost-Specific Rules (auto-injected by laravel/boost)

**Skill activation (mandatory):**
- `cashier-stripe-development` — any Cashier/Billable/subscription/Stripe work
- `pest-testing` — any Pest test write/edit/refactor
- `inertia-react-development` — any Inertia React page/form/router work

**Boost MCP tools (use over generic alternatives):**
- `search-docs` — version-specific Laravel docs. Multi-query, broad terms (e.g. `["rate limiting", "routing"]`). Auto-filters to installed packages. Do NOT add package names to queries.
- `database-schema` — inspect tables before writing migrations/models
- `database-query` — read-only queries
- `browser-logs` — recent browser errors only
- `get-absolute-url` — when sharing URLs
- `tinker` — `php artisan tinker --execute "..."` for debugging
- `last-error` — recent application errors

**Search syntax:** auto-stemming words; multiple terms = AND; `"quoted"` = exact phrase; `queries=[...]` = OR.

**Conventions (project-overrideable):**
- Curly braces always (single-line bodies too)
- Constructor property promotion in PHP 8
- Explicit return types on all methods/functions
- Enum keys in TitleCase
- PHPDoc blocks over inline comments
- Form Requests for validation (never inline)
- `Model::query()` over `DB::`; eager load to prevent N+1
- Eloquent API Resources + versioning for new APIs
- `route()` named routes for URL generation
- `ShouldQueue` for >5s operations
- `config('app.name')` not `env('APP_NAME')` outside config files
- Casts via `casts()` method (Laravel 12), not `$casts` property
- Migration column modify must include ALL prior attributes (else dropped)
- Inertia v2 features available: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data
- Deferred props need pulsing/skeleton empty states

**Laravel 12 structure:**
- Middleware/exceptions/routing in `bootstrap/app.php` (not `app/Http/Kernel.php`)
- `bootstrap/providers.php` for app providers
- No `app/Console/Kernel.php` — use `routes/console.php`
- Console commands in `app/Console/Commands/` auto-registered

**Test enforcement:** Every change needs a test (new or updated). Run minimum tests during iteration (`--filter`, `--dirty`). Never delete tests without approval.

**Pint:** `vendor/bin/pint --dirty --format agent` to fix; never `--test --format agent`.

**Vite manifest error:** run `npm run build` (or ask user to run `npm run dev` / `composer run dev`).

</laravel-boost-guidelines>
