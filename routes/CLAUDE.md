# Routes

## Files
- `web.php` - Page routes
- `auth.php` - Authentication routes
- `api.php` - API endpoints (Sanctum)

## Feature Gating
```php
if (config('features.billing.enabled')) {
    Route::get('/billing', ...);
}
```

## Middleware
- `auth` - Requires login
- `verified` - Requires email verification
- `guest` - Only non-authenticated

## Naming
Always name routes for `route()` helper usage.
