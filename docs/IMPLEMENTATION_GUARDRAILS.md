# Implementation Guardrails

**CRITICAL:** These rules apply DURING implementation to catch issues immediately.

## Real-Time Verification (After Each File Change)

### 1. After Modifying Service Layer

**Immediately run:**
```bash
# Static analysis on changed file
vendor/bin/phpstan analyse app/Services/{ChangedService}.php

# Run related tests
php artisan test --filter={ServiceName}Test
```

**Check:**
- [ ] No PHPStan errors
- [ ] All existing tests still pass
- [ ] New tests written for new methods

### 2. After Modifying Controller

**Immediately run:**
```bash
# Check routes still register
php artisan route:list | grep {controller_name}

# Run controller tests
php artisan test --filter={ControllerName}Test

# Check for N+1 queries
php artisan test --filter={ControllerName}Test --profile
```

**Check:**
- [ ] Routes registered correctly
- [ ] Tests pass
- [ ] No N+1 query warnings in test output

### 3. After Modifying Database Migration

**Immediately run:**
```bash
# Test migration up
php artisan migrate:fresh --seed

# Test migration down
php artisan migrate:rollback

# Re-run migration
php artisan migrate
```

**Check:**
- [ ] Migration runs without errors
- [ ] Migration is reversible (down works)
- [ ] Seeders still work after migration

### 4. After Modifying Form Request

**Immediately run:**
```bash
# Run validation tests
php artisan test --filter={RequestName}Test

# Check controller uses the request
grep -r "{RequestName}" app/Http/Controllers/
```

**Check:**
- [ ] Validation tests pass
- [ ] Controller injects FormRequest (not inline validation)
- [ ] All rules have error messages

### 5. After Modifying Frontend Component

**Immediately run:**
```bash
# Run component tests
npm test -- {ComponentName}

# Check TypeScript
npx tsc --noEmit

# Run dev server and visually verify
npm run dev
```

**Check:**
- [ ] Component tests pass
- [ ] No TypeScript errors
- [ ] Component renders correctly (visual check)
- [ ] Component works in dark mode

### 6. After Modifying Feature Flag Logic

**Immediately run:**
```bash
# Run contract tests
php artisan test tests/Contracts/FeatureFlagContractTest.php

# Run all feature flag tests
php artisan test --filter=FeatureFlag

# Check routes registration
php artisan route:list | head -50
```

**Check:**
- [ ] Contract tests pass (CRITICAL)
- [ ] All feature flag tests pass
- [ ] Routes correctly gated by feature flags
- [ ] If the flag has a prerequisite that would break the feature (not just degrade it), the dependency is registered in `FeatureFlagService::HARD_DEPENDENCIES` with test coverage — not just documented
- [ ] If the flag is `billing.tax_enabled`: verify BOTH `FEATURE_BILLING_TAX` and `BILLING_TAX_CONFIRM_COMPLIANT` are set to true, and that Stripe Tax is registered in every applicable jurisdiction (stripe.com/tax) with tax codes assigned per product/price. The two-key gate exists so a single checklist oversight can't ship uncollected-tax liability to production.

---

## Continuous Feedback Loops

### Micro-Loop (Every 5-10 Minutes)

**Save work checkpoint:**
```bash
git add .
git commit -m "WIP: {what you just changed}"
```

**Why:** If something breaks, you can revert to last good state.

### Mini-Loop (Every File Saved)

**IDE/Editor should show:**
- TypeScript errors (if using VS Code with TypeScript extension)
- ESLint warnings (if using VS Code with ESLint extension)
- PHPStan errors (if using Laravel IDE helper)

**Configure:**
```json
// .vscode/settings.json
{
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true
  },
  "php.validate.enable": true,
  "php.suggest.basic": false
}
```

### Small-Loop (Every Feature Complete)

**Before moving to next feature:**
```bash
# Run full test suite
php artisan test --parallel
npm test

# Check static analysis
vendor/bin/phpstan analyse

# Check code style
vendor/bin/pint --test

# Verify build
npm run build
```

---

## Pattern Recognition (Catch Anti-Patterns Early)

### Anti-Pattern Detectors

**1. N+1 Query Detector**

**Watch for:**
```php
// Anti-pattern
foreach ($users as $user) {
    echo $user->profile->name; // ← Lazy loads profile for each user
}
```

**Fix immediately:**
```php
// Correct
$users = User::with('profile')->get();
foreach ($users as $user) {
    echo $user->profile->name;
}
```

**Detection:**
```bash
# Enable query logging in tests
DB::enableQueryLog();
# ... run code
$queries = DB::getQueryLog();
if (count($queries) > 10) {
    dump($queries); // ← Shows N+1 if present
}
```

