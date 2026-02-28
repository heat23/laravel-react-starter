# Feature Flags

## Dependency Graph

### Hard Dependencies (will break if dependency disabled)
- `onboarding` → requires `user_settings` (stores completion timestamp in user_settings table)
- `billing` → requires `webhooks` for Stripe webhooks (auto-enabled in routes/api.php)
- `two_factor` → requires `user_settings` for enrollment preference (optional fallback exists)
- `api_docs` → requires `api_tokens` (documents token endpoints)
- `admin` → protected flag: cannot be overridden via DB (FeatureFlagService enforces hard floor when env=false)

### Soft Dependencies (graceful degradation)
- `notifications` + `webhooks` = webhook delivery notifications (webhook failures still logged to database)
- `billing` + `email_verification` = prevents subscriptions from unverified users (check in SubscriptionController)
- `social_auth` + `email_verification` = OAuth accounts start pre-verified (handled in SocialAuthService)

## Testing Feature Flag Combinations

When adding a new feature-gated feature, test these scenarios:
1. Feature ON, dependency OFF → should fail gracefully or show "requires X feature" message
2. Feature ON, dependency ON → full functionality
3. Feature OFF → routes don't register, nav links hidden, API returns 404

## Adding a New Feature Flag

1. Add to `config/features.php` with env var and `enabled` key
2. Document in CLAUDE.md "Feature Flags" table
3. Add dependency to this graph if applicable
4. Gate routes with `if (config('features.X.enabled'))` in routes files (boot-time) OR `abort_unless(feature_enabled('X', $user), 404)` in controller constructor (runtime) — see pattern guide below
5. Gate nav links with `{features.X && ...}` in TSX
6. Add test: `it('route returns 404 when feature disabled')`

## Gating Patterns (two approaches)

| Approach | When to use | Example features |
|----------|-------------|------------------|
| **Boot-time** (`if (config(...))` in routes) | Global on/off, no per-user overrides needed, or infrastructure-dependent (Stripe, admin panel) | `billing`, `admin`, `api_tokens`, `email_verification` |
| **Runtime** (`feature_enabled()` in controller) | Per-user overrides via DB needed, or gradual rollout | `notifications`, `webhooks`, `two_factor`, `social_auth` |

Boot-time gated routes are not registered when disabled — they return 404 at the router level. Runtime gated routes are always registered but controllers call `abort_unless(feature_enabled(...), 404)` which resolves per-user DB overrides via `FeatureFlagService`. Both approaches are valid; choose based on whether per-user granularity is needed.
