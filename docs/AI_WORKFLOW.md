# AI Workflow

**Single reference for AI-assisted development on this codebase.** Supersedes `PLANNING_CHECKLIST.md`, `IMPLEMENTATION_GUARDRAILS.md`, `AI_DEVELOPMENT_SAFEGUARDS.md`, and `PROACTIVE_SAFEGUARDS_SUMMARY.md`.

CLAUDE.md is the authoritative entry point; this file provides the detailed checklists.

---

## Part 1 — Before Writing Code (Planning)

Complete all 7 phases before touching a file.

### Phase 1: Understand existing patterns

```bash
grep -r "class.*Service" app/Services/
grep -r "FormRequest" app/Http/Requests/
find tests/ -name "*Test.php" | grep -i {feature_name}
ls -la docs/adr/
```

- [ ] Similar implementation found at: `{file_path}` — reuse, don't recreate
- [ ] Relevant ADR exists: `{adr_number}` — read it before designing
- [ ] Contract test exists: `{test_name}` — understand what must not break

### Phase 2: Design data model, service, routes, and frontend

**Data model checklist:**
- [ ] New columns nullable or with default — never bare NOT NULL on existing tables
- [ ] Foreign keys with `->constrained()->cascadeOnDelete()` + index
- [ ] `Schema::hasColumn()` guard for idempotency
- [ ] Migration reversible (`down()` works)

**Service layer checklist:**
- [ ] Constructor injection (not facades)
- [ ] Single-responsibility methods
- [ ] Redis lock if concurrent operations possible
- [ ] `DB::transaction()` around multi-table writes
- [ ] Eager-load relationships before any Cashier methods (see `billing.md`)

**Route checklist:**
- [ ] RESTful naming: `{resource}.{action}`
- [ ] Correct middleware stack (auth, verified, feature-gate, throttle)
- [ ] FormRequest validation class created
- [ ] Policy authorization if user-owned resource

**Frontend checklist:**
- [ ] Uses existing primitives in `Components/ui/`
- [ ] Handles loading, empty, and error states
- [ ] Accessible (ARIA, keyboard nav per `accessibility.md`)
- [ ] Works in dark mode (semantic color tokens)

### Phase 3: Testing strategy

For every service/controller/component, define before writing:

**Backend:**
- [ ] Happy path test (200 / success)
- [ ] Validation failure (422)
- [ ] Auth failure (302 redirect or 403)
- [ ] Edge cases: soft-deleted users, null relationships, concurrent ops

**Frontend:**
- [ ] Renders correctly
- [ ] User interaction (click, submit)
- [ ] Loading / error / empty states

**Contract test needed?** Required for: auth flows, payment logic, webhook verification, feature flag resolution, user permission checks.

**Mutation testing needed?** Required for: critical paths (auth, billing, webhooks) — `vendor/bin/infection --filter=app/Services/{Service}.php`, 75%+ target.

### Phase 4: Breaking change analysis

- [ ] API response structure unchanged (or versioned if breaking)
- [ ] Schema changes two-phased (code stops using column before drop)
- [ ] Inertia prop changes backward-compatible (add optional, never remove)

### Phase 5: Safe implementation order

1. Database (additive: new tables, nullable columns)
2. Backend (services, controllers, routes)
3. Frontend (components, pages, wiring)
4. Tests
5. Cleanup (remove old code only after new works)

### Phase 6: Documentation

- [ ] ADR needed if introducing a new architectural pattern
- [ ] CLAUDE.md update if changing a convention
- [ ] Inline comment only if the WHY is non-obvious

### Phase 7: Approval gate

Answer before writing any code:
1. What existing pattern am I following?
2. What edge cases am I covering?
3. Is this a breaking change? If yes, what's the migration path?

---

## Part 2 — During Implementation (Guardrails)

### After each file change

**Modified service:**
```bash
vendor/bin/phpstan analyse app/Services/{ChangedService}.php
php artisan test --filter={ServiceName}Test
```
- [ ] No PHPStan errors
- [ ] Tests pass
- [ ] New tests written for new methods

**Modified controller:**
```bash
php artisan route:list | grep {controller_name}
php artisan test --filter={ControllerName}Test
```
- [ ] Routes registered correctly
- [ ] No N+1 in test output

**Modified migration:**
```bash
php artisan migrate:fresh --seed
php artisan migrate:rollback && php artisan migrate
```
- [ ] Up and down both work
- [ ] Seeders still work

