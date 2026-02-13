# Planning Phase Checklist

**CRITICAL:** AI assistants MUST complete this checklist BEFORE writing any code.

## Phase 1: Understanding (Exploration)

Before proposing a solution, answer these questions:

### 1. What existing patterns can be reused?

**Search for similar implementations:**
```bash
# Search for similar services
grep -r "class.*Service" app/Services/

# Search for similar controllers
grep -r "class.*Controller" app/Http/Controllers/

# Search for similar validation
grep -r "FormRequest" app/Http/Requests/

# Search for similar tests
find tests/ -name "*Test.php" | grep -i {feature_name}
```

**Document findings:**
- [ ] Similar feature found at: `{file_path}`
- [ ] Pattern to reuse: `{pattern_name}`
- [ ] Differences from existing: `{differences}`

### 2. What contracts/interfaces exist?

**Check for existing contracts:**
```bash
# Search for interfaces
find app/ -name "*Interface.php"

# Search for abstract classes
grep -r "abstract class" app/

# Search for traits
find app/ -name "*Trait.php"
```

**Document:**
- [ ] Interface to implement: `{interface_name}`
- [ ] Abstract class to extend: `{class_name}`
- [ ] Trait to use: `{trait_name}`

### 3. What critical behavior exists nearby?

**Check for contract tests:**
```bash
# List all contract tests
ls -la tests/Contracts/

# Search for related tests
grep -r "{feature_area}" tests/Contracts/
```

**Read relevant ADRs:**
```bash
# List architectural decisions
ls -la docs/adr/

# Search for related decisions
grep -r "{feature_area}" docs/adr/
```

**Document:**
- [ ] Contract test exists: `{test_name}`
- [ ] ADR exists: `{adr_number}`
- [ ] Critical behavior to preserve: `{behavior_description}`

### 4. What dependencies will this introduce?

**Check current dependencies:**
```bash
# PHP dependencies
composer show

# JS dependencies
npm list --depth=0

# Check for version conflicts
composer why-not {package} {version}
```

**Document:**
- [ ] New packages needed: `{packages}`
- [ ] Conflicts with existing: `{conflicts}`
- [ ] Alternative without new dependency: `{alternative}`

### 5. What edge cases must be handled?

**Standard edge cases checklist:**
- [ ] Soft-deleted relationships (if using SoftDeletes)
- [ ] Null/empty/missing data
- [ ] Concurrent operations (race conditions)
- [ ] Very large datasets (N+1, pagination)
- [ ] Invalid input (validation)
- [ ] Unauthenticated/unauthorized users
- [ ] Feature flag disabled
- [ ] Rate limiting

**Document specific edge cases:**
1. `{edge_case_1}`
2. `{edge_case_2}`
3. `{edge_case_3}`

---

## Phase 2: Architecture (Design)

Before writing code, design the solution:

### 1. Data Model Changes

**If adding/modifying database schema:**

```bash
# Check existing migrations
ls -la database/migrations/ | tail -20

# Check for existing columns
grep -r "{column_name}" database/migrations/

# Check model relationships
grep -A 5 "public function.*(): HasMany\|BelongsTo\|HasOne" app/Models/{Model}.php
```

**Design checklist:**
- [ ] Migration reversible (down() method works)
- [ ] New columns nullable OR have default (never bare NOT NULL on existing tables)
- [ ] Foreign keys with `->constrained()->cascadeOnDelete()`
- [ ] Indexes on frequently queried columns
- [ ] Unique constraints where needed
- [ ] Schema check guards (`Schema::hasColumn()`) for idempotency

**Document:**
```sql
-- Proposed schema
CREATE TABLE {table_name} (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    {column_definitions}
);
```

### 2. Service Layer Design

**If creating/modifying service:**

**Check existing services:**
```bash
# List services
ls -la app/Services/

# Check how similar services are structured
cat app/Services/{SimilarService}.php
```

**Design checklist:**
- [ ] Service uses constructor injection (not facades)
- [ ] Service methods are single-responsibility
- [ ] Service has interface (if needed for testing)
- [ ] Service handles transactions (if mutating data)
- [ ] Service uses Redis locks (if concurrent operations possible)
- [ ] Service eager-loads relationships (prevent N+1)
- [ ] Service logs important actions (audit trail)

**Document:**
```php
class {ServiceName}
{
    public function __construct(
        private readonly {Dependency1},
        private readonly {Dependency2}
    ) {}

    public function {method1}({params}): {ReturnType}
    {
        // 1. Validate inputs
        // 2. Acquire lock if needed
        // 3. Start transaction
        // 4. Perform operation
        // 5. Log action
        // 6. Return result
    }
}
```

### 3. API/Route Design

**If adding/modifying routes:**

