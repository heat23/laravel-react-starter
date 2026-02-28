# Testing Guidelines

**CRITICAL:** These guidelines are MANDATORY for all code changes. AI assistants MUST follow these rules.

## Test-First Development (TDD)

### When TDD is REQUIRED

**Business logic, services, API endpoints, data transformations:**
1. Write test that describes desired behavior
2. Run test - MUST fail (red)
3. Write minimal code to pass
4. Run test - MUST pass (green)
5. Refactor if needed
6. Re-run test - still passes

**UI components, pages, visual changes:**
Test-after acceptable. Write component, then add tests.

### Example: Adding Feature Flag Override

**BAD (no test first):**
```php
// Directly modify FeatureFlagService
public function setGlobalOverride(string $flag, bool $enabled): void
{
    FeatureFlagOverride::updateOrCreate(...);
}
```

**GOOD (test first):**
```php
// 1. Write test
public function test_route_dependent_flags_respect_hard_floor(): void
{
    config(['features.billing.enabled' => false]);
    $service->setGlobalOverride('billing', true);

    $result = $service->resolveAll($user);

    expect($result['billing'])->toBeFalse(); // ← This MUST fail first
}

// 2. Run test → RED (fails as expected)
// 3. Write code to make it pass
// 4. Run test → GREEN
```

## Test Quality Requirements

### Every Test MUST Have

1. **Clear description** - Test name explains what behavior is being tested
2. **Arrange-Act-Assert structure** - Setup, execute, verify
3. **At least one assertion** - Tests without assertions are RISKY
4. **Isolated state** - No dependencies on other tests' side effects

### Forbidden Patterns

❌ **Tests that don't assert anything:**
```php
public function test_creates_user(): void
{
    User::factory()->create(); // ← No assertion!
}
```

✅ **Correct:**
```php
public function test_creates_user(): void
{
    $user = User::factory()->create();

    $this->assertDatabaseHas('users', [
        'email' => $user->email,
    ]);
}
```

❌ **Tests that only call mocks:**
```php
public function test_sends_webhook(): void
{
    $mock = Mockery::mock(WebhookService::class);
    $mock->shouldReceive('send')->once();
    // ← Only checks mock was called, not actual behavior
}
```

✅ **Correct:**
```php
public function test_sends_webhook(): void
{
    Http::fake();

    $service->send($endpoint, $payload);

    Http::assertSent(fn ($request) =>
        $request->url() === $endpoint->url &&
        $request->hasHeader('X-Webhook-Signature')
    );
}
```

## Contract Tests (Critical Paths)

**Location:** `tests/Contracts/`

Contract tests define IMMUTABLE behavior that MUST NOT break:
- Feature flag resolution precedence
- Billing subscription state transitions
- Webhook signature verification
- Auth/authorization flows

**Rules:**
1. DO NOT modify contract tests unless intentionally changing the contract
2. DO NOT skip or mark as risky
3. If contract test fails, FIX THE CODE, not the test
4. Add new contract test when defining new critical behavior

## Test Coverage Requirements

| Category | Minimum Coverage | Action on Failure |
|----------|-----------------|-------------------|
| Overall | 80% | Warn, block PR |
| Critical paths (auth, payments, webhooks) | 100% | BLOCK PR, refuse to ship |
| New code | 90% | Block PR |

**Check coverage:**
```bash
php artisan test --coverage --min=80
npm run test:coverage
```

## Regression Prevention Checklist

Before claiming ANY feature complete, run:

```bash
# 1. All tests pass
php artisan test --parallel

# 2. No new warnings
npm run lint

# 3. Build succeeds
npm run build

# 4. Static analysis passes
vendor/bin/phpstan analyse

# 5. Code style correct
vendor/bin/pint --test

# 6. Security audit clean
composer audit && npm audit

# 7. Contract tests pass
php artisan test --filter=Contract
```

**If any step fails:**
1. Fix the issue
2. Re-run full checklist
3. After 3 failed attempts, STOP and ask for help

## Test Organization

```
tests/
├── Contracts/         # Immutable behavior contracts
├── Feature/           # Integration tests (controllers, DB, full request)
├── Unit/              # Pure logic tests (services, models, utilities)
└── e2e/               # Playwright end-to-end tests
```

**Naming:**
- Feature tests: `{Feature}{Action}Test.php` (e.g., `UserRegistrationTest.php`)
- Unit tests: `{Class}Test.php` (e.g., `FeatureFlagServiceTest.php`)
- Contract tests: `{Domain}ContractTest.php` (e.g., `FeatureFlagContractTest.php`)

