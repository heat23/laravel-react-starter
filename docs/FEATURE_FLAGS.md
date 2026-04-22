# Feature Flags

## Dependency Graph

### Hard Dependencies (will break if dependency disabled)

**Runtime-enforced** by `FeatureFlagService::HARD_DEPENDENCIES` — if a listed dep is off, the dependent flag resolves to `false` and a `Log::warning(...)` is emitted. The warning is de-duped per-process (keyed by `"{flag}:{dependency}"`) so a misconfigured production doesn't flood logs on every Inertia request; workers re-emit after restart. The admin dashboard surfaces the same gate via `blocked_by_dependency` so the operator sees the runtime-accurate `effective` value. This prevents the "silent broken" bug class where a flag is on but its prerequisite is misconfigured.

- `onboarding` → requires `user_settings` (completion timestamp lives in `user_settings`; without it the wizard can't record progress) — **enforced**

**Documented but not runtime-enforced** (either a soft fallback exists, or the dependency lives at a different layer):

- `billing` → Stripe webhooks are registered by Cashier at `/stripe/webhook` regardless of `FEATURE_WEBHOOKS`. The `webhooks` flag only gates the user-configurable outgoing-webhook system.
- `two_factor` → uses `user_settings` for enrollment preference when available; falls back gracefully when `user_settings` is off (documented soft dep).
- `admin` → protected flag: cannot be overridden via DB (FeatureFlagService enforces hard floor when env=false).

### Adding a Hard Dependency

1. Add an entry to `FeatureFlagService::HARD_DEPENDENCIES` (`'dependent' => ['prerequisite', ...]`).
2. Add coverage in `tests/Unit/Services/FeatureFlagServiceTest.php` ("resolves X to false when Y is disabled") and `tests/Feature/FeatureFlagDependencyTest.php` if the integration path matters.
3. Document the dep above with a one-line justification.
4. Do NOT register a soft dependency here — reserve this for "will break if dep is off," not "degrades gracefully."

### Soft Dependencies (graceful degradation)
- `notifications` + `webhooks` = webhook delivery notifications (webhook failures still logged to database)
- `billing` + `email_verification` = prevents subscriptions from unverified users (check in SubscriptionController)
- `social_auth` + `email_verification` = OAuth accounts start pre-verified (handled in SocialAuthService)

### Implicit Env Var Dependencies (auto-detected, not config-driven)
- `social_auth` provider availability is determined by env var presence at runtime — `GOOGLE_CLIENT_ID` enables Google OAuth, `GITHUB_CLIENT_ID` enables GitHub OAuth. Setting `FEATURE_SOCIAL_AUTH=true` without either env var renders no social login buttons. The feature flag is a master switch; individual providers are controlled by env vars only.
- `onboarding` stores completion state in `user_settings` table (hard dependency above), but the completion timestamp key (`onboarding_completed_at`) is a convention, not enforced by a foreign key — disabling `user_settings` silently drops onboarding completion tracking.

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
