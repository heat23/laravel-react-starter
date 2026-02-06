# Laravel React Starter Template

**Stack:** Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4

This is a reusable starter template. All features are toggleable via `config/features.php`.

## Feature Flags

Check `config/features.php` and `.env` before implementing. Features default off unless env var is set:

| Flag | Env Var | What it enables |
|------|---------|-----------------|
| `billing.enabled` | `FEATURE_BILLING` | Stripe Cashier, pricing page, billing portal |
| `social_auth.enabled` | `FEATURE_SOCIAL_AUTH` | Google/GitHub OAuth (auto-detected by client ID presence) |
| `email_verification.enabled` | `FEATURE_EMAIL_VERIFICATION` | Email verification flow (default: true) |
| `api_tokens.enabled` | `FEATURE_API_TOKENS` | Sanctum token management UI (default: true) |
| `user_settings.enabled` | `FEATURE_USER_SETTINGS` | Theme/timezone persistence (default: true) |

## Environments

| Env | APP_ENV | Mail | Analytics | Sentry |
|-----|---------|------|-----------|--------|
| Local | `local` | Mailpit (:1025, view at :8025) | Disabled | Disabled |
| Preview | `preview` | SMTP | Optional | Enabled |
| Production | `production` | SMTP | `GOOGLE_ANALYTICS_ID` | `SENTRY_LARAVEL_DSN` |

## Architecture

**Models:** User, UserSetting (key-value), SocialAccount (OAuth)

**Services:**
- `AuditService` — activity logging
- `PlanLimitService` — enforce subscription limits (projects, items, tokens)
- `SessionDataMigrationService` — migrate guest session data on login
- `SocialAuthService` — OAuth provider abstraction

**Routes:**
- `routes/web.php` — pages (feature-gated with `if (config('features.*.enabled'))`)
- `routes/auth.php` — auth (Breeze + social auth + email verification)
- `routes/api.php` — Sanctum-protected API (user, settings, tokens)

## How to Add a New Feature

1. **Migration:** `php artisan make:migration create_{table}_table` — follow patterns in `database/migrations/CLAUDE.md`
2. **Model:** `php artisan make:model {Name}` — add to User relationship if user-owned
3. **Form Request:** Create in `app/Http/Requests/` — always implement `authorize()` and `rules()`
4. **Controller:** Create in `app/Http/Controllers/` — use constructor injection, Form Requests, policy auth
5. **Page:** Create in `resources/js/Pages/` — use `usePage()` for shared props, `useForm()` for forms
6. **Route:** Add to `routes/web.php` — wrap in feature flag if optional, always name routes
7. **Feature flag (if optional):** Add to `config/features.php` with env var default
8. **Tests:** Write Pest tests in `tests/Feature/` and `tests/Unit/` — see `tests/` structure

## Commands

```bash
composer dev           # Start Laravel + Vite + Queue
php artisan serve      # Laravel only
npm run dev            # Vite dev server
php artisan test       # Run tests (Pest, parallel)
npm run build          # Production build
scripts/init.sh        # First-time setup (configure project name, features)
```

## Conventions

**Backend:**
- Form Requests for validation (never inline `$request->validate()`)
- Services for business logic, controllers stay thin
- External API calls in Jobs only
- Constructor injection for dependencies

**Frontend:**
- UI primitives in `Components/ui/` (Radix + CVA + `cn()` from `lib/utils`)
- Theme via CSS variables (semantic tokens like `bg-background`, `text-foreground`)
- Forms: Inertia `useForm()` hook
- Icons: Lucide React only
- Shared props via `usePage()`, feature-gated UI via `features` prop

**Testing:**
- Framework: Pest (not PHPUnit) — use `it()` / `test()` syntax
- Parallel execution: `php artisan test --parallel`
- Frontend: Vitest + @testing-library/react (`npm test`)
- Database: SQLite in-memory for tests
- All auth pages have `.test.tsx` counterparts

**Migrations:**
- Always check before adding/dropping columns: `Schema::hasColumn()`
- New columns on existing tables: nullable or with default (never bare NOT NULL)
- Foreign keys: `->constrained()->cascadeOnDelete()`
- Feature-conditional migrations: `if (!config('features.*.enabled')) return;`

## Key Tables

- `users` — core user data + optional Stripe columns
- `social_accounts` — OAuth credentials (feature-gated)
- `user_settings` — key-value preferences
- `personal_access_tokens` — Sanctum API tokens
