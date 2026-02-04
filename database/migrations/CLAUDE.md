# Migrations

## Naming
`YYYY_MM_DD_HHMMSS_description.php`

## Feature Migrations
Some migrations are conditional:
```php
if (!config('features.social_auth.enabled')) {
    return;
}
```

## Key Tables
- `users` - Core user data + optional Stripe columns
- `social_accounts` - OAuth credentials (optional)
- `user_settings` - Key-value preferences
- `personal_access_tokens` - Sanctum API tokens

## Foreign Keys
Always cascade on delete for user-owned data.