**2. Missing Transaction Detector**

**Watch for:**
```php
// Anti-pattern
public function transferFunds($from, $to, $amount)
{
    $from->decrement('balance', $amount); // ← If this succeeds...
    $to->increment('balance', $amount);    // ← ...but this fails, data is inconsistent
}
```

**Fix immediately:**
```php
// Correct
public function transferFunds($from, $to, $amount)
{
    DB::transaction(function () use ($from, $to, $amount) {
        $from->decrement('balance', $amount);
        $to->increment('balance', $amount);
    });
}
```

**Detection:** Code review checklist
- [ ] Multi-table mutations wrapped in `DB::transaction()`

**3. Missing Eager Loading Detector**

**Watch for:**
```php
// Anti-pattern
$users = User::all();
return $users->map(fn ($u) => [
    'name' => $u->name,
    'subscription' => $u->subscription->plan, // ← Lazy load
]);
```

**Fix immediately:**
```php
// Correct
$users = User::with('subscription')->get();
```

**Detection in tests:**
```php
// Add to test
DB::enableQueryLog();
$controller->index();
$queries = DB::getQueryLog();

expect(count($queries))->toBeLessThan(5, 'Too many queries - N+1 detected');
```

**4. Missing Null Check Detector (SoftDeletes)**

**Watch for:**
```php
// Anti-pattern
return $model->user->name; // ← Crashes if user is soft-deleted
```

**Fix immediately:**
```php
// Correct
return $model->user?->name ?? '[Deleted User]';
```

**Detection:** PHPStan rule (already configured)

**5. Missing Feature Gate Detector**

**Watch for:**
```php
// Anti-pattern in route file
Route::get('/billing', [BillingController::class, 'index']);
```

**Fix immediately:**
```php
// Correct
if (config('features.billing.enabled')) {
    Route::get('/billing', [BillingController::class, 'index']);
}
```

**Detection:**
```bash
# Check route file for ungated routes
grep -A 2 "Route::" routes/web.php | grep -v "if (config"
```

**6. Hardcoded Config Detector**

**Watch for:**
```php
// Anti-pattern
$maxTokens = 10; // ← Hardcoded
```

**Fix immediately:**
```php
// Correct
$maxTokens = config('limits.max_tokens', 10);
```

**Detection:**
```bash
# Find magic numbers
grep -rn "[^0-9]10[^0-9]" app/ | grep -v "// " | grep -v "test"
```

---

## Incremental Testing (Test As You Go)

### Red-Green-Refactor Micro-Cycle

**For EVERY new method:**

**1. Red (Write failing test):**
```php
public function test_new_feature(): void
{
    $result = $service->newMethod();

    expect($result)->toBeTrue(); // ← Fails because method doesn't exist
}
```