**Modified feature flag logic:**
```bash
php artisan test tests/Contracts/FeatureFlagContractTest.php
php artisan test --filter=FeatureFlag
```
- [ ] Contract tests pass (CRITICAL)
- [ ] Routes correctly gated

**Modified frontend component:**
```bash
npm test -- {ComponentName}
npx tsc --noEmit
```
- [ ] No TypeScript errors
- [ ] Visual check in dev server

### Anti-pattern detectors

**N+1:** Any `foreach` accessing a relationship without eager loading.
```php
// Wrong
foreach ($users as $user) { echo $user->profile->name; }
// Fix
$users = User::with('profile')->get();
```
Detection: `DB::enableQueryLog()` in tests; assert `count($queries) < N`.

**Missing transaction:** Multiple table mutations without `DB::transaction()`.

**Missing null check (SoftDeletes):** `$model->user->name` → must be `$model->user?->name ?? '[Deleted User]'`. PHPStan catches this.

**Ungated feature route:** `Route::get('/billing', ...)` without `if (config('features.billing.enabled'))`.

**Hardcoded config:** Magic numbers not behind `config()`.

### TDD micro-cycle

1. **Red** — write a failing test first
2. **Green** — minimal implementation to pass
3. **Refactor** — improve without breaking the test

Run only the test you changed during this loop. Full suite runs once at the end via `/v-pre-flight`.

### Risk matrix — stop if MEDIUM or above

| Change type | Risk | Required action |
|------------|------|----------------|
| New isolated feature | Low | Tests pass, PHPStan clean |
| Modify existing service | Medium | Tests + contract tests + stop and confirm with user |
| Auth / billing / webhooks | High | All above + adversarial agent review |
| Destructive migration | High | Two-phase plan, rollback tested |
| Breaking API change | Critical | Version bump, deprecation notice |

### Red flags — stop and investigate

🚩 Test passes without any assertions written  
🚩 Need to skip a test to make the build pass  
🚩 Adding `sleep()` to fix a timing issue  
🚩 Copying 50+ lines of code  
🚩 Method has 5+ parameters (create a DTO)  
🚩 Function is 100+ lines (break it up)  

---

## Part 3 — Defense Layers

These run automatically on every commit and CI run. Do not bypass them.

| Layer | What it catches | When it runs |
|-------|----------------|-------------|
| Husky pre-commit | Pint formatting, lint-staged | Every `git commit` |
| PHPStan (level configured in `phpstan.neon`) | Type errors, undefined methods, null dereference | Pre-commit + CI |
| Laravel Pint | PSR-12 style violations | Pre-commit + CI |
| Pest (`--parallel`) | Regression, logic bugs | CI (all tests) |
| Vitest | Frontend component regressions | CI |
| Playwright | Auth smoke tests | CI |
| Infection (mutation testing) | Weak test assertions on critical paths | CI weekly cron |
| `composer audit` + `npm audit` | Known CVEs in dependencies | CI |
| Contract tests (`tests/Contracts/`) | Immutable behavioral contracts | CI — do NOT modify without user approval |
| ADRs (`docs/adr/`) | Architectural decision history | Human review |

**Contract tests are non-negotiable.** They document behavior that must survive any refactor. Do not modify without explicit user approval.

### Full quality gate (run before claiming done)

```bash
bash scripts/test-quality-check.sh
# Equivalent manual run:
php artisan test --parallel
npm test
vendor/bin/phpstan analyse
vendor/bin/pint --test
npm run lint
npm run build
```

Or invoke `/v-pre-flight` via the Skill tool — it handles stack detection and artifact generation.

---

## Quick reference — common mistakes

| Mistake | Correct pattern |
|---------|----------------|
| `await router.delete(...)` | `router.delete(..., { onSuccess: () => ... })` |
| `env('APP_URL')` in app code | `config('app.url')` |
| Direct `Cache::forget(AdminCacheKey::X)` | `$cacheInvalidationManager->invalidateX()` |
| Direct Cashier `cancel()` without eager load | `$sub->load('owner', 'items.subscription')` first |
| Bare NOT NULL column on existing table | Always nullable or with default |
| Breaking API response without versioning | Version the endpoint or deprecate gracefully |
| Test that asserts only mock was called | Assert final DB state and response too |
