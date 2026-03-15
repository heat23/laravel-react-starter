/v Fix the following admin panel audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.
Admin prefix: /admin.

## Findings to Fix

Work through these in order. For each one: write the test first (TDD for backend), implement, verify, then move to the next.

### Fix 1: ADM-PM-003 / ADM-OPS-002 — Failed job management admin page (P1, 8h est.)
**Problem:** The System page shows failed_jobs count (`AdminSystemController.php:33`) but provides no way to view, retry, or delete failed jobs. Admins must SSH to the server.
**Files to create:**
- `app/Http/Controllers/Admin/AdminFailedJobsController.php`
- `app/Http/Requests/Admin/AdminFailedJobIndexRequest.php`
- `resources/js/Pages/Admin/FailedJobs/Index.tsx`
- `resources/js/Pages/Admin/FailedJobs/Show.tsx`
- `tests/Feature/Admin/AdminFailedJobsTest.php`

**Files to modify:**
- `routes/admin.php` (add routes)
- `resources/js/config/admin-navigation.ts` (add nav item in System group)

**Test first:** `tests/Feature/Admin/AdminFailedJobsTest.php`:
- `it('shows failed jobs list for admin')` — seed failed_jobs table, visit index, assert page renders with jobs
- `it('shows failed job detail')` — visit show, assert payload and exception displayed
- `it('retries a failed job')` — POST retry, assert job removed from failed_jobs
- `it('deletes a failed job')` — DELETE, assert job removed
- `it('requires admin')` — non-admin gets 403

**Implementation:**

1. **Controller** (`AdminFailedJobsController`):
```php
class AdminFailedJobsController extends Controller
{
    public function index(AdminFailedJobIndexRequest $request): Response
    {
        $jobs = DB::table('failed_jobs')
            ->latest('failed_at')
            ->paginate(config('pagination.admin.failed_jobs', 25))
            ->through(fn ($job) => [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'payload_summary' => $this->extractJobName($job->payload),
                'failed_at' => $job->failed_at,
                'exception_summary' => Str::limit($job->exception, 200),
            ]);

        return Inertia::render('Admin/FailedJobs/Index', ['jobs' => $jobs]);
    }

    public function show(int $id): Response { /* full payload + exception */ }
    public function retry(int $id): RedirectResponse { /* Artisan::call('queue:retry', ['id' => $uuid]) */ }
    public function destroy(int $id): RedirectResponse { /* DB::table('failed_jobs')->where('id', $id)->delete() */ }

    private function extractJobName(string $payload): string
    {
        $data = json_decode($payload, true);
        return class_basename($data['displayName'] ?? $data['job'] ?? 'Unknown');
    }
}
```

2. **Routes** (add to admin.php in the main group):
```php
Route::get('/failed-jobs', [AdminFailedJobsController::class, 'index'])->name('failed-jobs.index');
Route::get('/failed-jobs/{id}', [AdminFailedJobsController::class, 'show'])->name('failed-jobs.show');
Route::post('/failed-jobs/{id}/retry', [AdminFailedJobsController::class, 'retry'])
    ->middleware('throttle:10,1')
    ->name('failed-jobs.retry');
Route::delete('/failed-jobs/{id}', [AdminFailedJobsController::class, 'destroy'])
    ->middleware('throttle:10,1')
    ->name('failed-jobs.destroy');
```

3. **Navigation** — add to System group in `admin-navigation.ts`:
```ts
{ href: "/admin/failed-jobs", label: "Failed Jobs", icon: AlertCircle },
```

4. **Frontend pages:** Follow patterns from existing admin pages:
- Index: Use `AdminDataTable` with pagination, show job name, queue, failed_at, exception summary
- Show: Full exception trace in `<pre>` block, payload JSON viewer, Retry and Delete buttons with `ConfirmDialog`
- Use `AdminLayout`, `PageHeader`, breadcrumbs on Show page

5. **Audit log** retry and delete actions.

6. **Cache invalidation:** After retry/delete, no cache key needed since this data isn't cached.

**Verify:** `php artisan test --filter=AdminFailedJobs && npm test`

### Fix 2: ADM-AI-002 — Data integrity checks page (P2, 8h est.)
**Problem:** No tools to detect orphaned records, stale data, or inconsistencies. Common AI-built blind spot.
**Files to create:**
- `app/Services/DataHealthService.php`
- `app/Http/Controllers/Admin/AdminDataHealthController.php`
- `resources/js/Pages/Admin/DataHealth.tsx`
- `tests/Feature/Admin/AdminDataHealthTest.php`

**Files to modify:**
- `routes/admin.php`
- `resources/js/config/admin-navigation.ts`

**Test first:** Seed orphan records, run checks, assert they're detected.

**Implementation:**
1. `DataHealthService` with check methods:
```php
class DataHealthService
{
    public function runAllChecks(): array
    {
        return [
            'orphaned_tokens' => $this->checkOrphanedTokens(),
            'orphaned_subscriptions' => $this->checkOrphanedSubscriptions(),
            'orphaned_audit_logs' => $this->checkOrphanedAuditLogs(),
            'stale_webhook_deliveries' => $this->checkStaleWebhookDeliveries(),
            'expired_tokens' => $this->checkExpiredTokens(),
        ];
    }

    private function checkOrphanedTokens(): array
    {
        $count = DB::table('personal_access_tokens')
            ->leftJoin('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->whereNull('users.id')
            ->count();
        return ['status' => $count === 0 ? 'ok' : 'warning', 'count' => $count, 'description' => 'Tokens without a valid user'];
    }
    // ... similar for other checks
}
```
2. Controller returns check results to an Inertia page with status cards and fix-action buttons.
3. Add to System nav group.

**Verify:** `php artisan test --filter=DataHealth`

### Fix 3: ADM-AI-003 — Admin alerting for critical events (P2, 12h est.)
**Problem:** When webhook failure rate spikes, failed jobs accumulate, or health checks fail, admins must manually check the dashboard. No proactive alerting.

This is a larger feature. Implement a minimal version:

**Files to create:**
- `app/Console/Commands/AdminHealthAlertCommand.php`
- `app/Notifications/AdminHealthAlertNotification.php`
- `tests/Feature/Commands/AdminHealthAlertTest.php`

**Implementation (minimal viable version):**
1. Create an artisan command `admin:health-alert` that:
   - Runs health checks via `HealthCheckService`
   - Checks failed_jobs count
   - Checks webhook failure rate (last 24h)
   - If any threshold is breached, sends notification to all admin users
2. Schedule the command every 15 minutes via Laravel scheduler
3. Add threshold config to `config/health.php`:
```php
'alert_thresholds' => [
    'failed_jobs' => env('HEALTH_ALERT_FAILED_JOBS', 10),
    'webhook_failure_rate' => env('HEALTH_ALERT_WEBHOOK_FAILURE_RATE', 25),
],
```
4. Use database notification channel (uses existing notifications table)
5. Show alerts on admin dashboard if any exist

**Verify:** `php artisan test --filter=AdminHealthAlert`

## After All Fixes

Run the full verification suite:
```bash
php artisan test --parallel
npm test
vendor/bin/phpstan analyse
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "feat(admin): failed job management, data integrity checks, health alerting"`
