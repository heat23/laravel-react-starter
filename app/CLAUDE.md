# Backend (app/)

## Structure
- `Http/Controllers/` - Request handlers
- `Http/Requests/` - Form validation
- `Models/` - Eloquent models
- `Services/` - Business logic

## Services
- `AuditService` - Security event logging (login, logout, register)
- `PlanLimitService` - Subscription limits and trial management
- `SessionDataMigrationService` - Migrate anonymous data on signup
- `SocialAuthService` - OAuth provider handling

## Patterns
- Use Form Requests for validation
- Services for complex logic
- Jobs for external API calls
- Cache expensive operations

## Feature Guards
Check feature flags before processing:
```php
if (!config('features.social_auth.enabled')) {
    abort(404);
}
```

## User Model
- `MustVerifyEmail` conditional on feature flag
- `HasApiTokens` for Sanctum
- `getSetting()`/`setSetting()` for user preferences
