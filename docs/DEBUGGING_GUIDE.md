# Test Failure Diagnosis Protocol

When a test fails after implementation, follow this checklist **in order:**

## Step 1: Classify the Failure

- **Type mismatch** (expected array, got object): Check Inertia prop structure
- **Database state** (expected record not found): Check factory relationships, soft deletes
- **Timing** (Promise resolved too early): Check async contract (Inertia router is fire-and-forget)
- **Cache** (stale data): Did you invalidate AdminCacheKey after mutation?
- **Feature flag** (route 404): Is the feature enabled in phpunit.xml or TestCase?

## Step 2: Common Root Causes by Test Type

### Feature test redirects to unexpected route
1. Check middleware stack (especially `verified`, `onboarding.completed`)
2. Check authorization in controller (policy, manual auth checks)
3. Check feature flag state in test
4. Check if user is soft-deleted (use `withTrashed()` if needed)

### Unit test returns wrong value
1. Check if dependent methods are mocked correctly
2. Check if relationships are loaded (call `->load()` before accessing)
3. Check if cache is returning stale value (call `Cache::flush()` in beforeEach)
4. Check if config values match expectations

### Integration test with external service
1. Verify mock/fake is called BEFORE model creation
2. Verify job is dispatched (Queue::fake()) not executed synchronously
3. Verify webhook signature format matches real provider format

## Step 3: Fixes to NEVER Make

- Remove assertion to make test pass
- Change assertion operator to weaker version (`toBe` → `not->toBeNull`)
- Add `sleep()` or `usleep()` to fix timing
- Disable middleware in test without understanding why it's failing
- Use `$this->withoutExceptionHandling()` to pass 500 errors

## Step 4: Fixes That Are Usually Correct

- Eager load relationships before accessing them
- Invalidate cache keys after mutations
- Add `withTrashed()` to queries that need soft-deleted records
- Use `assertSessionHas()` for flash messages, not Inertia assertions
- Mock Notification facade BEFORE creating models that dispatch events
