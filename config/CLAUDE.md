# Configuration

## Key Files
- `features.php` - Feature flags (billing, social_auth, etc.)
- `plans.php` - Subscription tiers and limits
- `services.php` - OAuth + Google Analytics ID
- `sentry.php` - Error tracking configuration
- `mail.php` - Mail settings (Mailpit local, SMTP production)

## Feature Flags
All features default via env vars:
```php
'billing' => ['enabled' => env('FEATURE_BILLING', false)]
```

## Monitoring
- **Google Analytics:** `services.google.analytics_id` (GOOGLE_ANALYTICS_ID)
- **Sentry:** `sentry.dsn` (SENTRY_LARAVEL_DSN)

## Mail Setup
- **Local:** SMTP to Mailpit on port 1025 (Laravel Herd default)
- **Production:** Configure MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD

## Environment Variables
See `.env.example` for all available options.
- Feature flags: `FEATURE_*`
- Analytics: `GOOGLE_ANALYTICS_ID`
- Sentry: `SENTRY_LARAVEL_DSN`, `VITE_SENTRY_DSN`
