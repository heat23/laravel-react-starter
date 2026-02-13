# ADR 0001: Feature Flag Architecture

**Date:** 2026-02-13
**Status:** Active
**Deciders:** Development Team

## Context

The application uses a feature flag system to enable/disable major subsystems (billing, webhooks, admin, etc.). The system supports:
- Config-based defaults (`config/features.php`)
- Database overrides (global and per-user)
- Route-dependent flags (cannot override if routes not registered)

## Decision

### Route-Dependent Flags

Certain flags are "route-dependent" meaning:
1. If `env=false`, routes are **not registered** in `routes/web.php`
2. Database overrides **cannot** enable these flags (hard floor)
3. Examples: `billing`, `webhooks`, `admin`, `notifications`

**Rationale:** Security and stability. If routes aren't registered, enabling via DB override would cause 404 errors and confusion.

### Override Precedence

```
User-specific override > Global override > Config default
```

**Except** for route-dependent flags with `env=false`, which always return `false`.

### Protected Flags

The `admin` flag **cannot be overridden** at all (neither globally nor per-user) for security reasons.

## Consequences

### Positive
- Clear separation between feature toggles and infrastructure requirements
- Prevents misconfigurations that would break routing
- Security boundary for admin access

### Negative
- Complexity in understanding when overrides apply
- Must document route-dependent flags clearly

## Testing Requirements

Every feature flag change MUST include:
1. Unit test for `FeatureFlagService::resolveAll()` with route-dependent logic
2. Feature test for admin UI CRUD operations
3. Integration test for route registration with flag disabled

## Known Issues (2026-02-13)

**BLOCKER:** 10 tests failing related to route-dependent flag resolution
- `FeatureFlagServiceTest::it resolveAll respects route-dependent` - expects `notifications` to be false but resolves to true
- `AdminFeatureFlagTest` - 9 tests returning 302 instead of 200 JSON responses

**Root Cause:** Route-dependent flag logic regression in `FeatureFlagService::resolveAll()`

**Fix Required:** Restore hard floor check for route-dependent flags before applying DB overrides.

## References

- `app/Services/FeatureFlagService.php`
- `config/features.php`
- `routes/web.php` - Route registration logic
- `tests/Unit/Services/FeatureFlagServiceTest.php`
- `tests/Feature/Admin/AdminFeatureFlagTest.php`
