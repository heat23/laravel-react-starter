# Proactive Safeguards Summary

**Last Updated:** 2026-02-13

## Problem We're Solving

When AI handles planning, development, and testing, quality issues can slip through if proper safeguards aren't in place. The recent feature flag regression (10 failing tests) demonstrated this risk.

**Root Cause:** Reactive testing (running tests AFTER all code is written) doesn't catch issues until it's too late.

**Solution:** Proactive verification at EVERY phase (planning, implementation, verification).

---

## The Complete System

### Reactive Safeguards (Catch AFTER commit)

| Layer | When | What It Catches | Tool |
|-------|------|-----------------|------|
| **Pre-commit Hook** | Before commit | Style, tests, types | `.husky/pre-commit` |
| **CI/CD Pipeline** | On PR | All quality gates | `.github/workflows/ci.yml` |
| **Test Quality Monitor** | On demand | Weak tests | `scripts/test-quality-check.sh` |
| **Mutation Testing** | Before deploy | Ineffective tests | `vendor/bin/infection` |
| **Contract Tests** | In test suite | Breaking changes | `tests/Contracts/` |
| **Static Analysis** | In CI | Type errors | `vendor/bin/phpstan` |

**Problem:** By the time reactive safeguards catch issues, code is already written and time is wasted.

---

### Proactive Safeguards (Catch DURING development)

| Layer | When | What It Prevents | Tool/Doc |
|-------|------|------------------|----------|
| **Planning Checklist** | BEFORE code | Bad architecture, missing patterns | `docs/PLANNING_CHECKLIST.md` |
| **Implementation Guardrails** | DURING code | Anti-patterns, N+1, transactions | `docs/IMPLEMENTATION_GUARDRAILS.md` |
| **Prompt Templates** | At task start | Unclear requirements, skipped steps | `docs/AI_PROMPT_TEMPLATES.md` |
| **Real-Time Verification** | After each file | Broken tests, type errors | PHPStan + test watch mode |
| **TDD Micro-Cycle** | Per method | Logic bugs | Red-Green-Refactor |
| **Checkpoint Commits** | Every 15-30 min | Lost work, hard-to-debug issues | Git micro-commits |
| **ADR Reviews** | At planning | Violating architectural decisions | `docs/adr/` |

**Benefit:** Catch issues BEFORE they compound. Fix takes 5 minutes instead of 2 hours.

---

## How It Works: Example Flow

### Scenario: Add Email Notification When Subscription Expires

#### ❌ **OLD WAY (Reactive)**

1. **Plan:** "Add email notification" (2 min)
2. **Implement:** Write service, controller, notification class (45 min)
3. **Write tests:** Add tests at the end (20 min)
4. **Run tests:** Discover N+1 query, missing transaction, edge case bug (15 min)
5. **Fix issues:** Refactor code to fix (30 min)
6. **Re-test:** Run again, discover contract test broke (10 min)
7. **Fix contract:** Update feature flag logic (20 min)

**Total time:** 2 hours 22 minutes
**Issues found:** Late (after code written)

---

#### ✅ **NEW WAY (Proactive)**

**Phase 1: Planning (15 minutes)**

Use `PLANNING_CHECKLIST.md`:

1. **Search for similar implementations:**
   ```bash
   grep -r "Notification" app/
   # Found: SubscriptionConfirmed notification exists
   ```
   **Result:** Reuse existing pattern ✓

2. **Check for contract tests:**
   ```bash
   ls tests/Contracts/
   # Found: BillingContractTest exists
   ```
   **Result:** Must verify contract doesn't break ✓

3. **Read relevant ADRs:**
   ```bash
   grep -r "billing" docs/adr/
   # Found: ADR-0002 defines billing service requirements
   ```
   **Result:** Must use BillingService, not direct Eloquent ✓

4. **List edge cases:**
   - Soft-deleted user
   - Unverified email
   - Subscription already expired
   - Email bounce
   **Result:** 4 edge cases to test ✓

5. **Design architecture:**
   - Use existing `SubscriptionExpiringNotification`
   - Add to `BillingService::checkExpiringSubscriptions()`
   - Schedule command to run daily
   - Eager load `user` relationship
   **Result:** Clear implementation plan ✓

6. **Get user approval:**
   - Show design
   - Show edge cases
   - Get OK to proceed
   **Result:** Aligned with user ✓

---

**Phase 2: Implementation (30 minutes)**

Use `IMPLEMENTATION_GUARDRAILS.md`:

**Step 1: Write test FIRST (5 min)**
```php
public function test_sends_expiring_notification(): void
{
    Notification::fake();
    $user = User::factory()->withSubscription('pro')->create();
    $subscription = $user->subscription('pro');
    $subscription->trial_ends_at = now()->addDays(7);
    $subscription->save();

    $service->checkExpiringSubscriptions();

    Notification::assertSentTo($user, SubscriptionExpiringNotification::class);
}
```

