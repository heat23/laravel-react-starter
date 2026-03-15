/v Fix the following admin panel audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.
Admin prefix: /admin.

## Findings to Fix

Work through these in order. For each one: write the test first (TDD for backend, test-after for UI), implement the fix, run the verification command, then move to the next.

### Fix 1: ADM-OPS-001 — Before/after value capture in audit trail (P1, 4h est.)
**Problem:** Admin mutations log only the new state, not the old state. `AdminUsersController.php:125-129` logs `is_admin => $user->is_admin` (post-mutation). There is no way to know what changed from the audit trail.
**Files:** `app/Http/Controllers/Admin/AdminUsersController.php`, `resources/js/Pages/Admin/AuditLogs/Show.tsx`
**Test first:** `tests/Feature/Admin/AdminUsersTest.php` — toggle admin, retrieve the audit log entry, assert metadata contains both `old_is_admin` and `new_is_admin` (or a structured `changes` key).
**Implementation:**
1. In `toggleAdmin`, capture old value before mutation:
```php
$wasAdmin = $user->is_admin;
$user->is_admin = !$user->is_admin;
$user->save();
// ...
$this->auditService->log('admin.toggle_admin', [
    'target_user_id' => $user->id,
    'target_email' => $user->email,
    'changes' => ['is_admin' => ['from' => $wasAdmin, 'to' => $user->is_admin]],
]);
```
2. Apply the same pattern to `toggleActive` and `bulkDeactivate` (capture `deleted_at` state).
3. In `AuditLogs/Show.tsx`, add rendering for the `changes` metadata key — display as "field: old → new".
**Verify:** `php artisan test --filter=toggleAdmin`

### Fix 2: ADM-OPS-003 — Audit log for admin data views and exports (P2, 2h est.)
**Problem:** Viewing user details (`AdminUsersController::show`) and exporting audit logs (`AdminAuditLogController::export`) are not audit-logged. Admin PII access leaves no trail.
**Files:** `app/Http/Controllers/Admin/AdminUsersController.php`, `app/Http/Controllers/Admin/AdminAuditLogController.php`
**Test first:** `tests/Feature/Admin/AdminUsersTest.php` — view user, assert audit log entry created with event `admin.user_viewed`. `tests/Feature/Admin/AdminAuditLogTest.php` — export, assert `admin.audit_logs_exported` entry.
**Implementation:**
1. In `AdminUsersController::show`, inject `AuditService` (already available via constructor) and add:
```php
$this->auditService->log('admin.user_viewed', [
    'target_user_id' => $user->id,
    'target_email' => $user->email,
]);
```
2. In `AdminAuditLogController::export`, inject `AuditService` and add:
```php
$this->auditService->log('admin.audit_logs_exported', [
    'filters' => $request->validated(),
]);
```
Note: The `export` method returns a StreamedResponse so log BEFORE the return.
**Verify:** `php artisan test --filter=AdminAuditLog`

### Fix 3: ADM-PM-001 — User CSV export from admin panel (P1, 3h est.)
**Problem:** Admin Users Index has no export. Audit Logs have CSV export via `ExportButton`. The `CsvExport` support class exists at `app/Support/CsvExport.php`.
**Files:**
- Create `app/Http/Requests/Admin/AdminUserExportRequest.php`
- `app/Http/Controllers/Admin/AdminUsersController.php` (add export method)
- `routes/admin.php` (add route)
- `resources/js/Pages/Admin/Users/Index.tsx` (add ExportButton)
**Test first:** `tests/Feature/Admin/AdminUsersTest.php` — call export endpoint, assert CSV response with headers, assert content contains user data.
**Implementation:**
1. Create `AdminUserExportRequest` extending the existing `AdminUserIndexRequest` (reuse filter validation).
2. Add `export` method to `AdminUsersController` following the audit log export pattern at `AdminAuditLogController.php:44-79`:
```php
public function export(AdminUserExportRequest $request): StreamedResponse
{
    $query = $this->buildQuery($request->validated()); // extract shared query building
    $maxRows = config('pagination.export.max_rows', 10000);

    return response()->streamDownload(function () use ($query, $maxRows) {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['ID', 'Name', 'Email', 'Admin', 'Verified', 'Last Login', 'Created', 'Status']);
        // ... iterate with lazyByIdDesc, apply formula injection protection
        fclose($handle);
    }, 'users-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
}
```
3. Add route: `Route::get('/users/export', [AdminUsersController::class, 'export'])->middleware('throttle:10,1')->name('users.export');` — place BEFORE the `{user}` route to avoid conflict.
4. Add `ExportButton` to `Users/Index.tsx` PageHeader actions, passing current filter params.
5. Audit log the export (per Fix 2 pattern).
**Verify:** `php artisan test --filter=AdminUser`

### Fix 4: ADM-PM-004 — Subscription CSV export (P1, 3h est.)
**Problem:** Billing Subscriptions has search/filter/sort but no CSV export. `Subscriptions.tsx` PageHeader only has "Back to Billing" link.
**Files:**
- Create `app/Http/Requests/Admin/AdminSubscriptionExportRequest.php` (if needed, or reuse index request)
- `app/Http/Controllers/Admin/AdminBillingController.php` (add export method)
- `routes/admin.php` (add route inside billing feature gate)
- `resources/js/Pages/Admin/Billing/Subscriptions.tsx` (add ExportButton)
**Test first:** `tests/Feature/Admin/AdminBillingTest.php` — call export, assert CSV response with subscription data.
**Implementation:** Follow the same streaming CSV pattern as audit log export. Include columns: User Name, User Email, Tier, Status, Quantity, Trial Ends, Ends At, Created At, Stripe ID.
Add route inside the `if (config('features.billing.enabled'))` block:
```php
Route::get('/billing/subscriptions/export', [AdminBillingController::class, 'export'])
    ->middleware('throttle:10,1')
    ->name('billing.subscriptions.export');
```
Place before the `{subscription}` route.
**Verify:** `php artisan test --filter=AdminBilling`

### Fix 5: ADM-PM-005 — Admin-initiated password reset (P2, 2h est.)
**Problem:** Admin cannot trigger a password reset email for a user from the admin panel. Common support workflow.
**Files:**
- `app/Http/Controllers/Admin/AdminUsersController.php` (add sendPasswordReset method)
- `routes/admin.php` (add route)
- `resources/js/Pages/Admin/Users/Show.tsx` (add button)
**Test first:** `tests/Feature/Admin/AdminUsersTest.php` — call sendPasswordReset endpoint, assert password reset notification was sent, assert audit log created.
**Implementation:**
1. Add method to `AdminUsersController`:
```php
public function sendPasswordReset(Request $request, User $user): RedirectResponse
{
    if (!$user->hasPassword()) {
        return back()->with('error', 'User has no password (OAuth-only account).');
    }

    $user->sendPasswordResetNotification(
        app('auth.password.broker')->createToken($user)
    );

    $this->auditService->log('admin.password_reset_sent', [
        'target_user_id' => $user->id,
        'target_email' => $user->email,
    ]);

    return back()->with('success', 'Password reset email sent.');
}
```
2. Add route: `Route::post('/users/{user}/send-password-reset', ...)->middleware('throttle:5,1')->name('users.send-password-reset');`
3. Add button to Users/Show.tsx (only show when `user.has_password && !user.deleted_at`).
**Verify:** `php artisan test --filter=passwordReset`

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

Commit with: `git add -u && git commit -m "feat(admin): audit trail before/after capture, user & subscription CSV export, admin password reset"`
