# Laravel React Starter Template

**Stack:** Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4

**This is a production-ready starter template, not scaffolding.** Every feature is a complete, tested implementation.

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
