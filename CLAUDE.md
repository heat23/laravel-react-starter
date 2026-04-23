# Laravel React Starter Template

**Production-ready starter template** for Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4. Used as the base for both paid SaaS products and free applications. Every feature is a complete, tested implementation — not scaffolding.

## AI Development Safeguards

**Workflow docs** (follow for all tasks):
- [docs/AI_WORKFLOW.md](docs/AI_WORKFLOW.md) — planning checklist, implementation guardrails, TDD cycle, defense layers
- [docs/TESTING_GUIDELINES.md](docs/TESTING_GUIDELINES.md) — test standards, verification, and edge case checklist
- [docs/DEBUGGING_GUIDE.md](docs/DEBUGGING_GUIDE.md) — test failure diagnosis protocol
- [docs/AI_PROMPT_TEMPLATES.md](docs/AI_PROMPT_TEMPLATES.md) — structured request templates

**Reference docs:** [docs/FEATURE_FLAGS.md](docs/FEATURE_FLAGS.md) (dependency graph, gating patterns)

**Project-specific rules (beyond global CLAUDE.md):**
- Contract tests in `tests/Contracts/` — do NOT modify without user approval
- Full quality gate: `bash scripts/test-quality-check.sh` (or manually: `php artisan test --parallel && npm test && vendor/bin/phpstan analyse && vendor/bin/pint --test && npm run lint && npm run build`)
- Defense layers: pre-commit hooks (`.husky/pre-commit`), CI gates (`.github/workflows/ci.yml`), mutation testing (`infection`), ADRs (`docs/adr/`)
- Path-scoped rules in `.claude/rules/` — billing, webhooks, testing, accessibility, frontend, lifecycle, migrations (load automatically when touching relevant files)

## Feature Flags

Configure your app by toggling features in `config/features.php` (or `.env`). 11 feature flags control major subsystems.

| Flag | Env Var | Default | What it enables |
|------|---------|---------|-----------------|
| `billing.enabled` | `FEATURE_BILLING` | false | Stripe Cashier, pricing page, billing portal |
| `billing.tax_enabled` | `FEATURE_BILLING_TAX` + `BILLING_TAX_CONFIRM_COMPLIANT` | false | Stripe Tax (two-key gate — both env vars required) |
| `social_auth.enabled` | `FEATURE_SOCIAL_AUTH` | false | Google/GitHub OAuth (buttons render only when CLIENT_ID env vars are set) |
| `email_verification.enabled` | `FEATURE_EMAIL_VERIFICATION` | true | Email verification flow |
| `api_tokens.enabled` | `FEATURE_API_TOKENS` | true | Sanctum token management UI |
| `user_settings.enabled` | `FEATURE_USER_SETTINGS` | true | Theme/timezone persistence |
| `notifications.enabled` | `FEATURE_NOTIFICATIONS` | false | In-app notification system |
| `onboarding.enabled` | `FEATURE_ONBOARDING` | true | Welcome wizard (hard dep: `user_settings`) |
| `two_factor.enabled` | `FEATURE_TWO_FACTOR` | false | TOTP 2FA authentication |
| `webhooks.enabled` | `FEATURE_WEBHOOKS` | false | User-configurable incoming/outgoing webhooks (Stripe webhooks are always registered by Cashier, independent of this flag) |
| `admin.enabled` | `FEATURE_ADMIN` | true | Admin panel: user management, health monitoring, audit logs, config viewer, system info |
| `indexnow.enabled` | `FEATURE_INDEXNOW` | false | IndexNow instant-indexing pings (Bing/Yandex/etc.); `INDEXNOW_AUTO_PING_SITEMAP` inherits this flag when unset |

**Hard dependencies are runtime-enforced** by `FeatureFlagService::resolve()` — if `onboarding` is on but `user_settings` is off, the dependent flag resolves to `false` and a `Log::warning` is emitted. See [docs/FEATURE_FLAGS.md](docs/FEATURE_FLAGS.md).

**Feature flag service layer (three classes):**
- `FeatureFlagValidator` — pure validation; throws on unknown or protected flags; no external deps
- `FeatureFlagOverrideStore` — all DB + cache access for global/user overrides
- `FeatureFlagService` — slim orchestrator (≤200 LOC); delegates to the two classes above + `CacheInvalidationManager`

**Common configurations:** SaaS with billing (enable `billing`, `webhooks`, `two_factor`, `api_tokens`) | Internal tool (enable `two_factor`, `api_tokens`, `notifications`) | Simple MVP (leave defaults as-is — `admin` + `onboarding` + `email_verification` + `user_settings` + `api_tokens` are on).

