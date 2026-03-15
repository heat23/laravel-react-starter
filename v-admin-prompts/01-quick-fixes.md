/v Fix the following admin panel audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.
Admin prefix: /admin.

## Findings to Fix

Work through these in order. For each one: write the test first (TDD for backend, test-after for UI), implement the fix, run the verification command, then move to the next.

### Fix 1: ADM-UX-001 — Wrap AdminLayout children in ErrorBoundary (P1, 0.5h est.)
**Problem:** An `ErrorBoundary` component exists at `resources/js/Components/ui/error-boundary.tsx` but is unused. `AdminLayout.tsx` renders `{children}` directly. A runtime error in any admin page crashes to a white screen.
**Files:** `resources/js/Layouts/AdminLayout.tsx`
**Test first:** `resources/js/Layouts/AdminLayout.test.tsx` — render AdminLayout with a child component that throws, assert the fallback UI renders instead of crashing.
**Implementation:** Import `ErrorBoundary` from `@/Components/ui/error-boundary` and wrap `{children}` inside the `SidebarLayout`:
```tsx
<SidebarLayout ...>
  <ErrorBoundary>
    {children}
  </ErrorBoundary>
</SidebarLayout>
```
**Verify:** `npm test -- --run AdminLayout`

### Fix 2: ADM-QA-002 — AuditLog::user() relationship missing withTrashed (P1, 0.5h est.)
**Problem:** `AuditLog::user()` at `app/Models/AuditLog.php` line ~32 uses `belongsTo(User::class)` without `withTrashed()`. Audit logs from soft-deleted users show "System" instead of their actual name.
**Files:** `app/Models/AuditLog.php`
**Test first:** `tests/Feature/Admin/AdminAuditLogTest.php` — create a user, create an audit log, soft-delete the user, load audit log with user relation, assert user name is still available.
**Implementation:** Change the user() relationship:
```php
return $this->belongsTo(User::class)->withTrashed();
```
**Verify:** `php artisan test --filter=AdminAuditLog`

### Fix 3: ADM-QA-006 — Minimum admin count check to prevent lockout (P1, 1h est.)
**Problem:** `toggleAdmin` in `AdminUsersController.php:114-134` prevents self-demotion but not demoting the last remaining admin, risking complete admin lockout.
**Files:** `app/Http/Controllers/Admin/AdminUsersController.php` (toggleAdmin method)
**Test first:** `tests/Feature/Admin/AdminUsersTest.php` — create exactly 2 admin users, attempt to remove admin from one as the other, assert it's prevented when it would leave only 1 admin.
**Implementation:** Before `$user->is_admin = !$user->is_admin;`, add:
```php
if ($user->is_admin && User::where('is_admin', true)->count() <= 2) {
    return back()->with('error', 'Cannot remove admin status. At least two admin accounts must exist.');
}
```
**Verify:** `php artisan test --filter=toggleAdmin`

### Fix 4: ADM-QA-001 — Hide toggle-admin for deactivated users in UI (P1, 1h est.)
**Problem:** The `toggle-admin` route at `routes/admin.php:33` lacks `->withTrashed()`, so toggling admin on a soft-deleted user returns 404. But the UI still shows the action.
**Files:** `resources/js/Pages/Admin/Users/Index.tsx` (line ~261), `resources/js/Pages/Admin/Users/Show.tsx` (lines ~63-69)
**Test first:** Not needed — this is a UI conditional rendering fix.
**Implementation:**
- In `Users/Index.tsx`, wrap the toggle-admin dropdown item: `{!user.deleted_at && (<DropdownMenuItem onClick={...}>...toggle admin...</DropdownMenuItem>)}`
- In `Users/Show.tsx`, add `!user.deleted_at &&` condition before the Make Admin/Remove Admin button.
**Verify:** `npm test -- --run Users`