## When Tests Fail

### Diagnosis Protocol

1. **Read the failure message** - What assertion failed?
2. **Check recent changes** - What was modified?
3. **Isolate the test** - Run it solo: `php artisan test --filter=test_name`
4. **Add debug output** - `dump()` or `dd()` to inspect state
5. **Check database state** - `$this->assertDatabaseHas()` or manual SQL

### Fix Protocol

1. **Understand root cause** - Don't just make test pass, fix the bug
2. **Verify fix** - Run test 5 times to ensure it's not flaky
3. **Run related tests** - Ensure fix didn't break something else
4. **Run full suite** - Ensure no regressions

### DO NOT

❌ Change test to make it pass without understanding why it failed
❌ Skip or mark tests as risky to "fix" them
❌ Comment out failing assertions
❌ Add `sleep()` or `wait()` to fix timing issues (indicates real bug)

## Edge Cases to Always Test

Every feature MUST test:
- ✅ Happy path (success case)
- ✅ Validation failures (invalid input)
- ✅ Authorization failures (wrong user)
- ✅ Not found cases (missing resource)
- ✅ Soft-deleted relationships (if using SoftDeletes)
- ✅ Concurrent operations (if mutation)
- ✅ Rate limiting (if protected endpoint)

## Test Data Factories

**Always use factories, never hardcode:**

❌ **Bad:**
```php
User::create(['name' => 'Test', 'email' => 'test@example.com']);
```

✅ **Good:**
```php
User::factory()->create();
User::factory()->admin()->create();
User::factory()->withSubscription('pro')->create();
```

**Why:** Factories ensure valid data structure, reduce duplication, and adapt to schema changes.

## Database Transactions

**All feature tests MUST use transactions:**

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase; // ← Required for all Feature tests
}
```

**Why:** Prevents test pollution and ensures clean state per test.

## Mocking External Services

**ALWAYS mock external HTTP calls:**

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'stripe.com/*' => Http::response(['id' => 'ch_123'], 200),
]);
```

**DO NOT:**
- Make real API calls in tests (slow, flaky, costs money)
- Skip webhook signature verification in tests (defeats purpose)

## Mutation Testing

**Run mutation tests on critical code:**

```bash
vendor/bin/infection --filter=app/Services/BillingService.php
```

**Purpose:** Verify tests actually catch bugs (not just code coverage).

**Frequency:** Before deploying changes to critical paths (billing, auth, webhooks).

## CI/CD Integration

All these checks run automatically in CI (`.github/workflows/ci.yml`):
- PHP tests with coverage
- JS tests with coverage
- PHPStan static analysis
- Pint code style
- Security audit
- E2E smoke tests

**Local pre-commit hook** runs subset of checks (`.husky/pre-commit`).

## When to Ask for Help

**STOP and ask after:**
- 3 failed attempts to fix a test
- Test passes locally but fails in CI (environment issue)
- Contract test fails (indicates breaking change)
- Coverage drops below 80% after changes
- Test suite takes >2 minutes to run (performance issue)

---

**Remember:** Tests are the safety net. If tests are weak, regressions WILL happen.

## Edge Case Coverage Checklist

Every feature test MUST include these scenarios (if applicable):

- [ ] **Soft-deleted relationships:** Does code handle `$user->owner` when owner is soft-deleted?
- [ ] **Null relationships:** Does code handle `$subscription->user` being null after user deletion?
- [ ] **Unverified users:** Does route allow unverified users when `email_verification.enabled=false`?
- [ ] **Feature disabled:** Does route return 404 when feature flag is off?
- [ ] **Concurrent operations:** Does code prevent race conditions (use BillingService pattern)?
- [ ] **Empty collections:** Does page render correctly with 0 results?
- [ ] **Pagination edge cases:** Does page 1 show when page 999 requested?
- [ ] **Authorization edge cases:** Does user B's valid ID give 403 to user A?

**Example: Comprehensive Edge Case Coverage**

See `tests/Feature/Admin/AdminFeatureFlagTest.php` for reference — tests include:
- Active operation (enable global override)
- State transitions (disable after enabled)
- Removal operations (remove override)
- Protected resource handling (cannot override admin flag)
- Authorization (non-admin gets 403)
- Validation (unknown flag name returns error)
- Side effects (audit logging)
- Reason/metadata storage
