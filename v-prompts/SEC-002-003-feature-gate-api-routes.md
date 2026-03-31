# SEC-002/003: Feature-gate notification and webhook API routes

## Problem
In `routes/api.php`, notification routes (line 50) and webhook CRUD routes (line 67) are always registered regardless of their feature flags. This exposes endpoints when features are disabled.

## Fix
Wrap both route groups in feature flag checks.

## Prompt
```
/v In routes/api.php, wrap the notification routes (lines 50-55) in if (config('features.notifications.enabled')) and the webhook routes (lines 67-79) in if (config('features.webhooks.enabled')). This matches the pattern used in routes/web.php and routes/admin.php for feature-gated routes.
```
