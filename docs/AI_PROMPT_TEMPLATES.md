# AI Prompt Templates

**Purpose:** Structured prompts that enforce proactive quality checks.

---

## Template 1: Feature Implementation

**Use when:** Adding a new feature from scratch

```markdown
# Feature Implementation Request

## Feature Description
{Clear description of what needs to be built}

## Pre-Implementation Checklist

Before writing ANY code, complete:

### 1. Exploration (REQUIRED)
- [ ] Search for similar implementations: `grep -r "{pattern}" app/`
- [ ] Check for existing contracts: `ls tests/Contracts/`
- [ ] Read relevant ADRs: `ls docs/adr/ | grep "{domain}"`
- [ ] List reusable patterns found: {list them}

### 2. Architecture (REQUIRED)
- [ ] Design data model changes (if any)
- [ ] Design service layer (if any)
- [ ] Design routes/API (if any)
- [ ] Design frontend components (if any)
- [ ] Document proposed architecture

### 3. Testing Strategy (REQUIRED)
- [ ] List unit tests to write: {list tests}
- [ ] List feature tests to write: {list tests}
- [ ] Determine if contract test needed: Yes/No
- [ ] Identify edge cases to test: {list cases}

### 4. Breaking Change Analysis (REQUIRED)
- [ ] Check API compatibility
- [ ] Check database compatibility
- [ ] Check frontend compatibility
- [ ] Document breaking changes (if any)

## Implementation Requirements

### Test-Driven Development
- Write tests FIRST for all business logic
- Run tests after each change
- Verify tests fail before implementation (RED)
- Verify tests pass after implementation (GREEN)

### Real-Time Verification
After EACH file change, run:
```bash
# Static analysis
vendor/bin/phpstan analyse {changed_file}

# Related tests
php artisan test --filter={TestName}

# Report results
```

### Commit Strategy
- Commit after each working increment (15-30 min)
- Use descriptive commit messages
- Never accumulate more than 1 hour of uncommitted work

## Acceptance Criteria

Feature is complete when ALL of these pass:

```bash
✅ php artisan test --parallel
✅ npm test
✅ vendor/bin/phpstan analyse
✅ vendor/bin/pint --test
✅ npm run lint
✅ npm run build
✅ php artisan test tests/Contracts/ (if contract tests exist)
```

## Expected Output

1. Architecture design document
2. Test list with descriptions
3. Implementation in small commits
4. Verification results for each step
5. Final quality gate report
```

---

## Template 2: Bug Fix

**Use when:** Fixing a bug or regression

```markdown
# Bug Fix Request

## Bug Description
{What's broken}

## Reproduction Steps
1. {Step 1}
2. {Step 2}
3. {Expected vs Actual}

## Root Cause Analysis (REQUIRED)

Before fixing, diagnose:

### 1. Locate the Bug
- [ ] Which file contains the bug: {file_path}
- [ ] Which function/method: {function_name}
- [ ] Line number (if known): {line_number}

### 2. Understand Why It Broke
- [ ] When was this introduced: `git log -p {file_path}`
- [ ] What changed: `git diff {commit}`
- [ ] Why did tests not catch it: {explanation}

### 3. Prevent Future Occurrences
- [ ] Add contract test if critical behavior
- [ ] Add unit test for specific case
- [ ] Add mutation test if weak coverage
- [ ] Update ADR if architectural assumption was wrong

## Fix Requirements (TDD)

### Step 1: Write Failing Test
```php
public function test_bug_{issue_number}_fixed(): void
{
    // Reproduce the bug
    $result = $service->buggyMethod();

    // Assert correct behavior
    expect($result)->toBe($expected); // ← This MUST fail first
}
```

Run: `php artisan test --filter=test_bug_{issue_number}`
Expected: ❌ FAILS

### Step 2: Minimal Fix
```php
// Fix the bug with minimal changes
```

Run: `php artisan test --filter=test_bug_{issue_number}`
Expected: ✅ PASSES

### Step 3: Verify No Regressions
```bash
php artisan test --parallel
vendor/bin/phpstan analyse
```

Expected: ✅ ALL PASS

### Step 4: Document
- [ ] Add comment explaining why fix is needed
- [ ] Update ADR if architectural change
- [ ] Add to regression test suite

## Acceptance Criteria

✅ Test reproduces bug (fails before fix)
✅ Test passes after fix
✅ No regressions (all tests pass)
✅ Static analysis clean
✅ Root cause documented
✅ Prevention measure added (contract test/mutation test/ADR)
```

---

## Template 3: Refactoring

**Use when:** Improving code structure without changing behavior

