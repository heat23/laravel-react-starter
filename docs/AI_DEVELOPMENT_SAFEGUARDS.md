# AI Development Safeguards

**Purpose:** Prevent regressions when using AI for planning, development, and testing.

## Problem Statement

When AI handles all development phases, quality issues can slip through if proper safeguards aren't in place. The recent feature flag test failures (10 tests) demonstrate this risk.

## Multi-Layer Defense Strategy

### Layer 1: Pre-Commit Hooks (Immediate Feedback)

**File:** `.husky/pre-commit`

**Blocks commits if:**
- Code style fails (Pint)
- Static analysis fails (PHPStan)
- Tests fail (PHP + JS)
- Linting fails (ESLint)
- TypeScript errors exist

**Bypass:** Not recommended. Override with `git commit --no-verify` only in emergencies.

**Installation:**
```bash
npm install husky --save-dev
npx husky init
chmod +x .husky/pre-commit
```

### Layer 2: Static Analysis (Catch Bugs Before Tests)

**Tool:** PHPStan with Larastan (Laravel-specific rules)
**Config:** `phpstan.neon`
**Level:** 5 (production-grade, not pedantic)

**Catches:**
- Type errors
- Undefined methods/properties
- Dead code
- Logic errors

**Run:**
```bash
vendor/bin/phpstan analyse
```

**CI Integration:** Runs on every PR (`.github/workflows/ci.yml`)

### Layer 3: Contract Tests (Immutable Behavior)

**Location:** `tests/Contracts/`

**Purpose:** Define critical behavior that MUST NOT change without explicit approval.

**Example:** `FeatureFlagContractTest`
- Route-dependent flags cannot override env=false
- User overrides take precedence over global
- Admin flag cannot be overridden at all

**Rules:**
1. DO NOT modify contract tests without understanding impact
2. If contract test fails, FIX THE CODE, not the test
3. Add new contract test when defining new critical behavior

### Layer 4: Test Quality Monitoring

**Script:** `scripts/test-quality-check.sh`

**Checks:**
- Tests without assertions (risky tests)
- Skipped tests (forgotten work)
- Coverage below 80%
- Tests marked as risky

**Run:**
```bash
bash scripts/test-quality-check.sh
```

### Layer 5: Mutation Testing (Verify Tests Catch Bugs)

**Tool:** Infection
**Config:** `infection.json.dist` (auto-generated on first run)