**Check existing routes:**
```bash
# List routes
php artisan route:list | grep {feature_area}

# Check route naming patterns
grep "->name(" routes/web.php
```

**Design checklist:**
- [ ] Route follows RESTful conventions
- [ ] Route name follows convention: `{resource}.{action}`
- [ ] Middleware stack correct (auth, verified, feature-gate)
- [ ] Rate limiting appropriate for action
- [ ] FormRequest validation exists
- [ ] Policy authorization exists (for user-owned resources)

**Document:**
```php
// Proposed routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/{resource}', [{Controller}::class, 'index'])->name('{resource}.index');
    Route::post('/{resource}', [{Controller}::class, 'store'])->name('{resource}.store');
    // ...
});
```

### 4. Frontend Component Design

**If adding/modifying React components:**

**Check existing components:**
```bash
# List components
find resources/js/Components/ -name "*.tsx"

# Check how similar components are structured
cat resources/js/Components/{SimilarComponent}.tsx
```

**Design checklist:**
- [ ] Component uses existing UI primitives (`Components/ui/`)
- [ ] Component handles loading state
- [ ] Component handles error state
- [ ] Component handles empty state
- [ ] Component is accessible (ARIA labels, keyboard nav)
- [ ] Component works in dark mode
- [ ] Component follows existing patterns (useForm, usePage, etc.)

**Document:**
```tsx
// Proposed component structure
export function {ComponentName}({ prop1, prop2 }: Props) {
    // 1. State management (useForm, useState)
    // 2. Data fetching (if needed)
    // 3. Event handlers
    // 4. Render (loading, error, success states)
}
```

---

## Phase 3: Testing Strategy (Before Implementation)

Define how you'll verify the solution works:

### 1. Test Coverage Plan

**For each component/service/controller:**

**Unit tests (services, utilities):**
- [ ] Happy path test
- [ ] Validation failure test
- [ ] Edge case tests (null, empty, concurrent)
- [ ] Error handling test

**Feature tests (controllers, end-to-end):**
- [ ] Successful request test (200)
- [ ] Validation failure test (422)
- [ ] Authorization failure test (403)
- [ ] Unauthenticated test (302 redirect)
- [ ] Not found test (404)
- [ ] Rate limit test (429)

**Frontend tests:**
- [ ] Renders correctly
- [ ] Handles user interaction
- [ ] Shows loading state
- [ ] Shows error state
- [ ] Shows empty state

**Document test plan:**
```
Tests to write:
1. test_{feature}_happy_path() - User can {action}
2. test_{feature}_validates_input() - Rejects invalid {input}
3. test_{feature}_requires_auth() - Redirects unauthenticated users
4. test_{feature}_requires_authorization() - Blocks unauthorized users
5. test_{feature}_handles_{edge_case}() - Handles {edge_case}
```

### 2. Contract Test Decision

**Ask:** Does this feature define critical behavior that must never break?

**Examples of contract-worthy features:**
- Authentication/authorization flows
- Payment processing logic
- Webhook signature verification
- Feature flag resolution
- User permission checks

**If yes, create contract test:**
```php
// tests/Contracts/{Domain}ContractTest.php
public function test_{immutable_behavior}(): void
{
    // This behavior MUST NOT change without explicit approval
}
```

**Document:**
- [ ] Contract test needed: Yes/No
- [ ] Contract test name: `{test_name}`
- [ ] Immutable behavior: `{behavior_description}`

### 3. Mutation Testing Plan

**For critical paths (auth, billing, webhooks):**
- [ ] Run mutation testing BEFORE claiming done
- [ ] Target mutation score: 75%+
- [ ] Command: `vendor/bin/infection --filter=app/Services/{Service}.php`

---

## Phase 4: Breaking Change Analysis

Before implementing, check if this breaks existing functionality:

### 1. API Compatibility Check

**If modifying API responses:**
```bash
# Find all API routes
grep -r "Route::.*api" routes/api.php

# Check API tests
find tests/Feature/Api/ -name "*.php"
```

**Questions:**
- [ ] Does this change response structure? (Breaking)
- [ ] Does this add optional fields? (Safe)
- [ ] Does this remove fields? (Breaking - need deprecation)
- [ ] Does this change HTTP status codes? (Breaking)

**If breaking:**
- [ ] Version the API: `/api/v2/...`
- [ ] Deprecate old version
- [ ] Document migration path

### 2. Database Compatibility Check

**If modifying schema:**
```bash
# Check production database structure
php artisan db:show

# Check dependent code
grep -r "{table_name}" app/
```

**Questions:**
- [ ] Is this a two-phase migration? (Drop column after code removed)
- [ ] Will this break existing queries?
- [ ] Do I need to backfill data?