### Fix 5: ADM-QA-005 — Self-action prevention on User Show page (P1, 1h est.)
**Problem:** On User Show page (`Users/Show.tsx:63-75`), toggle-admin and deactivate buttons are shown even when viewing your own profile. Backend prevents but UX is poor.
**Files:** `resources/js/Pages/Admin/Users/Show.tsx`
**Test first:** Not needed — UI conditional rendering.
**Implementation:** Add conditions to disable/hide buttons when `user.id === currentUserId`:
- Disable or hide "Make Admin"/"Remove Admin" button when viewing self
- Disable or hide "Deactivate" button when viewing self
- The impersonate guard already exists (line 77)
**Verify:** `npm test -- --run Users`

### Fix 6: ADM-UX-003 — Add Clear Filters to Billing Subscriptions (P1, 0.5h est.)
**Problem:** `Billing/Subscriptions.tsx:90-99` — AdminDataTable has no `emptyAction` prop. When filters return zero results, there's no way to clear them from the empty state.
**Files:** `resources/js/Pages/Admin/Billing/Subscriptions.tsx`
**Implementation:**
1. Destructure `clearFilters` from `useAdminFilters` (line 22)
2. Add `emptyAction` prop to `AdminDataTable` following the pattern from `Users/Index.tsx:171-177`:
```tsx
emptyAction={
  (filters.search || filters.status || filters.tier) ? (
    <Button variant="outline" size="sm" onClick={clearFilters}>Clear filters</Button>
  ) : undefined
}
```
**Verify:** `npm test -- --run Subscriptions`

### Fix 7: ADM-QA-003 — Create Form Request for bulkDeactivate (P2, 1h est.)
**Problem:** `AdminUsersController.php:138-141` uses inline `$request->validate()` instead of a Form Request. All other admin methods use Form Requests.
**Files:** Create `app/Http/Requests/Admin/AdminBulkDeactivateRequest.php`, update `AdminUsersController.php`
**Test first:** Existing tests should continue passing.
**Implementation:**
1. Create `AdminBulkDeactivateRequest` with `authorize()` returning `$this->user()->isAdmin()` and `rules()` matching the inline validation
2. Update `bulkDeactivate` method signature to use the new request class
3. Remove inline validation
**Verify:** `php artisan test --filter=bulkDeactivate`

### Fix 8: ADM-QA-007 — Add response.ok check to Feature Flags fetch calls (P2, 0.5h est.)
**Problem:** `FeatureFlags/Index.tsx:88-98` and `115-128` — `fetch()` doesn't reject on HTTP errors. Error responses could be silently processed as data.
**Files:** `resources/js/Pages/Admin/FeatureFlags/Index.tsx`
**Implementation:** After each `fetch()` call, add:
```tsx
if (!response.ok) {
  throw new Error(`Request failed: ${response.status}`);
}
```
Add this to both `loadUserOverrides` (after line 91) and `handleSearchUsers` (after line 121).
**Verify:** `npm test -- --run FeatureFlags`

### Fix 9: ADM-UX-006 — Focus management after bulk deactivation (P2, 0.5h est.)
**Problem:** After bulk deactivation, `selectedIds` is cleared (`Users/Index.tsx:97`) which removes the bulk action bar from DOM. Focus is lost.
**Files:** `resources/js/Pages/Admin/Users/Index.tsx`
**Implementation:** After `setSelectedIds(new Set())` in the `onSuccess` callback (line 97), add:
```tsx
requestAnimationFrame(() => searchInputRef.current?.focus());
```
**Verify:** Manual test — select users, bulk deactivate, verify focus moves to search input.

### Fix 10: ADM-QA-004 — Clear selection on page change (P2, 1h est.)
**Problem:** `selectedIds` persists across pagination. Users selected on page 1 remain counted on page 2 but are invisible.
**Files:** `resources/js/Pages/Admin/Users/Index.tsx`
**Implementation:** Add a `useEffect` that clears selection when page changes:
```tsx
useEffect(() => {
  setSelectedIds(new Set());
}, [users.current_page]);
```
Place after the existing `useState` declarations.
**Verify:** `npm test -- --run Users`

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

Commit with: `git add -u && git commit -m "fix(admin): quick fixes — ErrorBoundary, withTrashed, self-action guards, clear filters, form request"`