**Disabling features:** Set env var to `false`. Feature-gated routes won't register, middleware won't apply, UI elements won't render. Database tables remain (safe to leave empty).

## Environments

| Env | APP_ENV | Mail | Analytics | Sentry |
|-----|---------|------|-----------|--------|
| Local | `local` | Mailpit (:1025, view at :8025) | Disabled | Disabled |
| Preview | `preview` | SMTP | Optional | Enabled |
| Production | `production` | SMTP | `GOOGLE_ANALYTICS_ID` | `SENTRY_LARAVEL_DSN` |

## Architecture

**Tenancy:** Single-tenant. Do not add account/org/workspace scoping unless explicitly requested.

**Routes:**
- `routes/web.php` — thin orchestrator that requires the three domain files below
- `routes/marketing.php` — public/SEO surface (no auth): /, /compare/*, /features/*, /guides/*, /blog/*, /changelog, /roadmap, etc.
- `routes/app.php` — authenticated user surface: dashboard, profile, settings, billing, onboarding, NPS, export
- `routes/dev.php` — infrastructure: /health, /robots.txt, /sitemap.xml, /llms.txt, /favicon.ico, IndexNow key
- `routes/admin.php` — admin panel (required from web.php when `admin.enabled`), middleware: `['auth', 'verified', 'admin', 'throttle:60,1']`
- `routes/auth.php` — auth (Breeze + social auth + email verification)
- `routes/api.php` — Sanctum-protected API (user, settings, tokens)
- Health check: `/up` (Laravel built-in) + `/health` (custom `HealthCheckController` with token/IP/local auth)

**Two-Factor Authentication:** Via `laragear/two-factor` package (TOTP + recovery codes). `TwoFactorChallengeController` handles verification. Feature-gated via `two_factor.enabled`.

**Vendor Models (not in `app/Models/`):** TwoFactorAuthentication (`Laragear\TwoFactor\Models\TwoFactorAuthentication` — referenced via User trait, do not create a local model)

## Decision-Making Frameworks

### When to Create a Service Class

Create a dedicated Service when **ANY** of these apply:
1. Logic involves external API calls (Stripe, GitHub, etc.)
2. Logic requires distributed locking (Redis locks)
3. Logic wraps database transactions across multiple models
4. Logic is reusable across 2+ controllers/jobs/commands
5. Logic involves complex state machines or multi-step processes
6. Logic requires extensive mocking/stubbing in tests

Keep logic in Controller when **ALL** are true: single model CRUD, no external dependencies, validation handled by Form Request, no transaction coordination, <30 lines of business logic.

**Examples:** `BillingService` (Redis locks + Stripe API + transactions) | `WebhookService` (external HTTP + HMAC signing) | `OnboardingController` (single setting update)

### When to Create a Policy vs Manual Auth Checks

Always use policies for resource authorization (`$this->authorize('delete', $project)`).
- Feature flag checks -> `abort_unless(feature_enabled('X'))` in controller
- Role-based route protection -> middleware: `->middleware('admin')`
- Global permissions not tied to a resource -> `Gate::define` in AuthServiceProvider

### When to Create a Job vs Execute Synchronously

Job: external API call that can be retried, long-running (>5s), rate-limited, should survive request timeout.
Synchronous: user needs immediate feedback, fast (<1s), failure requires user action.

## Performance Budgets

**Per-request query budgets:** Dashboard <=5 | User index (admin) <=3/page | Detail pages <=8 | API endpoints <=4

**Cache strategy (all cached with `AdminCacheKey` enum):**
- Admin dashboard stats (5min TTL), billing tier distribution (5min TTL), chart aggregations (1hr TTL), feature flag global overrides (5min TTL, flushed on change)
- Do NOT cache: user-specific current state, per-request data, small lookup tables (<100 rows)

**Cache invalidation checklist:**
- User count/subscription changes -> `DASHBOARD_STATS`
- Billing mutations -> `BILLING_STATS` + `BILLING_TIER_DIST`
- Webhooks/tokens/2FA stats -> respective enum key
- Global feature flag override changes -> `AdminCacheKey::flushAll()`

## How to Add a New Feature

1. Migration (`php artisan make:migration`) -> Model + Factory -> Form Request -> Controller -> Policy (if user-owned) -> Page (`resources/js/Pages/`) -> Route (feature-gated if optional) -> Tests -> Nav links -> TypeScript types

**Review checklist (run before claiming done):**
- **Query count budget:** Does page meet budget? Add `DB::enableQueryLog()` test.
- **Accessibility:** Can you complete the flow with keyboard only?
- **Soft-delete sweep:** Does code access `->user->`, `->owner->` without `?->` where related model uses `SoftDeletes`? Add `withTrashed()` to admin-facing queries.
- **Middleware audit:** Route outside normal middleware group — right (and ONLY right) middleware? Don't put `verified` on routes unverified users need.
- **Cache invalidation:** Mutation affects admin dashboard/cached stats? Call `Cache::forget()` on relevant `AdminCacheKey`.
- **Async contract:** `onConfirm` callback returns `Promise` that resolves after server responds (not fire-and-forget)?
- **Nav/URL prefix collisions:** Does `startsWith` matching cause false positives with parent routes?
- **Local state vs URL params:** Does `clearFilters` reset ALL state?

## Commands

```bash
composer dev           # Start Laravel + Vite + Queue
php artisan test       # Run tests (Pest, parallel)
npm test               # Vitest frontend tests
npm run build          # Production build
npm run lint           # ESLint
php artisan subscriptions:check-incomplete  # Find failed payments, send reminders
php artisan billing:enforce-grace-period    # Downgrade past-due subscriptions after grace window
php artisan trials:check-expired            # Handle expired trial transitions
php artisan emails:send-welcome-sequence    # Send scheduled welcome emails (2, 3)
php artisan audit:prune                     # Delete old audit logs (--days=N)
php artisan webhooks:mark-abandoned         # Mark orphaned webhook deliveries as abandoned (--hours=N)
php artisan webhooks:delete-old             # Delete old terminal webhook deliveries (--days=N)
php artisan admin:health-alert              # Run health checks, alert on failures
php artisan indexnow:generate-key           # Generate an IndexNow verification key
php artisan prune-read-notifications        # Delete old read notifications
scripts/init.sh                             # First-time setup
```

## Critical Gotchas

**Feature Flag Dependencies:**
- Email verification is default-ON (middleware checks `config('features.email_verification.enabled', true)`)
- Social auth auto-detects providers by env var presence (GOOGLE_CLIENT_ID/GITHUB_CLIENT_ID)
- Two-factor setup only shows in settings if `two_factor.enabled`

**Health Check Auth:** `/health` supports 3 modes: token-based, IP allowlist, local-only. Configure in `config/health.php` — default is local-only in production.

**Admin Cache (`AdminCacheKey`):** Dashboard stats cached 5-min TTL. Any mutation changing user count, subscription state, token count, or webhook stats MUST call the relevant semantic method on `CacheInvalidationManager` (e.g. `invalidateDashboardStats()`, `invalidateIndexNow()`) — never call `Cache::forget(AdminCacheKey::*)` directly outside that service. Stale admin dashboards are a known bug class.

**Relationship Loading with SoftDeletes:** Use `->load(['relation' => fn ($q) => $q->withTrashed()])` for admin views. Always use null-safe operator (`?->`) with fallback: `$model->owner?->name ?? '[Deleted User]'`

## Error Recovery & Test Quality

Test failures? Follow [docs/DEBUGGING_GUIDE.md](docs/DEBUGGING_GUIDE.md). Edge case checklist: see [docs/TESTING_GUIDELINES.md](docs/TESTING_GUIDELINES.md).

## CI/CD

GitHub Actions (`.github/workflows/ci.yml`): PHP tests with PCOV coverage (MySQL 8.0, 4 parallel workers), JS tests (Vitest), build verification, Laravel Pint, `composer audit` + `npm audit`. Local tests use SQLite in-memory, CI uses MySQL 8.0.

## Deployment

`deploy/` — nginx gzip + static cache configs, supervisor config. `scripts/` — `vps-setup.sh`, `vps-verify.sh`, `setup-horizon.sh`. No Docker (VPS-based). Trusted proxies via `TRUSTED_PROXIES` env in `bootstrap/app.php`.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/cashier (CASHIER) - v16
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- tightenco/ziggy (ZIGGY) - v2
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA_REACT) - v2
- react (REACT) - v18
- eslint (ESLINT) - v9

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `cashier-stripe-development` — Handles Laravel Cashier Stripe integration including subscriptions, webhooks, Stripe Checkout, invoices, charges, refunds, trials, coupons, metered billing, and payment failure handling. Triggered when a user mentions Cashier, Billable, IncompletePayment, stripe_id, newSubscription, Stripe subscriptions, or billing. Also applies when setting up webhooks, handling SCA/3DS payment failures, testing with Stripe test cards, or troubleshooting incomplete subscriptions, CSRF webhook errors, or migration publish issues.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `inertia-react-development` — Develops Inertia.js v2 React client-side applications. Activates when creating React pages, forms, or navigation; using <Link>, <Form>, useForm, or router; working with deferred props, prefetching, or polling; or when user mentions React with Inertia, React pages, React forms, or React navigation.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`, `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>