### 3. Frontend Compatibility Check

**If modifying Inertia props:**
```bash
# Find usages of the prop
grep -r "{prop_name}" resources/js/
```

**Questions:**
- [ ] Does this change prop structure? (Breaking)
- [ ] Does this remove props? (Breaking)
- [ ] Does this add optional props? (Safe)

---

## Phase 5: Implementation Order (Safe Sequencing)

Plan the order of changes to avoid breaking the app:

### Safe Implementation Pattern:

1. **Database changes first** (if additive)
   - Add new columns (nullable)
   - Add new tables
   - Add new indexes

2. **Backend changes second**
   - Add new services
   - Add new routes
   - Add new controllers

3. **Frontend changes third**
   - Add new components
   - Add new pages
   - Wire up to backend

4. **Tests fourth**
   - Add unit tests
   - Add feature tests
   - Add contract tests (if needed)

5. **Cleanup last** (if removing)
   - Remove old code (after verifying new works)
   - Remove old columns (two-phase migration)

### Unsafe Pattern (Avoid):
❌ Modify existing service without tests first
❌ Remove columns in same deploy as code removal
❌ Change API response structure without versioning

---

## Phase 6: Documentation Requirements

Before implementation, commit to documenting:

### 1. If Adding Architectural Pattern

**Create ADR:**
- [ ] File: `docs/adr/{NNNN}-{title}.md`
- [ ] Sections: Context, Decision, Consequences, Testing Requirements, References

### 2. If Modifying Critical Behavior

**Update existing ADR:**
- [ ] Find ADR: `grep -r "{feature}" docs/adr/`
- [ ] Update status to "Superseded" if replacing
- [ ] Create new ADR with reference to old

### 3. If Adding Complex Logic

**Add inline documentation:**
- [ ] PHPDoc for public methods
- [ ] JSDoc for exported functions
- [ ] Comments for non-obvious logic (why, not what)

---

## Phase 7: Pre-Implementation Approval

Before writing ANY code, answer:

**Architecture Questions:**
1. What pattern am I following? (Existing or new?)
2. What dependencies am I introducing? (Justified?)
3. What edge cases am I handling? (Comprehensive?)

**Testing Questions:**
1. How will I test this? (Unit, feature, contract?)
2. What's the test coverage plan? (80%+ minimum)
3. Is mutation testing needed? (Critical paths only)

**Breaking Change Questions:**
1. Does this break existing APIs? (Version if yes)
2. Does this break existing UI? (Gradual rollout if yes)
3. Does this break existing DB queries? (Two-phase migration)

**Documentation Questions:**
1. Is an ADR needed? (New pattern or critical behavior)
2. Are inline docs needed? (Complex logic)
3. Is CLAUDE.md updated? (New conventions)

---

## Checklist Summary (Copy-Paste for Each Feature)

```markdown
## Planning Checklist for: {Feature Name}

### Phase 1: Understanding ✓
- [ ] Searched for similar implementations
- [ ] Checked for existing contracts/interfaces
- [ ] Read relevant ADRs
- [ ] Checked for contract tests
- [ ] Listed edge cases

### Phase 2: Architecture ✓
- [ ] Designed data model changes (if any)
- [ ] Designed service layer (if any)
- [ ] Designed routes/API (if any)
- [ ] Designed frontend components (if any)

### Phase 3: Testing Strategy ✓
- [ ] Planned unit tests
- [ ] Planned feature tests
- [ ] Decided on contract test (if critical)
- [ ] Planned mutation testing (if critical path)

### Phase 4: Breaking Change Analysis ✓
- [ ] Checked API compatibility
- [ ] Checked database compatibility
- [ ] Checked frontend compatibility

### Phase 5: Implementation Order ✓
- [ ] Defined safe sequence (DB → Backend → Frontend → Tests)

### Phase 6: Documentation ✓
- [ ] ADR needed: Yes/No
- [ ] Inline docs needed: Yes/No
- [ ] CLAUDE.md update needed: Yes/No

### Phase 7: Pre-Implementation Approval ✓
- [ ] Architecture questions answered
- [ ] Testing questions answered
- [ ] Breaking change questions answered
- [ ] Documentation questions answered
```

---

## Enforcement

**For AI Assistants:**

1. **DO NOT write code** until this checklist is complete
2. **DO NOT claim planning is done** until user confirms approval
3. **DO NOT skip phases** even if "simple" change
4. **DO show your work** - document findings for each phase

**For Developers:**

1. **Review checklist** before approving plan
2. **Challenge assumptions** - ask "why not reuse X?"
3. **Verify edge cases** - are they comprehensive?
4. **Approve implementation order** - is it safe?

---

**Last Updated:** 2026-02-13
**Review:** After every breaking change or regression
