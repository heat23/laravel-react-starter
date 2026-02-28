# ADR 0003: Admin Cache Invalidation Strategy

## Status
Accepted

## Context
Admin dashboard pages aggregate statistics (user counts, subscription distributions, token stats, webhook stats) that are expensive to compute on every request. Caching is necessary, but stale data in admin dashboards was a recurring bug class — mutations (user toggle, subscription change, token CRUD) didn't always invalidate the right cache keys.

## Decision
1. All admin cache keys are defined in the `AdminCacheKey` enum with a default 5-minute TTL
2. Cache invalidation is centralized in `CacheInvalidationManager` with domain-specific methods (`invalidateBilling()`, `invalidateWebhooks()`, `invalidateDashboard()`, etc.)
3. Controllers call the appropriate invalidation method after mutations
4. `AdminCacheKey::flushAll()` clears everything (used when global feature flag overrides change)

## Consequences

### Positive
- Single source of truth for cache keys (enum prevents typos)
- Domain-grouped invalidation prevents forgetting related keys
- 5-minute TTL provides acceptable staleness for admin dashboards

### Negative
- Adding a new cached stat requires adding to the enum AND adding invalidation calls to relevant controllers
- Cache-aside pattern means first request after invalidation is slow

### Testing Requirements
- `CacheInvalidationManagerTest` verifies each domain method clears correct keys
- `SoftDeleteCacheConsistencyTest` verifies mutations trigger invalidation

## References
- `app/Enums/AdminCacheKey.php` — cache key definitions
- `app/Services/CacheInvalidationManager.php` — centralized invalidation