**Run:** `php artisan test --filter=test_sends_expiring_notification`
**Result:** ❌ FAILS (method doesn't exist) - EXPECTED ✓

---

**Step 2: Minimal implementation (10 min)**
```php
public function checkExpiringSubscriptions(): void
{
    Subscription::with('user')  // ← Eager load (prevent N+1)
        ->whereBetween('trial_ends_at', [now()->addDays(7), now()->addDays(8)])
        ->each(function ($subscription) {
            Notification::send($subscription->user, new SubscriptionExpiringNotification($subscription));
        });
}
```

**Run:** `php artisan test --filter=test_sends_expiring_notification`
**Result:** ✅ PASSES ✓

**Verify immediately:**
```bash
vendor/bin/phpstan analyse app/Services/BillingService.php
# Result: No errors ✓

php artisan test --filter=BillingService
# Result: All 15 tests pass ✓
```

**Commit:** `git commit -m "Add checkExpiringSubscriptions method"`

---

**Step 3: Test edge cases (10 min)**
```php
// Add 4 more tests for edge cases
test_soft_deleted_user_doesnt_receive_notification()
test_unverified_email_doesnt_receive_notification()
test_already_expired_subscription_skipped()
test_handles_email_bounce()
```

**Run:** `php artisan test --filter=BillingService`
**Result:** ❌ 2 fail (soft-delete and unverified email not handled)

**Fix immediately:** Add guards
```php
->whereHas('user', fn ($q) => $q->whereNull('deleted_at')->whereNotNull('email_verified_at'))
```

**Re-run:** ✅ All pass ✓

**Commit:** `git commit -m "Add edge case handling for expiring notifications"`

---

**Step 4: Verify contract (3 min)**
```bash
php artisan test tests/Contracts/BillingContractTest.php
# Result: ✅ All pass (contract intact) ✓
```

---

**Step 5: Final verification (2 min)**
```bash
php artisan test --parallel
# ✅ 1040 passing

vendor/bin/phpstan analyse
# ✅ No errors

npm run lint
# ✅ Clean
```

**Result:** ✅ Feature complete, all gates pass ✓

---

### Comparison

| Approach | Time | Issues Found | Quality |
|----------|------|--------------|---------|
| **Old (Reactive)** | 2h 22min | Late (after code written) | Rework required |
| **New (Proactive)** | 45min | Early (incremental) | Clean first time |

**Time saved:** 1h 37min (68% faster)
**Bugs prevented:** N+1 query, missing transaction, edge cases, contract break

---

## Why Proactive Works Better

### 1. **Issues Found Earlier = Cheaper to Fix**

| When Found | Time to Fix | Impact |
|------------|-------------|--------|
| **During planning** | 2 minutes | Change design doc |
| **During TDD (red)** | 5 minutes | Adjust test |
| **After file change** | 10 minutes | Revert or fix small change |
| **After full feature** | 1 hour | Refactor entire feature |
| **After merge** | 4 hours | Coordinate with team, deploy fix |
| **In production** | 1 day | Incident response, rollback, post-mortem |

**Exponential cost increase.** Catch early = save exponentially.

---

### 2. **Planning Prevents Architecture Mistakes**

**Example: Feature Flag Test Failures**

**What happened:**
- AI modified `FeatureFlagService::resolveAll()`
- Didn't check for contract tests
- Didn't read ADR documenting route-dependent flags
- Broke immutable behavior
- 10 tests failed

**With proactive planning:**
```markdown
## Planning Checklist

1. Check for contract tests:
   ls tests/Contracts/
   # Result: FeatureFlagContractTest.php exists

2. Read ADR:
   cat docs/adr/0001-feature-flag-architecture.md
   # Result: Route-dependent flags have hard floor

3. Understand behavior:
   # Route-dependent flags CANNOT override if env=false
   # This is IMMUTABLE (contract test exists)

4. Design change:
   # Must preserve hard floor logic
   # Add new method, don't modify resolveAll()
```

**Result:** Contract preserved, 0 tests broken.

---

### 3. **TDD Prevents Logic Bugs**

**Example: N+1 Query**

**Without TDD:**
```php
// Write code first
public function getUsers()
{
    return User::all()->map(fn ($u) => [
        'name' => $u->name,
        'subscription' => $u->subscription->plan, // ← N+1
    ]);
}

// Test later
public function test_get_users()
{
    $response = $this->get('/users');
    $response->assertOk();
}
```
**Result:** Test passes, but N+1 query in production.

---

**With TDD:**
```php
// Test first
public function test_get_users_without_n_plus_1()
{
    User::factory()->count(10)->create();

    DB::enableQueryLog();
    $controller->getUsers();
    $queries = DB::getQueryLog();

    expect(count($queries))->toBeLessThan(3); // ← Fails if N+1
}

// Watch test fail (RED)
// Write code to pass (GREEN)
public function getUsers()
{
    return User::with('subscription')->get()->map(...); // ← Eager load
}
```
**Result:** N+1 prevented before merge.

---

### 4. **Real-Time Verification Prevents Accumulation**

**Without real-time checks:**
- Change 10 files
- Run tests at the end
- 15 tests fail
- Spend 2 hours debugging which change broke what

**With real-time checks:**
- Change file 1 → run tests → ✅ pass
- Change file 2 → run tests → ❌ 2 fail
- **STOP:** Fix file 2 immediately
- Change file 2 again → run tests → ✅ pass
- Continue...

**Result:** Never more than 1 broken file at a time.

---

## Enforcement Mechanisms

### For AI Assistants

**Claude Code must:**
1. Complete `PLANNING_CHECKLIST.md` BEFORE writing code
2. Show checklist results to user
3. Wait for approval before implementation
4. Run verification after EACH file change
5. Report results before continuing
6. STOP if any check fails (don't continue with broken code)

**Enforcement:**
- Built into prompt templates
- Referenced in CLAUDE.md
- User can verify AI followed process

---

### For Developers

**Automated enforcement:**
```bash
# Pre-commit hook runs automatically
.husky/pre-commit
# Blocks commit if:
# - Tests fail
# - PHPStan has errors
# - ESLint has errors
# - TypeScript has errors
# - Pint fails
```

**Manual enforcement:**
```bash
# Before claiming done
bash scripts/test-quality-check.sh

# If fails: fix before proceeding
```

---

## Metrics to Track Success

### Leading Indicators (Proactive)

**Weekly:**
- [ ] % of features planned with checklist (Target: 100%)
- [ ] % of commits that pass pre-commit hook first try (Target: >90%)
- [ ] Average time from "start" to "tests green" (Target: <30 min)
- [ ] Number of checkpoint commits per feature (Target: 3-5)

### Lagging Indicators (Reactive)

**Monthly:**
- [ ] Number of regressions caught in CI (Target: 0)
- [ ] Number of bugs found in production (Target: 0)
- [ ] Test coverage % (Target: >80%)
- [ ] Mutation score on critical paths (Target: >75%)

---

## What to Do When Things Fail

### If Planning Phase Skipped

**Symptom:** AI starts writing code without showing checklist
**Action:**
```
STOP. Before writing code, please complete the planning checklist
in docs/PLANNING_CHECKLIST.md and show your findings.
```

---

### If Tests Written After Code

**Symptom:** AI writes service, then writes tests
**Action:**
```
STOP. Tests must be written FIRST for business logic.
Please follow TDD: write test, watch it fail, then implement.
See docs/TESTING_GUIDELINES.md section "Test-First Development".
```

---

### If Verification Skipped

**Symptom:** AI claims feature complete without showing test results
**Action:**
```
Please run the quality gates and show results:

bash scripts/test-quality-check.sh

Show output here before I approve.
```

---

### If Contract Test Breaks

**Symptom:** Contract test fails after change
**Action:**
```
CRITICAL: Contract test failure indicates you broke immutable behavior.

1. Revert your changes: git reset --hard HEAD~1
2. Re-read the ADR: docs/adr/{relevant-adr}.md
3. Re-plan the change to preserve the contract
4. Get approval before re-implementing
```

---

## Success Criteria

**Project is "proactive-safe" when:**

✅ Every feature starts with planning checklist
✅ Every business logic method has test written FIRST
✅ Every file change is verified immediately
✅ Every 15-30 minutes has a commit
✅ Pre-commit hook passes first try >90% of time
✅ Contract tests never break unintentionally
✅ ADRs consulted before architectural changes
✅ Quality gates run before claiming done
✅ Mutation score >75% on critical paths
✅ Zero regressions reach production

---

## Quick Reference Card

**Print this and put it next to your monitor:**

```
┌─────────────────────────────────────────────────────┐
│  PROACTIVE DEVELOPMENT WORKFLOW                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│  BEFORE WRITING CODE:                               │
│  □ Complete planning checklist                      │
│  □ Search for existing patterns                     │
│  □ Check for contract tests                         │
│  □ Read relevant ADRs                               │
│  □ Get user approval                                │
│                                                     │
│  WHILE WRITING CODE:                                │
│  □ Write test FIRST (TDD)                           │
│  □ Watch test fail (RED)                            │
│  □ Write minimal code                               │
│  □ Watch test pass (GREEN)                          │
│  □ Run PHPStan on changed file                      │
│  □ Commit (every 15-30 min)                         │
│                                                     │
│  AFTER WRITING CODE:                                │
│  □ bash scripts/test-quality-check.sh               │
│  □ All tests pass?                                  │
│  □ Static analysis clean?                           │
│  □ Contract tests pass?                             │
│  □ Build succeeds?                                  │
│                                                     │
│  IF ANY STEP FAILS:                                 │
│  ❌ STOP IMMEDIATELY                                │
│  ❌ FIX BEFORE CONTINUING                           │
│  ❌ DON'T ACCUMULATE FAILURES                       │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

**Last Updated:** 2026-02-13
**Next Review:** After first feature using full proactive workflow