```markdown
# Refactoring Request

## Current State
{What code needs refactoring}

## Desired State
{What it should look like after refactoring}

## Safety Requirements (MANDATORY)

### 1. Establish Baseline (REQUIRED)
Before ANY changes:

```bash
# Run all tests - must be GREEN
php artisan test --parallel

# Record current behavior
php artisan test --filter={AffectedArea} > /tmp/before-refactor.txt

# Run static analysis
vendor/bin/phpstan analyse {file_to_refactor} > /tmp/phpstan-before.txt
```

Expected: ✅ ALL PASS

### 2. Refactoring Rules
- [ ] Change ONLY structure, NOT behavior
- [ ] Tests must pass THROUGHOUT refactoring
- [ ] Commit after each safe transformation
- [ ] If tests fail, revert immediately

### 3. Refactoring Steps (Incremental)

**Step 1:** Extract method
- [ ] Extract small method
- [ ] Run tests: ✅ PASS
- [ ] Commit: "Extract {method_name} method"

**Step 2:** Rename for clarity
- [ ] Rename method/variable
- [ ] Run tests: ✅ PASS
- [ ] Commit: "Rename {old} to {new}"

**Step 3:** Remove duplication
- [ ] Create shared helper
- [ ] Replace duplicates
- [ ] Run tests: ✅ PASS
- [ ] Commit: "Remove duplication in {area}"

**Repeat until refactoring complete**

### 4. Post-Refactoring Verification

```bash
# Tests still pass
php artisan test --filter={AffectedArea} > /tmp/after-refactor.txt

# Compare behavior (should be identical)
diff /tmp/before-refactor.txt /tmp/after-refactor.txt

# Static analysis improved or same
vendor/bin/phpstan analyse {file_to_refactor}
```

Expected:
✅ Test output identical
✅ PHPStan errors same or fewer
✅ Code more readable

## Acceptance Criteria

✅ All tests pass (before, during, after)
✅ Behavior unchanged (proven by tests)
✅ Code more readable/maintainable
✅ No new PHPStan errors
✅ Each step committed separately
```

---

## Template 4: Database Migration

**Use when:** Adding/modifying database schema

```markdown
# Database Migration Request

## Schema Change
{What needs to change in the database}

## Safety Checklist (MANDATORY)

### 1. Two-Phase Migration Assessment

**Question:** Is this a breaking change?

Scenarios requiring two-phase:
- [ ] Dropping a column
- [ ] Renaming a column
- [ ] Changing column type (if not compatible)
- [ ] Removing a table

If YES to any:
- **Phase 1:** Deploy code that doesn't use column/table
- **Phase 2:** (Next deploy) Drop column/table

If NO:
- Single-phase migration OK

### 2. Migration Design

**Additive migrations (safe):**
```php
Schema::create('{table}', function (Blueprint $table) {
    // All new columns nullable OR have defaults
    $table->string('new_column')->nullable();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
});
```

**Destructive migrations (require two-phase):**
```php
// Phase 1: Mark as deprecated in code, don't drop yet
// Phase 2 (next deploy): Actually drop
if (Schema::hasColumn('{table}', '{column}')) {
    Schema::table('{table}', function (Blueprint $table) {
        $table->dropColumn('{column}');
    });
}
```

### 3. Migration Testing (REQUIRED)

**Test sequence:**
```bash
# 1. Fresh migration
php artisan migrate:fresh --seed
✅ Should succeed

# 2. Rollback
php artisan migrate:rollback
✅ Should succeed

# 3. Re-migrate
php artisan migrate
✅ Should succeed

# 4. Run tests
php artisan test --parallel
✅ Should pass

# 5. Check for missing indexes
php artisan db:show --database=mysql
# Verify foreign keys have indexes
```

### 4. Data Migration (if needed)

If migrating existing data:
```php
public function up(): void
{
    Schema::table('{table}', function (Blueprint $table) {
        $table->string('new_column')->nullable();
    });

    // Backfill data in chunks (don't lock table)
    DB::table('{table}')->chunkById(1000, function ($records) {
        foreach ($records as $record) {
            DB::table('{table}')
                ->where('id', $record->id)
                ->update(['new_column' => calculate($record)]);
        }
    });

    // Make NOT NULL after backfill
    Schema::table('{table}', function (Blueprint $table) {
        $table->string('new_column')->nullable(false)->change();
    });
}
```

### 5. Performance Assessment

**For large tables (>100k rows):**
- [ ] Adding index: Run `ANALYZE TABLE` after
- [ ] Backfilling data: Use chunking (shown above)
- [ ] Changing column type: May lock table (consider two-phase)

### 6. Production Checklist

Before deploying migration to production:
- [ ] Tested on local database
- [ ] Tested rollback works
- [ ] Tested with realistic data volume
- [ ] Checked for table locks (SHOW PROCESSLIST during migration)
- [ ] Verified indexes exist on foreign keys
- [ ] Backup verified and restoration tested

## Acceptance Criteria

✅ Migration runs successfully (up)
✅ Migration rolls back successfully (down)
✅ All tests pass after migration
✅ Seeders work after migration
✅ No table locks for >5 seconds
✅ Foreign keys have indexes
✅ Schema documented (if complex change)
```