**Purpose:** Mutate code and verify tests fail (if tests still pass, they're weak).

**Run on critical paths before deploying:**
```bash
vendor/bin/infection --filter=app/Services/BillingService.php
vendor/bin/infection --filter=app/Services/FeatureFlagService.php
```

**Frequency:** Before deploying changes to auth, billing, webhooks, feature flags.

### Layer 6: CI/CD Quality Gates (Enforce Standards)

**File:** `.github/workflows/ci.yml`

**Jobs:**
1. **PHP Tests** - Parallel execution, coverage report
2. **JS Tests** - Vitest with coverage
3. **Build Verification** - TypeScript + ESLint + production build
4. **Code Quality** - Pint + **PHPStan** (NEW)
5. **Security Audit** - Composer + NPM vulnerabilities

**New Addition:** PHPStan now runs in CI on every PR.

**Status Checks:** All jobs MUST pass before merge.

### Layer 7: Architectural Decision Records (ADRs)

**Location:** `docs/adr/`

**Purpose:** Document WHY decisions were made, so future changes don't break assumptions.

**Example:** `0001-feature-flag-architecture.md`
- Explains route-dependent flag logic
- Documents known issues (10 failing tests)
- Defines testing requirements
- Provides references to relevant files

**When to Create ADR:**
- New architectural pattern introduced
- Complex business rule implemented
- Security boundary defined
- Breaking change made

**Template:**
```markdown
# ADR NNNN: Title

**Date:** YYYY-MM-DD
**Status:** Active | Deprecated | Superseded

## Context
[Why this decision is needed]

## Decision
[What was decided]

## Consequences
[Positive and negative impacts]

## Testing Requirements
[How to verify this behavior]

## References
[Related files, docs, tickets]
```

### Layer 8: Testing Guidelines (AI Instructions)

**File:** `docs/TESTING_GUIDELINES.md`

**Mandatory rules for AI assistants:**
- TDD required for business logic
- Contract tests MUST NOT be modified casually
- Every test must have assertions
- Coverage minimums enforced
- Regression checklist before claiming done

**AI Prompts Should Reference:**
```
"Follow the testing guidelines in docs/TESTING_GUIDELINES.md"
"Check if contract tests exist for this feature"
"Run the regression prevention checklist before claiming complete"
```

## Workflow Integration

### For Solo Developer (You)

**Before starting work:**
```bash
# Pull latest and verify clean state
git pull
php artisan test --parallel
npm test
```

**During development:**
```bash
# Run tests frequently
php artisan test --filter=MyFeatureTest

# Check static analysis
vendor/bin/phpstan analyse app/Services/MyService.php
```

**Before committing:**
```bash
# Pre-commit hook runs automatically, but you can run manually:
.husky/pre-commit
```

**Before deploying:**
```bash
# Full regression suite
bash scripts/test-quality-check.sh
vendor/bin/infection --threads=4
```

### For AI Assistant

**When receiving task:**
1. Read relevant ADRs in `docs/adr/`
2. Check if contract tests exist for affected area
3. Run tests to establish baseline

**While implementing:**
1. Write tests FIRST (TDD) for business logic
2. Run tests after each change
3. Check PHPStan after modifying PHP files

**Before claiming complete:**
1. Run regression checklist from `TESTING_GUIDELINES.md`
2. Verify all quality gates pass
3. Report coverage metrics
4. Document any new architectural decisions in ADR

## Preventing Specific Issues

### Issue: Feature Flag Tests Failing

**Root Cause:** Route-dependent flag logic regression

**Prevention Strategy:**
1. ✅ Contract test added: `FeatureFlagContractTest::test_route_dependent_flags_respect_env_hard_floor()`
2. ✅ ADR documented: `docs/adr/0001-feature-flag-architecture.md`
3. ✅ PHPStan configured: Will catch type errors in FeatureFlagService
4. ✅ Testing guidelines: Require tests for all feature flag changes

**Next Steps:**
- Fix the 10 failing tests
- Add mutation tests for FeatureFlagService
- Update contract tests to cover all edge cases

### Issue: Breaking Changes to API

**Prevention:**
- Contract tests for API response schemas
- API versioning strategy (`/api/v1/`)
- Deprecation notices before removal

### Issue: Soft Delete Relationship Bugs

**Prevention:**
- PHPStan rule: Warn on accessing relationships without null checks
- Testing guideline: Always test with soft-deleted related models
- Code review checklist: Verify `?->` used for SoftDeletes models

## Metrics to Track

**Weekly:**
- Test count (should increase with features)
- Coverage percentage (should stay >80%)
- PHPStan errors (should be 0)
- Skipped tests (should trend down)

**Per PR:**
- New tests added
- Coverage delta
- PHPStan issues introduced
- CI/CD duration

**Before deployment:**
- Mutation score (critical paths >75%)
- E2E test pass rate (100%)
- Security audit (0 vulnerabilities)

## Rollback Plan

**If quality gates fail in production:**

1. **Immediate:** Revert last deployment
   ```bash
   git revert HEAD
   git push
   ```

2. **Investigation:** Check CI logs, run tests locally
   ```bash
   git log -10 --oneline
   git diff HEAD~1 HEAD
   php artisan test --filter=FailingTest
   ```

3. **Fix:** Apply fix in new branch, verify ALL gates pass
   ```bash
   git checkout -b fix/issue-name
   # Make changes
   bash scripts/test-quality-check.sh
   vendor/bin/phpstan analyse
   ```

4. **Deploy:** Only after full CI pass and manual verification

## Future Improvements

**Planned:**
1. [ ] Visual regression testing (Percy/Chromatic)
2. [ ] Performance regression testing (Lighthouse CI)
3. [ ] Accessibility regression testing (axe-core)
4. [ ] Database query monitoring (prevent N+1)
5. [ ] Bundle size tracking (prevent bloat)

**Nice to Have:**
1. [ ] Automated ADR generation from commit messages
2. [ ] AI-powered test suggestion based on code changes
3. [ ] Continuous mutation testing (nightly)

## Summary

**The key principle:** **Fail fast, fail early, fail loudly.**

Every layer adds a checkpoint:
1. Pre-commit hook → Blocks bad commits
2. Static analysis → Catches bugs before tests
3. Contract tests → Protects critical behavior
4. Test quality monitoring → Ensures tests are strong
5. Mutation testing → Verifies tests catch bugs
6. CI/CD gates → Enforces standards
7. ADRs → Documents decisions
8. Testing guidelines → Instructs AI

**Result:** Regressions caught at multiple points before reaching production.

---

**Last Updated:** 2026-02-13
**Next Review:** After fixing feature flag tests
