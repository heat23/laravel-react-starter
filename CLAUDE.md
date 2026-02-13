# Laravel React Starter Template

**Stack:** Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4

**This is a production-ready starter template, not scaffolding.** Every feature is a complete, tested implementation.

## 🛡️ AI Development Safeguards

**CRITICAL:** AI assistants MUST follow these workflows to prevent regressions. Human developers should use the same processes when working solo.

### 📋 Workflow for AI Assistants

**When receiving ANY task, use this structured approach:**

#### 1. **Planning Phase** (BEFORE writing code)
Follow: [docs/PLANNING_CHECKLIST.md](docs/PLANNING_CHECKLIST.md)

**Required steps:**
- Search for similar implementations (reuse, don't recreate)
- Check for contract tests in affected area
- Read relevant ADRs
- List edge cases
- Design architecture
- Plan testing strategy
- Assess breaking changes
- Get user approval BEFORE implementation

#### 2. **Implementation Phase** (WHILE writing code)
Follow: [docs/IMPLEMENTATION_GUARDRAILS.md](docs/IMPLEMENTATION_GUARDRAILS.md)

**Required steps:**
- Write tests FIRST (TDD for business logic)
- Run tests after EACH file change
- Run PHPStan after each PHP file change
- Commit every 15-30 minutes
- Self-verify before moving to next step
- Stop immediately if any check fails

#### 3. **Verification Phase** (AFTER implementation)
Follow: [docs/TESTING_GUIDELINES.md](docs/TESTING_GUIDELINES.md)

**Required steps:**
```bash
# Full quality gate check
bash scripts/test-quality-check.sh

# Or run manually:
php artisan test --parallel
npm test
vendor/bin/phpstan analyse
vendor/bin/pint --test
npm run lint
npm run build
php artisan test tests/Contracts/  # If contract tests exist
```

### 📝 Prompt Templates

For structured requests, use templates in [docs/AI_PROMPT_TEMPLATES.md](docs/AI_PROMPT_TEMPLATES.md):

- **Template 1:** Feature Implementation (new features)
- **Template 2:** Bug Fix (fixing regressions)
- **Template 3:** Refactoring (improving code structure)
- **Template 4:** Database Migration (schema changes)
- **Template 5:** API Endpoint Addition (new API routes)

### 🔒 Defense Layers

**Reactive (Catch after commit):**
- ✅ Pre-commit hooks (`.husky/pre-commit`) - Block bad commits
- ✅ CI/CD quality gates (`.github/workflows/ci.yml`) - Block bad merges
- ✅ Test quality monitoring (`scripts/test-quality-check.sh`) - Detect weak tests
- ✅ Mutation testing (`infection`) - Verify tests catch bugs

**Proactive (Catch during development):**
- ✅ Planning checklist - Prevent bad designs
- ✅ Implementation guardrails - Real-time verification
- ✅ Contract tests (`tests/Contracts/`) - Protect critical behavior
- ✅ Architectural Decision Records (`docs/adr/`) - Document assumptions
- ✅ Prompt templates - Enforce structured thinking

### ⚠️ Critical Rules for AI

**DO NOT:**
- ❌ Write code before completing planning checklist
- ❌ Skip tests (TDD is mandatory for business logic)
- ❌ Accumulate failures (fix immediately, don't continue)
- ❌ Modify contract tests without user approval
- ❌ Skip verification steps to "save time"
- ❌ Claim work complete without running quality gates

**DO:**
- ✅ Search for existing patterns before creating new ones
- ✅ Write tests FIRST for business logic
- ✅ Run checks after EACH file change
- ✅ Commit frequently (every 15-30 min)
- ✅ Report verification results before continuing
- ✅ Stop and ask if anything is unclear

## Customization via Feature Flags

Configure your app by toggling features in `config/features.php` (or `.env`). 10 feature flags control major subsystems:

**Common configurations:**
- **SaaS with billing:** Enable `billing`, `webhooks`, `two_factor`, `api_tokens`
- **Internal tool:** Enable `two_factor`, `api_tokens`, `notifications`; disable `billing`, `social_auth`
- **Simple MVP:** Enable only `email_verification`, `user_settings`; disable premium features

**Safe to toggle:** Feature-gated routes don't register when disabled. Database tables remain but stay empty. UI elements conditionally render.

See "Feature Flags" section below for what each flag controls.

## Feature Flags

Check `config/features.php` and `.env` before implementing. Features default off unless noted:

| Flag | Env Var | What it enables |
|------|---------|-----------------|
| `billing.enabled` | `FEATURE_BILLING` | Stripe Cashier, pricing page, billing portal |
| `social_auth.enabled` | `FEATURE_SOCIAL_AUTH` | Google/GitHub OAuth (auto-detected by client ID presence) |
| `email_verification.enabled` | `FEATURE_EMAIL_VERIFICATION` | Email verification flow (default: true) |
| `api_tokens.enabled` | `FEATURE_API_TOKENS` | Sanctum token management UI (default: true) |
| `user_settings.enabled` | `FEATURE_USER_SETTINGS` | Theme/timezone persistence (default: true) |
| `notifications.enabled` | `FEATURE_NOTIFICATIONS` | In-app notification system |
| `onboarding.enabled` | `FEATURE_ONBOARDING` | Welcome wizard for new users |
| `api_docs.enabled` | `FEATURE_API_DOCS` | Scribe interactive API docs |
| `two_factor.enabled` | `FEATURE_TWO_FACTOR` | TOTP 2FA authentication |
| `webhooks.enabled` | `FEATURE_WEBHOOKS` | Incoming/outgoing webhooks |

**What each flag controls:**
- `billing`: BillingService, SubscriptionController, PricingController, pricing page, billing portal, Stripe webhooks, CheckIncompletePayments command
- `social_auth`: SocialAuthController, SocialAccount model, Google/GitHub OAuth flows
- `email_verification`: Email verification routes, SendEmailVerificationNotification listener, middleware
- `api_tokens`: TokenController, API token CRUD UI in settings
- `user_settings`: UserSettingsController, theme/timezone persistence
- `notifications`: NotificationController, in-app notification UI
- `onboarding`: OnboardingController, welcome wizard flow
- `api_docs`: Scribe-generated API documentation
- `two_factor`: TwoFactorController, TOTP setup/verification, recovery codes
- `webhooks`: WebhookService, WebhookEndpoint/Delivery/Incoming models, signature verification

**Disabling features:** Set env var to `false`. Feature-gated routes won't register, middleware won't apply, UI elements won't render. Database tables remain (safe to leave empty).

### Feature Flag Dependency Graph

**Hard Dependencies (will break if dependency disabled):**
- `onboarding` → requires `user_settings` (stores completion timestamp in user_settings table)
- `billing` → requires `webhooks` for Stripe webhooks (auto-enabled in routes/api.php)
- `two_factor` → requires `user_settings` for enrollment preference (optional fallback exists)
- `api_docs` → requires `api_tokens` (documents token endpoints)

**Soft Dependencies (graceful degradation):**
- `notifications` + `webhooks` = webhook delivery notifications (webhook failures still logged to database)
- `billing` + `email_verification` = prevents subscriptions from unverified users (check in SubscriptionController)
- `social_auth` + `email_verification` = OAuth accounts start pre-verified (handled in SocialAuthService)

**Testing Feature Flag Combinations:**
When adding a new feature-gated feature, test these scenarios:
1. Feature ON, dependency OFF → should fail gracefully or show "requires X feature" message
2. Feature ON, dependency ON → full functionality
3. Feature OFF → routes don't register, nav links hidden, API returns 404

**Adding a New Feature Flag:**
1. Add to `config/features.php` with env var and `enabled` key
2. Document in this section "What each flag controls"
3. Add dependency to this graph if applicable
4. Gate routes with `if (config('features.X.enabled'))` in routes files
5. Gate nav links with `{features.X && ...}` in TSX
6. Add test: `it('route returns 404 when feature disabled')`

## Environments

| Env | APP_ENV | Mail | Analytics | Sentry |
|-----|---------|------|-----------|--------|
| Local | `local` | Mailpit (:1025, view at :8025) | Disabled | Disabled |
| Preview | `preview` | SMTP | Optional | Enabled |
| Production | `production` | SMTP | `GOOGLE_ANALYTICS_ID` | `SENTRY_LARAVEL_DSN` |

## Architecture

**Models:** User, UserSetting (key-value), SocialAccount (OAuth), AuditLog, WebhookEndpoint, WebhookDelivery, IncomingWebhook, TwoFactorAuthentication (via Laragear)

**Services:**
- `AuditService` — activity logging
- `BillingService` — Redis-locked Stripe subscription mutations (CRITICAL: see Gotchas)
- `PlanLimitService` — enforce subscription limits (projects, items, tokens)
- `SessionDataMigrationService` — migrate guest session data on login
- `SocialAuthService` — OAuth provider abstraction
- `WebhookService` — outgoing webhook dispatch with HMAC signing
- `IncomingWebhookService` — process/validate incoming webhooks

**Billing (Production-Grade):**
- `BillingService` — Redis-locked subscription mutations (create, cancel, resume, swap)
  - **CRITICAL:** Uses Redis locks (35s timeout) to prevent concurrent Stripe API calls
  - **CRITICAL:** Must eager load `owner` + `items.subscription` before Cashier methods
  - All operations wrapped in DB transactions for atomicity
- Plan tiers: free, pro, team (3-50 seats), enterprise (custom)
- Incomplete payment tracking: `CheckIncompletePayments` command sends reminders at 1h/12h

**Webhooks:**
- `WebhookService` — Outgoing webhooks with HMAC-SHA256 signing, async dispatch via `DispatchWebhookJob`
- `IncomingWebhookService` — Process GitHub/Stripe webhooks with signature verification via `VerifyWebhookSignature` middleware
- Models: `WebhookEndpoint`, `WebhookDelivery`, `IncomingWebhook`

**Two-Factor Authentication:**
- Via `laragear/two-factor` package (TOTP + recovery codes)
- `TwoFactorChallengeController` handles verification
- Feature-gated via `two_factor.enabled`

**Tenancy:** Single-tenant. Do not add account/org/workspace scoping unless explicitly requested.

**Routes:**
- `routes/web.php` — pages (feature-gated with `if (config('features.*.enabled'))`)
- `routes/auth.php` — auth (Breeze + social auth + email verification)
- `routes/api.php` — Sanctum-protected API (user, settings, tokens)
- Health check: `/up` (configured in `bootstrap/app.php`)

## Decision-Making Frameworks

### When to Create a Service Class

Create a dedicated Service when **ANY** of these conditions apply:
1. Logic involves external API calls (Stripe, GitHub, etc.)
2. Logic requires distributed locking (Redis locks)
3. Logic wraps database transactions across multiple models
4. Logic is reusable across 2+ controllers/jobs/commands
5. Logic involves complex state machines or multi-step processes
6. Logic requires extensive mocking/stubbing in tests

Keep logic in Controller when **ALL** of these are true:
- Single model CRUD operations
- No external dependencies
- Validation handled by Form Request
- No transaction coordination needed
- Less than 30 lines of business logic

**Examples from this codebase:**
- ✅ Service: `BillingService` (Redis locks + Stripe API + transactions)
- ✅ Service: `WebhookService` (external HTTP calls + HMAC signing)
- ✅ Controller: `OnboardingController` (single setting update)
- ⚠️  Gray area: `ProfileController` (consider service if adding photo upload to S3)

### When to Create a Policy vs Manual Auth Checks

**Rule:** Always use policies for resource authorization (user can view/update/delete specific resource).

```php
// ❌ Bad: Manual role checks in controller
if (auth()->user()->is_admin || $project->user_id === auth()->id()) {
    $project->delete();
}

// ✅ Good: Policy with clear rules
// In ProjectPolicy:
public function delete(User $user, Project $project): bool
{
    return $user->is_admin || $project->user_id === $user->id;
}

// In Controller:
$this->authorize('delete', $project);
```

**When NOT to use policies:**
- Feature flag checks (use `abort_unless(feature_enabled('X'))` in controller constructor)
- Role-based route protection (use middleware: `->middleware('admin')`)
- Global permissions not tied to a resource (use Gate::define in AuthServiceProvider)

### When to Create a Job vs Execute Synchronously

Create a Job when:
- External API call that can be retried (Stripe, email sending)
- Long-running operation (>5 seconds)
- Rate-limited operation that needs queuing
- Operation that should survive request timeout

Execute synchronously when:
- User needs immediate feedback (form submission response)
- Operation is fast (<1 second)
- Failure requires user action (payment declined)

**Note:** No Jobs directory currently exists. Create `app/Jobs/{Domain}/` when first needed.

### When to Create a FormRequest vs Inline Validation

**Rule:** ALWAYS use FormRequest. Never inline `$request->validate()` in controllers.

**Reasons:**
- Keeps controllers thin (single responsibility)
- Centralizes authorization + validation logic
- Reusable across multiple controller methods
- Easier to test in isolation

```php
// ❌ Bad: Inline validation
public function update(Request $request, User $user)
{
    $request->validate(['name' => 'required|max:255']);
    // ...
}

// ✅ Good: FormRequest
public function update(UpdateUserRequest $request, User $user)
{
    $user->update($request->validated());
    // ...
}
```

## Performance Budgets

### Query Count Limits (enforce in tests)

**Per-request budgets:**
- Dashboard page: ≤5 queries (user + settings + cached stats)
- User index (admin): ≤3 queries per page (users + pagination count + audit log latest)
- Detail pages with relationships: ≤8 queries (model + 3 relationships + cache checks)
- API endpoints: ≤4 queries (auth + main query + optional related)

**When to eager load:**
- ✅ Always: accessing `$model->relationship->property` in Blade/Inertia props
- ✅ Always: looping over collection and accessing relationships
- ✅ Always: before calling Cashier methods (`->load('owner', 'items.subscription')`)
- ✅ Conditionally: if feature flag might show data

**How to verify in tests:**
```php
it('user index page has no N+1 queries', function () {
    DB::enableQueryLog();

    $admin = User::factory()->admin()->create();
    User::factory()->count(20)->create(); // create 20 users

    $this->actingAs($admin)->get('/admin/users');

    $queries = DB::getQueryLog();
    expect(count($queries))->toBeLessThanOrEqual(5); // allow user auth + main query + count
});
```

**Common N+1 patterns to avoid:**
```php
// ❌ Bad: N+1 in loop
foreach ($users as $user) {
    $tier = $user->subscription->tier; // lazy loads for each user
}

// ✅ Good: eager load
$users = User::with('subscription')->get();
foreach ($users as $user) {
    $tier = $user->subscription->tier;
}

// ❌ Bad: N+1 in Inertia props
return Inertia::render('Users/Index', [
    'users' => $users, // User model with subscription relationship not loaded
]);

// ✅ Good: eager load before Inertia
return Inertia::render('Users/Index', [
    'users' => $users->load('subscription'),
]);
```

### Cache Strategy

**When to cache (all cached with AdminCacheKey enum):**
- Admin dashboard stats (5min TTL)
- Billing tier distribution (5min TTL)
- Chart data that aggregates historical records (1hr TTL)
- Feature flag global overrides (5min TTL, flushed on change)

**When NOT to cache:**
- User-specific current state (subscription status, unread count)
- Data that changes on every request (audit logs, real-time notifications)
- Small lookup tables that fit in opcache (< 100 rows)

**Cache invalidation checklist** (when adding mutations):
- If mutation affects admin dashboard counts → `Cache::forget(AdminCacheKey::DASHBOARD_STATS->value)`
- If mutation affects billing stats → invalidate `BILLING_STATS` + `BILLING_TIER_DIST`
- If mutation affects webhooks/tokens/2FA stats → invalidate respective enum key
- If global feature flag override changes → `AdminCacheKey::flushAll()`

## How to Add a New Feature

1. **Migration:** `php artisan make:migration create_{table}_table` — follow existing migration patterns in `database/migrations/`
2. **Model:** `php artisan make:model {Name}` — add to User relationship if user-owned
3. **Factory:** Create in `database/factories/` — required for any new model to keep tests easy
4. **Form Request:** Create in `app/Http/Requests/` — always implement `authorize()` and `rules()`
5. **Controller:** Create in `app/Http/Controllers/` — use constructor injection, Form Requests, policy auth
6. **Policy (if user-owned):** Create in `app/Policies/` — register in `AppServiceProvider` if needed. Only `UserPolicy` exists currently.
7. **Page:** Create in `resources/js/Pages/` — use `usePage()` for shared props, `useForm()` for forms
8. **Route:** Add to `routes/web.php` — wrap in feature flag if optional, always name routes. Add API route to `routes/api.php` if applicable.
9. **Feature flag (if optional):** Add to `config/features.php` with env var default
10. **Tests:** Write Pest tests in `tests/Feature/` and `tests/Unit/` — see `tests/` structure
11. **Nav links:** Update navigation components to show new feature (gate with `{features.X && ...}` if feature-flagged)
12. **TypeScript types:** Add Inertia page props type definitions in `resources/js/types/`
13. **Review checklist (run mentally before claiming done):**
    - **Query count budget:** Does page meet query count budget? Add `DB::enableQueryLog()` test.
    - **Accessibility:** Can you complete the flow with keyboard only? (no mouse)
    - **Soft-delete sweep:** Does any code access `->user->`, `->owner->`, or other relationships without `?->` where the related model uses `SoftDeletes`? Add `withTrashed()` to admin-facing queries.
    - **Middleware audit:** If a route is outside its normal middleware group, does it have the right (and ONLY the right) middleware? Especially: don't put `verified` on routes that unverified users need.
    - **Cache invalidation:** If the feature mutates data that feeds an admin dashboard or cached stats, does it call `Cache::forget()` on the relevant `AdminCacheKey`?
    - **Async contract:** If a function is passed as `onConfirm` to a dialog or awaited anywhere, does it return a `Promise` that resolves after the server responds (not after the fire-and-forget call)?
    - **Nav/URL prefix collisions:** If adding a new nav item or route, does `startsWith` matching cause false positives with parent routes?
    - **Local state vs URL params:** If a component uses both `useState` and URL-based filters, does `clearFilters` reset ALL local state?

## Error Recovery Playbooks

### Test Failure Diagnosis Protocol

When a test fails after implementation, follow this checklist **in order:**

**Step 1: Classify the Failure**
- **Type mismatch** (expected array, got object): Check Inertia prop structure
- **Database state** (expected record not found): Check factory relationships, soft deletes
- **Timing** (Promise resolved too early): Check async contract (Inertia router is fire-and-forget)
- **Cache** (stale data): Did you invalidate AdminCacheKey after mutation?
- **Feature flag** (route 404): Is the feature enabled in phpunit.xml or TestCase?

**Step 2: Common Root Causes by Test Type**

*Feature test redirects to unexpected route:*
1. Check middleware stack (especially `verified`, `onboarding.completed`)
2. Check authorization in controller (policy, manual auth checks)
3. Check feature flag state in test
4. Check if user is soft-deleted (use `withTrashed()` if needed)

*Unit test returns wrong value:*
1. Check if dependent methods are mocked correctly
2. Check if relationships are loaded (call `->load()` before accessing)
3. Check if cache is returning stale value (call `Cache::flush()` in beforeEach)
4. Check if config values match expectations

*Integration test with external service:*
1. Verify mock/fake is called BEFORE model creation
2. Verify job is dispatched (Queue::fake()) not executed synchronously
3. Verify webhook signature format matches real provider format

**Step 3: Fixes to NEVER Make**
- ❌ Remove assertion to make test pass
- ❌ Change assertion operator to weaker version (`toBe` → `not->toBeNull`)
- ❌ Add `sleep()` or `usleep()` to fix timing
- ❌ Disable middleware in test without understanding why it's failing
- ❌ Use `$this->withoutExceptionHandling()` to pass 500 errors

**Step 4: Fixes That Are Usually Correct**
- ✅ Eager load relationships before accessing them
- ✅ Invalidate cache keys after mutations
- ✅ Add `withTrashed()` to queries that need soft-deleted records
- ✅ Use `assertSessionHas()` for flash messages, not Inertia assertions
- ✅ Mock Notification facade BEFORE creating models that dispatch events

### Migration Failure Recovery

**If `php artisan migrate` fails in production:**

1. **DO NOT run `migrate:rollback` blindly** — it might drop production data

2. **Diagnose the failure:**
   - Check error message for specific issue (duplicate key, missing column, data type mismatch)
   - Check if migration was partially applied: `SELECT * FROM migrations ORDER BY id DESC LIMIT 5;`

3. **Write a fix migration instead:**
   ```bash
   php artisan make:migration fix_failed_migration_issue
   ```

**Common failure modes and fixes:**

*Duplicate column (column already exists):*
```php
// Fix migration:
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'phone')) {
            $table->string('phone')->nullable();
        }
    });
}
```

*Data type mismatch (existing data doesn't fit new type):*
```php
// Failed: $table->integer('quantity')->change(); (but existing data has decimals)
// Fix migration: migrate data first, then change type
public function up(): void
{
    DB::table('order_items')->update(['quantity' => DB::raw('ROUND(quantity)')]);

    Schema::table('order_items', function (Blueprint $table) {
        $table->integer('quantity')->change();
    });
}
```

*Foreign key constraint failure (orphaned records exist):*
```php
// Fix migration: clean up orphans first
public function up(): void
{
    DB::table('projects')->whereNotIn('user_id', DB::table('users')->pluck('id'))->delete();

    Schema::table('projects', function (Blueprint $table) {
        $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
    });
}
```

**Two-Phase Deploys for Breaking Schema Changes**

When dropping a column:
```php
// ❌ Bad: drop column in same deploy as code removal
// Problem: Zero-downtime deploy will have old code running with missing column

// ✅ Good: two-phase deploy
// Deploy 1: Remove code that uses column (migration does nothing)
// Wait for rollout to complete (15 minutes)
// Deploy 2: Add migration that drops column

public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (Schema::hasColumn('users', 'old_field')) {
            $table->dropColumn('old_field');
        }
    });
}
```

## Test Quality Standards

### Anatomy of a High-Quality Test

```php
it('admin can toggle user admin status and change is logged', function () {
    // 1. ARRANGE: Set up preconditions
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create(['is_admin' => false]);

    // 2. ACT: Perform the action being tested
    $response = $this->actingAs($admin)->patch("/admin/users/{$targetUser->id}/toggle-admin");

    // 3. ASSERT: Verify user-visible outcomes
    $response->assertRedirect();
    $response->assertSessionHas('flash.type', 'success');

    // 4. ASSERT: Verify database state changed correctly
    expect($targetUser->fresh()->is_admin)->toBeTrue();

    // 5. ASSERT: Verify side effects occurred
    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.user.toggle_admin',
        'user_id' => $admin->id,
        'data->target_user_id' => $targetUser->id,
    ]);
});
```

### Test Smell Checklist

**❌ Testing implementation details:**
```php
// Bad: verifying mock was called
$service = Mockery::mock(BillingService::class);
$service->shouldReceive('cancelSubscription')->once();
// Problem: Refactoring breaks test even if behavior is correct
```

**✅ Testing user-visible behavior:**
```php
// Good: verifying outcome
$user = User::factory()->withSubscription()->create();

$this->actingAs($user)->delete('/billing/subscription');

expect($user->fresh()->subscription('default'))
    ->onGracePeriod()->toBeTrue()
    ->ends_at->toBeInstanceOf(Carbon::class);
```

**❌ Incomplete assertions:**
```php
// Bad: only checks redirect
$this->actingAs($user)->patch('/profile', ['name' => 'New Name']);
$this->assertRedirect(); // But did the name actually change?
```

**✅ Complete assertions:**
```php
// Good: verifies redirect AND database state
$response = $this->actingAs($user)->patch('/profile', ['name' => 'New Name']);

$response->assertRedirect('/profile');
$response->assertSessionHas('flash.type', 'success');
expect($user->fresh()->name)->toBe('New Name');
```

**❌ Test comments that lie:**
```php
// Bad: comment doesn't match assertion
it('route requires authentication', function () {
    $response = $this->get('/admin/users');
    $response->assertStatus(403); // Comment says "requires auth", but checks 403 not 401/redirect
});
```

**✅ Accurate test comments:**
```php
// Good: comment matches assertion
it('route requires admin role (authenticated non-admins get 403)', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $response = $this->actingAs($user)->get('/admin/users');
    $response->assertStatus(403);
});

it('route redirects guests to login', function () {
    $response = $this->get('/admin/users');
    $response->assertRedirect('/login');
});
```

### Edge Case Coverage Checklist

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

## Commands

```bash
composer dev           # Start Laravel + Vite + Queue
php artisan serve      # Laravel only
npm run dev            # Vite dev server
php artisan test       # Run tests (Pest, parallel)
npm test               # Vitest frontend tests
npm run test:e2e       # Playwright E2E tests
npm run build          # Production build
npm run lint           # ESLint
composer audit         # Security audit (fails on vulnerabilities)
npm audit              # JS vulnerabilities (reports but doesn't block)
php artisan CheckIncompletePayments  # Find failed payments, send reminders
php artisan PruneAuditLogs           # Delete old audit logs
scripts/init.sh        # First-time setup (configure project name, features)
```

## Conventions

**Backend:**
- Form Requests for validation (never inline `$request->validate()`)
- Services for business logic, controllers stay thin
- External API calls in Jobs only (create `app/Jobs/` when needed — not yet created)
- Constructor injection for dependencies
- Custom exceptions in `app/Exceptions/` when needed (currently uses Laravel defaults)

**Frontend:**
- UI primitives in `Components/ui/` (Radix + CVA + `cn()` from `lib/utils`)
- Theme via CSS variables (semantic tokens like `bg-background`, `text-foreground`)
- Forms: Inertia `useForm()` hook
- Icons: Lucide React only
- Shared props via `usePage()`, feature-gated UI via `features` prop
- Custom hooks in `hooks/`: `useMobile`, `useFormValidation`, `useTimezone`, `useUnsavedChanges`
- Shared Inertia props must stay minimal (auth summary + feature flags + flash). Never send whole Eloquent models — use explicit arrays.

**Frontend State Management:**

*Decision Tree: Where to Store State*

- **URL Params** (shareable/bookmarkable): Pagination (`?page=2`), filters (`?status=active`), search (`?q=john`), sort order
- **React useState** (ephemeral UI): Dialog open/closed, form validation errors (before submission), hover/focus state
- **Inertia Props** (server-driven): Current user auth, feature flags, flash messages, paginated data
- **localStorage** (UI preferences NOT in user_settings): Sidebar collapsed, table column widths, last visited tab
- **user_settings table** (sync across devices): Theme (light/dark), timezone, notification preferences

*Common Anti-Pattern:*
```tsx
// ❌ Bad: Mixing URL params and useState for filters
const [status, setStatus] = useState('active'); // local state
const tier = searchParams.get('tier'); // URL param
// Problem: clearFilters only clears one source

// ✅ Good: Single source of truth (URL params only)
const searchParams = new URLSearchParams(window.location.search);
const status = searchParams.get('status') || 'all';
const tier = searchParams.get('tier') || 'all';
function clearFilters() {
    router.get('/users', {}, { preserveState: true }); // clears ALL
}
```

*Inertia Router Fire-and-Forget Behavior (CRITICAL):*
`router.post()`, `router.patch()`, `router.delete()` return immediately, NOT a Promise.

```tsx
// ❌ Bad: Awaiting Inertia router calls
async function deleteUser(id: number) {
    setLoading(true);
    await router.delete(`/users/${id}`); // Returns immediately! await does nothing
    setLoading(false); // Executes before server response
}

// ✅ Good: Use onSuccess callback
function deleteUser(id: number) {
    setLoading(true);
    router.delete(`/users/${id}`, {
        onSuccess: () => setLoading(false),
        onError: () => setLoading(false),
    });
}

// ✅ Better: Use LoadingButton component
<LoadingButton onClick={() => router.delete(`/users/${id}`)}>
    Delete
</LoadingButton>
```

**Accessibility (WCAG 2.1 Level AA Required):**

All new UI components MUST meet these standards:

*Keyboard Navigation:*
- All interactive elements focusable via Tab
- Focus visible (outline or ring-2 ring-offset-2)
- Dialogs trap focus and restore on close
- Esc key closes dialogs/dropdowns

*Semantic HTML:*
- Use `<button>` for actions, `<a>` for navigation
- Use `<label>` for all inputs (not just placeholder)
- Use semantic elements (`<nav>`, `<main>`, `<aside>`, `<article>`)
- Heading hierarchy (single `<h1>`, sequential `<h2>`-`<h6>`)

*ARIA Attributes:*
- `aria-label` on icon-only buttons
- `aria-describedby` for error messages linked to inputs
- `aria-live="polite"` for toast notifications (implemented in `Toast.tsx`)
- `role="alert"` for validation errors

*Color Contrast:*
- Text: ≥4.5:1 for normal text, ≥3:1 for large text (18px+)
- Interactive elements: ≥3:1 against background
- Never rely on color alone (use icons + text)

*Forms:*
- Associate labels with inputs (`htmlFor={id}`)
- Show validation errors below field with `aria-describedby`
- Disable submit button while processing (with loading state)

*Testing Accessibility:*
Before claiming a UI feature done:
1. Can you complete the flow with keyboard only? (no mouse)
2. Do all images have alt text (or `alt=""` for decorative)?
3. Are loading states announced? (`aria-busy="true"`)

*Existing Accessible Components:*
- `Button`, `Dialog`, `Toast`, `LoadingButton` (Radix-based, accessibility built-in)

**Testing:**
- Framework: Pest (not PHPUnit) — use `it()` / `test()` syntax
- Parallel execution: `php artisan test --parallel`
- Frontend: Vitest + @testing-library/react (`npm test`)
- Database: SQLite in-memory for tests
- All auth pages have `.test.tsx` counterparts
- E2E: Playwright (`tests/e2e/`) — auth smoke tests
- **Test quality rules (IMPORTANT):**
  - Assert user-visible behavior, not implementation details — check redirect destinations, session flash content, and final DB state, not just that a mock was called
  - Every test comment must be accurate — if a comment says "route doesn't have X", verify it. Wrong comments hide bugs.
  - Inertia router calls (`router.patch`, `router.post`) are fire-and-forget — when testing hooks/components that wrap them, mock with `onSuccess` callback invocation to simulate real async behavior
  - For every mutation test, verify both the success path AND the final state (e.g., `$user->fresh()->is_admin` after toggle)
  - Edge case coverage required: soft-deleted users, unverified users, null/missing relationships, concurrent operations
- **Boot-time route registration limitation:**
  - Routes conditionally registered at boot time (e.g., `if (config('features.billing.enabled'))` in route files) cannot be tested for both enabled/disabled states in the same test suite
  - Feature flags set in `phpunit.xml` determine which routes are registered at application boot
  - Tests can verify route behavior when enabled (route exists) OR when disabled (route returns 404), but not both
  - Example: BillingFeatureFlagTest is skipped because billing routes are enabled in phpunit.xml
  - Workaround: Test route-specific logic (controllers, middleware) in unit tests; only test route registration in integration tests matching the phpunit.xml config

**Migrations:**
- Always check before adding/dropping columns: `Schema::hasColumn()`
- New columns on existing tables: nullable or with default (never bare NOT NULL)
- Foreign keys: `->constrained()->cascadeOnDelete()` (auto-indexed)
- Feature-conditional migrations: only for whole-table creation (`Schema::hasTable` check). Never gate column additions/removals on feature flags — causes schema drift.

**Code Organization (File Placement):**

- **Controllers:** `/app/Http/Controllers/{Domain}/{Name}Controller.php`
  - Subdirectories: `Admin/`, `Api/`, `Auth/`, `Billing/`, `Settings/`, `Webhook/`
  - Single-action: `{Verb}{Noun}Controller` (e.g., `ExportUsersController`)
  - CRUD: `{Resource}Controller` (e.g., `WebhookEndpointController`)

- **Models:** `/app/Models/{Name}.php` (flat structure, no subdirectories)

- **Services:** `/app/Services/{Name}Service.php` (flat structure)
  - Naming: `{Domain}Service` (e.g., `BillingService`, `WebhookService`)
  - Never `UserService` or `ProjectService` — keep model logic in model

- **Form Requests:** `/app/Http/Requests/{Domain}/{Action}Request.php`
  - Example: `/app/Http/Requests/Auth/LoginRequest.php`
  - Example: `/app/Http/Requests/Admin/UpdateFeatureFlagRequest.php`

- **Policies:** `/app/Policies/{Resource}Policy.php`
  - Register in `AppServiceProvider` if not auto-discovered

- **Middleware:** `/app/Http/Middleware/{Name}Middleware.php`
  - Prefer descriptive names: `EnsureOnboardingCompleted` not `CheckOnboarding`

- **Enums:** `/app/Enums/{Name}.php` (use for fixed sets of values)

- **Jobs:** `/app/Jobs/{Domain}/{Name}Job.php` (create when first needed)

- **Commands:** `/app/Console/Commands/{Name}.php`
  - Signature: `{domain}:{action}` (e.g., `billing:check-incomplete-payments`)

- **React Components:**
  - Pages: `/resources/js/Pages/{Domain}/{Name}.tsx`
  - Shared: `/resources/js/Components/{name}.tsx` (kebab-case)
  - UI primitives: `/resources/js/Components/ui/{name}.tsx`

- **Tests:** Mirror application structure
  - Feature: `/tests/Feature/{Domain}/{Name}Test.php`
  - Unit: `/tests/Unit/{Domain}/{Name}Test.php`

## Key Tables

- `users` — core user data + optional Stripe columns
- `social_accounts` — OAuth credentials (feature-gated)
- `user_settings` — key-value preferences
- `personal_access_tokens` — Sanctum API tokens
- `audit_logs` — activity tracking with IP/user agent
- `webhook_endpoints` — user-configured webhook destinations
- `webhook_deliveries` — outgoing webhook attempt history
- `incoming_webhooks` — received webhooks (GitHub/Stripe)
- `two_factor_authentications` — TOTP secrets + recovery codes
- Stripe tables: `customers`, `subscriptions`, `subscription_items` (Cashier)

## Security Infrastructure

Already implemented — verify before duplicating:
- Rate limiting: registration (5/min), login (5 attempts, IP+email), password reset (3/min), email verification (6/min), API settings (30/min), tokens (20/min), webhooks (30/min), export (10/min), Stripe webhook (120/min)
- CSRF via Sanctum middleware
- Session regeneration on login
- Configurable remember-me duration (`REMEMBER_ME_DAYS` env)
- Audit logging via `AuditService` (login, logout, registration with IP + user agent)
- Custom queued `SendEmailVerificationNotification` listener (overrides framework default via `EventServiceProvider::configureEmailVerification()`)

### Security Patterns & Anti-Patterns

**Input Validation:**

```php
// ✅ Good: Form Request with authorization
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->route('user'))],
            'role' => ['required', Rule::in(['user', 'admin'])], // whitelist
        ];
    }
}

// ❌ Bad: Trusting client input
$user->update($request->all()); // client can send is_admin=1

// ✅ Good: Explicit field list
$user->update($request->only(['name', 'email']));
```

**Authorization:**

```php
// ❌ Bad: Only hiding UI (API endpoint still accessible via curl)
// In Controller: no auth check
// In TSX: {user.is_admin && <DeleteButton />}

// ✅ Good: Authorize in controller
public function destroy(User $user): RedirectResponse
{
    $this->authorize('delete', $user); // throws 403 if unauthorized
    $user->delete();
    return redirect()->route('users.index');
}
```

**SQL Injection Prevention:**

```php
// ❌ Bad: Raw SQL with interpolation
DB::select("SELECT * FROM users WHERE email = '{$request->email}'");

// ✅ Good: Parameter binding
DB::select('SELECT * FROM users WHERE email = ?', [$request->email]);

// ✅ Better: Query builder
User::where('email', $request->email)->first();
```

**XSS Prevention:**

```tsx
// ❌ Bad: User content as HTML
<div dangerouslySetInnerHTML={{__html: comment.body}} />

// ✅ Good: Plain text rendering (React escapes by default)
<div>{comment.body}</div>

// ✅ If HTML needed: Sanitize with DOMPurify
import DOMPurify from 'dompurify';
<div dangerouslySetInnerHTML={{__html: DOMPurify.sanitize(comment.body)}} />
```

**Mass Assignment Protection:**

```php
// ❌ Bad: No protection
class User extends Model {
    // no $fillable or $guarded
}
User::create($request->all()); // client can set is_admin=1

// ✅ Good: Explicit fillable fields
class User extends Model {
    protected $fillable = ['name', 'email', 'password'];
}
```

**CSRF Protection:**

CSRF enabled by default. Only bypass for webhooks with signature verification.

```php
// ❌ Bad: Disabling CSRF without signature verification
protected $except = ['/webhooks/stripe']; // no signature check

// ✅ Good: Signature verification replaces CSRF
protected $except = ['/webhooks/stripe']; // VerifyWebhookSignature middleware validates HMAC
```

**Rate Limiting:**

All auth endpoints already rate-limited. When adding new sensitive endpoints:

```php
Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
    Route::post('/admin/users/{user}/impersonate', [ImpersonationController::class, 'start']);
});
```

**Secrets Management:**

```php
// ❌ In config/services.php (committed to git)
'stripe' => ['secret' => 'sk_live_xxx'], // NEVER

// ✅ In config/services.php (committed to git)
'stripe' => ['secret' => env('STRIPE_SECRET')], // reads from .env
```

## Critical Gotchas

**Billing (DO NOT MODIFY WITHOUT READING):**

- **Why eager loading is required:** Cashier methods like `cancel()` and `swap()` internally access `$subscription->owner` and nested `$subscription->items->subscription` relationships. Without eager loading, each call triggers lazy loading queries, causing N+1 problems and potential race conditions.

- **Detection rule:** If you're calling ANY Cashier method (`cancel`, `resume`, `swap`, `updateQuantity`, `noProrate`, `anchorBillingCycleOn`), you MUST eager load first: `$subscription->load('owner', 'items.subscription')`

- **Error symptom:** `Attempt to read property "stripe_id" on null` when calling `->cancel()` means `owner` wasn't loaded.

- **Pattern to follow:** See `app/Services/BillingService.php` lines 68-70 for correct eager loading pattern.

- **Redis locks:** All subscription mutations MUST use `BillingService` methods — direct Cashier calls will cause race conditions. Redis locks (35s timeout) prevent concurrent operations. If lock acquisition fails, operation is rejected with `ConcurrentOperationException`.

- **Seat constraints:** Team/Enterprise tiers have min 1, max 50 seats for team tier — validate before subscription creation.

**Webhook Signature Verification:**
- Incoming webhooks use HMAC-SHA256 with provider-specific secrets (`config/webhooks.php`)
- Stripe webhook route excluded from CSRF (signature verification replaces it)
- Outgoing webhooks use same HMAC scheme for user endpoints

**Feature Flag Dependencies:**
- Email verification is default-ON (middleware checks `config('features.email_verification.enabled', true)`)
- Social auth auto-detects providers by env var presence (GOOGLE_CLIENT_ID/GITHUB_CLIENT_ID)
- Two-factor setup only shows in settings if `two_factor.enabled`

**Migration Patterns:**
- Never gate column additions/removals on feature flags (causes schema drift)
- Feature-conditional migrations only for whole-table creation (`Schema::hasTable` check)

**Health Check Auth:**
- `/health` endpoint supports 3 modes: token-based, IP allowlist, local-only
- Configure in `config/health.php` — default is local-only in production

**Admin Cache (`AdminCacheKey`):**
- Dashboard stats are cached with 5-min TTL (`AdminCacheKey::DEFAULT_TTL`)
- Any mutation that changes user count, subscription state, token count, or webhook stats MUST call `Cache::forget(AdminCacheKey::RELEVANT_KEY->value)` — stale admin dashboards are a known bug class
- User mutations (toggle admin, deactivate, restore) invalidate `DASHBOARD_STATS`
- Billing mutations (subscribe, cancel, resume, swap) must invalidate `BILLING_STATS` and `BILLING_TIER_DIST`
- Token/webhook CRUD must invalidate their respective cache keys

**Relationship Loading with SoftDeletes:**
- When loading relationships where the related model uses `SoftDeletes`, use `->load(['relation' => fn ($q) => $q->withTrashed()])` if the display context needs to show deleted records (e.g., admin views)
- Always use null-safe operator (`?->`) with fallback when accessing relationship properties that could be null: `$model->owner?->name ?? '[Deleted User]'`

**Impersonation:**
- Stop-impersonation route must NOT use `verified` middleware — the impersonated user may be unverified
- The route is intentionally outside the admin middleware group because the impersonated user is not an admin

## CI/CD

GitHub Actions (`.github/workflows/ci.yml`):
- PHP tests with PCOV coverage (MySQL 8.0, 4 parallel workers)
- JS tests with Vitest
- Build verification (TypeScript + ESLint + production build)
- Code quality: Laravel Pint
- Security: `composer audit` + `npm audit` (npm audit uses `continue-on-error` — reports but doesn't block. Tighten before production launch.)

Note: Local tests use SQLite in-memory, CI uses MySQL 8.0.

## Deployment

- `deploy/` — nginx gzip + static cache configs, supervisor config
- `scripts/` — `vps-setup.sh`, `vps-verify.sh`, `setup-horizon.sh`, `init.sh`
- No Docker/containerization (VPS-based deployment)
- Trusted proxies not configured — add `TrustProxies` middleware if deploying behind load balancer/CDN