**Run:** `php artisan test --filter=test_new_feature`
**Expect:** ❌ FAILS (method doesn't exist)

**2. Green (Minimal implementation):**
```php
public function newMethod(): bool
{
    return true; // ← Just enough to pass
}
```

**Run:** `php artisan test --filter=test_new_feature`
**Expect:** ✅ PASSES

**3. Refactor (Improve without breaking tests):**
```php
public function newMethod(): bool
{
    // Add real logic
    $result = $this->complexCalculation();
    return $result;
}
```

**Run:** `php artisan test --filter=test_new_feature`
**Expect:** ✅ STILL PASSES

**Time per cycle:** 2-5 minutes
**Benefit:** Never more than 5 minutes from working state

---

## Checkpoint Commits (Revertible History)

### Commit Strategy

**Instead of:**
```bash
# Work for 2 hours
git add .
git commit -m "Added feature"
```

**Do this:**
```bash
# Every 15-30 minutes
git add app/Services/MyService.php
git commit -m "Add MyService::calculateTotal method"

# 15 minutes later
git add tests/Unit/MyServiceTest.php
git commit -m "Add tests for MyService::calculateTotal"

# 15 minutes later
git add app/Http/Controllers/MyController.php
git commit -m "Wire MyService into MyController"
```

**Benefits:**
- If you break something, revert to last good commit
- Clear history shows progression
- Easy to find where bug was introduced

**Commit message format:**
```
{Action} {what} [{optional: why}]

Examples:
✅ "Add BillingService::cancelSubscription method"
✅ "Fix N+1 query in UserController::index"
✅ "Refactor FeatureFlagService for readability"
❌ "stuff" (too vague)
❌ "WIP" (no context)
```

---

## Live Monitoring (Watch Mode)

### 1. Test Watcher (Auto-Run Tests)

**Terminal 1:**
```bash
php artisan test --watch
```

**Terminal 2:**
```bash
npm test -- --watch
```

**Benefit:** Tests re-run automatically on file save. Instant feedback.

### 2. Type Checker (Auto-Check Types)

**Terminal 3:**
```bash
npx tsc --noEmit --watch
```

**Benefit:** TypeScript errors appear immediately.

### 3. Static Analyzer (Auto-Check PHP)

**Terminal 4:**
```bash
# Watch mode for PHPStan (requires phpstan-watch package)
# OR: Run manually after each save
while inotifywait -e modify app/; do vendor/bin/phpstan analyse; done
```

**Benefit:** Catch type errors immediately.

---

## Pair Programming with AI (Verification Dialog)

### After Each Significant Change

**AI should ask:**
```
I just modified {file}. Let me verify:
- [ ] Tests pass: php artisan test --filter={TestName}
- [ ] Static analysis: vendor/bin/phpstan analyse {file}
- [ ] No new warnings: npm run lint

Running checks now...

Results:
✅ Tests: PASS (3 tests, 12 assertions)
✅ PHPStan: No errors
⚠️  ESLint: 1 new warning (unused variable)

Fixing ESLint warning...
✅ ESLint: Clean

Ready to proceed to next step.
```

**Benefit:** AI self-checks before moving forward.

---

## Risk-Based Gating (Stop Early if High Risk)

### Risk Assessment Matrix

**Before implementing, assess risk:**

| Change Type | Risk Level | Required Checks Before Proceeding |
|-------------|-----------|-----------------------------------|
| New feature (isolated) | LOW | Tests pass, static analysis clean |
| Modify existing service | MEDIUM | Tests pass, contract tests pass, mutation test 75%+ |
| Modify auth/billing/webhooks | HIGH | All above + manual verification + second review |
| Database migration (prod) | HIGH | Two-phase plan, backup verified, rollback tested |
| Breaking API change | CRITICAL | Version bump, deprecation notice, migration guide |

**If MEDIUM or above:**
- [ ] Stop and get user confirmation before proceeding
- [ ] Run extra verification (mutation tests, manual testing)
- [ ] Document decision in ADR

---

## Example: Safe Implementation Flow

### Scenario: Add email notification when user subscribes

**Step 1: Plan (5 minutes)**
```markdown
- [ ] Check for existing notification patterns
- [ ] Check for similar email templates
- [ ] List edge cases (unverified email, bounces)
```

**Step 2: Write Test First (5 minutes)**
```php
public function test_sends_email_on_subscription(): void
{
    Notification::fake();
    $user = User::factory()->create();

    $service->subscribe($user, 'pro');

    Notification::assertSentTo($user, SubscriptionConfirmed::class);
}
```

**Run:** ❌ FAILS (notification doesn't exist)

**Step 3: Minimal Implementation (10 minutes)**
```php
// Create notification class
Notification::send($user, new SubscriptionConfirmed());
```

**Run:** ✅ PASSES

**Step 4: Verify (2 minutes)**
```bash
php artisan test --filter=test_sends_email_on_subscription
vendor/bin/phpstan analyse app/Services/BillingService.php
```

**Step 5: Commit (1 minute)**
```bash
git add .
git commit -m "Add email notification on subscription"
```

**Total time:** 23 minutes
**Times verified:** 3 (test red, test green, final check)

---

## Red Flags (Stop Immediately)

**If you see any of these, STOP and investigate:**

🚩 **Test passes but you didn't write code yet** → Test is wrong
🚩 **Test passes without assertions** → Test is useless (risky)
🚩 **Code works locally but not in CI** → Environment issue
🚩 **Need to skip test to make build pass** → Real bug, not test issue
🚩 **Adding `sleep()` to fix timing issue** → Race condition exists
🚩 **Copying 50+ lines of code** → Refactor into shared function
🚩 **Adding 3rd OR condition to if statement** → Refactor to strategy pattern
🚩 **Method has 5+ parameters** → Create DTO or value object
🚩 **Function is 100+ lines** → Break into smaller functions

---

## Enforcement

**For AI Assistants:**

After EVERY file modification:
1. Run relevant tests immediately
2. Run static analysis on changed file
3. Report results before continuing
4. If any check fails, fix immediately (don't accumulate failures)

**For Developers:**

Set up IDE to:
1. Auto-run tests on save (if fast enough)
2. Show inline PHPStan/ESLint errors
3. Auto-format on save (Pint, Prettier)

---

**Last Updated:** 2026-02-13
**Review:** Weekly or after any regression
