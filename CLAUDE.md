# Laravel React Starter Template

## Stack
Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4

## Feature Flags
Check `config/features.php` before implementing:
- `billing` - Stripe integration (Cashier)
- `social_auth` - Google/GitHub OAuth
- `email_verification` - Email verification flow
- `api_tokens` - Sanctum token management
- `user_settings` - Key-value user preferences

## Environment Setup

| Environment | APP_ENV | Mail | Analytics | Sentry |
|-------------|---------|------|-----------|--------|
| Local | local | Mailpit (1025) | Disabled | Disabled |
| Preview | preview | SMTP configured | Optional | Enabled |
| Production | production | SMTP configured | Enabled | Enabled |

**Local (Laravel Herd):** Uses Mailpit on port 1025, view at http://localhost:8025

## Monitoring & Analytics
- **Google Analytics:** Set `GOOGLE_ANALYTICS_ID` (production only, auto-injected via `@production`)
- **Sentry:** Install `sentry/sentry-laravel`, set `SENTRY_LARAVEL_DSN`
- **Frontend Sentry:** Set `VITE_SENTRY_DSN` for React error tracking

## Conventions

### Backend
- Controllers in `app/Http/Controllers/`
- Form requests for validation
- Services for business logic
- External API calls in Jobs, never in request lifecycle

### Frontend
- Pages in `resources/js/Pages/`
- UI primitives in `Components/ui/` (Radix + CVA)
- Use `cn()` from `lib/utils` for class merging
- Theme via CSS variables, not hardcoded colors

### Routes
- Feature-gated with `if (config('features.*.enabled'))`
- Auth routes in `routes/auth.php`
- API routes use Sanctum middleware

## Commands
```bash
composer dev           # Start Laravel + Vite + Queue
php artisan serve      # Start Laravel only
npm run dev            # Vite dev server
php artisan test       # Run tests (Pest, parallel)
npm run build          # Production build
```

## Post-Clone
Run `scripts/init.sh` to configure project name, features, and initialize.