---

## Template 5: API Endpoint Addition

**Use when:** Adding new API endpoint

```markdown
# API Endpoint Request

## Endpoint Specification
- Method: {GET|POST|PUT|PATCH|DELETE}
- Path: `/api/v1/{resource}`
- Purpose: {What it does}

## API Design Checklist (REQUIRED)

### 1. RESTful Compliance
- [ ] Uses correct HTTP verb (GET=read, POST=create, PUT=replace, PATCH=update, DELETE=remove)
- [ ] Resource naming (plural nouns: `/users`, `/tokens`)
- [ ] Nested resources (if needed: `/users/{user}/tokens`)
- [ ] Follows existing API patterns

### 2. Request Validation
```php
// Create FormRequest
class {Action}{Resource}Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->resource);
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'max:255'],
        ];
    }
}
```

### 3. Response Format (Consistent)
```json
{
  "data": {
    "id": 1,
    "attributes": {},
    "relationships": {}
  },
  "meta": {
    "version": "1.0"
  }
}
```

### 4. Error Handling (Consistent)
```json
{
  "errors": [
    {
      "status": "422",
      "title": "Validation Error",
      "detail": "The email field is required.",
      "source": {"pointer": "/data/attributes/email"}
    }
  ]
}
```

### 5. Versioning
- [ ] Route registered under `/api/v1/`
- [ ] Version in response meta
- [ ] Documented deprecation policy

### 6. Authentication & Authorization
- [ ] Sanctum middleware applied
- [ ] Policy authorization implemented
- [ ] Rate limiting configured
- [ ] Scopes/abilities checked (if applicable)

### 7. Testing (REQUIRED)

**Tests to write:**
```php
// 1. Happy path
public function test_user_can_{action}_{resource}(): void
{
    $response = $this->actingAs($user)->postJson('/api/v1/{resource}', $data);

    $response->assertStatus(201);
    $response->assertJsonStructure(['data' => ['id', 'attributes']]);
}

// 2. Validation failure
public function test_{action}_{resource}_validates_input(): void
{
    $response = $this->actingAs($user)->postJson('/api/v1/{resource}', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['field']);
}

// 3. Authorization failure
public function test_{action}_{resource}_requires_authorization(): void
{
    $response = $this->actingAs($otherUser)->postJson('/api/v1/{resource}', $data);

    $response->assertStatus(403);
}

// 4. Unauthenticated
public function test_{action}_{resource}_requires_authentication(): void
{
    $response = $this->postJson('/api/v1/{resource}', $data);

    $response->assertStatus(401);
}

// 5. Not found
public function test_{action}_{resource}_returns_404_for_missing(): void
{
    $response = $this->actingAs($user)->getJson('/api/v1/{resource}/999');

    $response->assertStatus(404);
}

// 6. Rate limiting
public function test_{action}_{resource}_rate_limits(): void
{
    for ($i = 0; $i < 61; $i++) {
        $response = $this->actingAs($user)->postJson('/api/v1/{resource}', $data);
    }

    $response->assertStatus(429);
}
```

### 8. Documentation (REQUIRED)

If `api_docs` feature enabled, add to OpenAPI spec:
```php
/**
 * @OA\Post(
 *   path="/api/v1/{resource}",
 *   summary="{Action} a {resource}",
 *   @OA\Response(response=201, description="Created"),
 *   @OA\Response(response=422, description="Validation failed")
 * )
 */
```

## Acceptance Criteria

✅ Endpoint follows REST conventions
✅ FormRequest validation implemented
✅ Policy authorization implemented
✅ All 6 test scenarios pass
✅ Rate limiting configured
✅ Response format consistent
✅ Versioned under `/api/v1/`
✅ Documented (if api_docs enabled)
```

---

## How to Use These Templates

### For Developers:

When requesting work from AI, copy-paste the relevant template into your prompt.

**Example:**
```
Please implement a new feature for user notifications.

{Paste Template 1: Feature Implementation}

## Feature Description
Send email when user's subscription is about to expire (7 days before).

## Pre-Implementation Checklist
{AI fills this out before writing code}
```

### For AI Assistants:

When you receive a task:
1. Identify which template applies
2. Complete ALL checklist items in the template
3. Report findings to user
4. Wait for approval before implementation
5. Follow implementation requirements strictly
6. Verify acceptance criteria before claiming done

---

**Last Updated:** 2026-02-13
**Review:** After any template proves insufficient
