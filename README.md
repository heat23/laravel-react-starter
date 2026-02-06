# Laravel React Starter

A production-ready starter template for building SaaS applications with Laravel, React, and TypeScript.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![React](https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black)
![TypeScript](https://img.shields.io/badge/TypeScript-5.8-3178C6?logo=typescript&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)

## Features

### Authentication
- [x] Email/password registration and login
- [x] Password reset flow
- [x] Email verification (feature-flagged)
- [x] Social auth via Google/GitHub (feature-flagged)
- [x] Remember me with configurable duration
- [x] Session regeneration on login
- [x] Rate limiting on all auth endpoints

### Billing
- [x] Stripe integration via Laravel Cashier (feature-flagged)
- [x] Pricing page with plan comparison
- [x] Billing portal for subscription management
- [x] Plan limits enforcement

### UI Components
- [x] Dark/light/system theme with persistence
- [x] Charts library (Area, Bar, Line, Pie) with theme-aware colors
- [x] File upload dropzone with drag-and-drop, validation, and preview
- [x] Notification dropdown with unread count badge (feature-flagged)
- [x] Radix UI primitives (Dialog, Popover, Dropdown, etc.)

### Developer Experience
- [x] Sanctum API token management (feature-flagged)
- [x] Request ID tracking across logs, responses, and Sentry
- [x] JSON error envelope for API routes
- [x] Audit logging (login, logout, registration)
- [x] Feature flags via environment variables
- [x] User settings (theme, timezone persistence)
- [x] Security headers middleware

## Quick Start

```bash
git clone https://github.com/your-org/laravel-react-starter.git
cd laravel-react-starter
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer dev
```

Visit `http://localhost:8000` to see the app.

## Feature Flags

Toggle optional modules via `.env` without code changes:

| Environment Variable | Default | Description |
|---------------------|---------|-------------|
| `FEATURE_BILLING` | `false` | Stripe billing, pricing page, billing portal |
| `FEATURE_SOCIAL_AUTH` | `false` | Google/GitHub OAuth login |
| `FEATURE_EMAIL_VERIFICATION` | `true` | Require email verification |
| `FEATURE_API_TOKENS` | `true` | Sanctum token management UI |
| `FEATURE_USER_SETTINGS` | `true` | Theme/timezone persistence |
| `FEATURE_NOTIFICATIONS` | `false` | In-app notification system |

## Architecture

**Stack:** Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4

**Models:** User, UserSetting, SocialAccount, AuditLog

**Services:**
- `AuditService` — activity logging with request ID context
- `PlanLimitService` — subscription limit enforcement
- `SocialAuthService` — OAuth provider abstraction

**Routes:**
- `routes/web.php` — Inertia pages (feature-gated)
- `routes/auth.php` — Authentication (Breeze + social auth)
- `routes/api.php` — Sanctum-protected API (user, settings, tokens, notifications)

## Project Structure

```
app/
  Http/
    Controllers/       # Thin controllers, constructor injection
    Middleware/         # RequestId, SecurityHeaders, HandleInertia
    Requests/           # Form Requests (validation)
  Models/              # Eloquent models
  Policies/            # Authorization (UserPolicy)
  Services/            # Business logic
config/
  features.php         # Feature flags
resources/js/
  Components/
    notifications/     # NotificationDropdown, NotificationItem
    ui/
      charts/          # AreaChart, BarChart, LineChart, PieChart
      file-upload/     # FileUpload, FilePreview
  Layouts/             # DashboardLayout
  Pages/               # Inertia pages
  types/               # TypeScript type definitions
routes/
  web.php              # Web routes
  api.php              # API routes
  auth.php             # Auth routes
tests/
  Feature/             # Integration tests (Pest)
  Unit/                # Unit tests (Pest)
deploy/                # Nginx, supervisor configs
scripts/               # Setup and deployment scripts
```

## Testing

```bash
# PHP tests (Pest, parallel)
php artisan test --parallel

# JavaScript tests (Vitest)
npm test

# E2E tests (Playwright)
npx playwright test

# Type checking
npx tsc --noEmit

# Linting
npm run lint

# Code style
./vendor/bin/pint --test
```

## Deployment

VPS-based deployment with scripts in `deploy/` and `scripts/`:

- `scripts/vps-setup.sh` — Initial server provisioning
- `scripts/vps-verify.sh` — Post-deploy verification
- `deploy/` — Nginx and supervisor configuration

## Contributing

See [CLAUDE.md](CLAUDE.md) for coding conventions, architecture decisions, and development guidelines.

## License

MIT
