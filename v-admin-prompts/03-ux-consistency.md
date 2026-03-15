/v Fix the following admin panel audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.
Admin prefix: /admin.

## Findings to Fix

Work through these in order. For each one: implement the fix, run the verification command, then move to the next. These are all frontend changes.

### Fix 1: ADM-UX-002 — Add keyboard shortcuts to all admin list pages (P1, 1h est.)
**Problem:** `useAdminKeyboardShortcuts` is only wired up in `Users/Index.tsx:33`. AuditLogs/Index and Billing/Subscriptions have search and pagination but no keyboard shortcuts (/ for search, n/p for pagination).
**Files:** `resources/js/Pages/Admin/AuditLogs/Index.tsx`, `resources/js/Pages/Admin/Billing/Subscriptions.tsx`

**Implementation for AuditLogs/Index.tsx:**
1. Import `useAdminKeyboardShortcuts` from `@/hooks/useAdminKeyboardShortcuts`
2. Import `useRef` (already imported via `useState`)
3. Create a `searchInputRef = useRef<HTMLInputElement>(null)` — but note AuditLogs uses a Select for event type, not a text search. The first focusable filter element should be targeted. Since there's no search input on this page (just select dropdowns and date inputs), wire `/` to focus the event type Select trigger or skip it. Actually, the User ID input is the closest equivalent — add a ref to it.
4. Get `currentPage` and `lastPage` from `logs`:
```tsx
const currentPage = logs.current_page;
const lastPage = logs.last_page;
useAdminKeyboardShortcuts({
    onSearch: () => userIdInputRef.current?.focus(),
    onNextPage: currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
});
```
5. Add `ref={userIdInputRef}` to the User ID Input element.

**Implementation for Billing/Subscriptions.tsx:**
1. Import `useAdminKeyboardShortcuts` and `useRef`
2. Create `searchInputRef = useRef<HTMLInputElement>(null)`
3. Add `ref={searchInputRef}` to the search Input
4. Wire up the hook:
```tsx
const currentPage = subscriptions.current_page;
const lastPage = subscriptions.last_page;
useAdminKeyboardShortcuts({
    onSearch: () => searchInputRef.current?.focus(),
    onNextPage: currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
});
```
**Verify:** `npm test -- --run AuditLogs Subscriptions`

### Fix 2: ADM-UX-004 — Improve Audit Logs filter state model (P2, 2h est.)
**Problem:** `AuditLogs/Index.tsx:28-30` uses local `useState` for `from`, `to`, `userId` that only syncs to URL on "Apply Filters" click. Typed-but-unapplied values lost on navigation.
**Files:** `resources/js/Pages/Admin/AuditLogs/Index.tsx`
**Implementation:** Add a subtle visual indicator when filters are "dirty" (typed but not applied):
1. Compute `hasPendingFilters`:
```tsx
const hasPendingFilters =
    (from !== (filters.from ?? "")) ||
    (to !== (filters.to ?? "")) ||
    (userId !== (filters.user_id ?? ""));
```
2. Change the "Apply Filters" button to show visual feedback when pending:
```tsx
<Button
    variant={hasPendingFilters ? "default" : "outline"}
    size="default"
    onClick={...}
>
    {hasPendingFilters ? "Apply Filters" : "Filters Applied"}
</Button>
```
This is a lighter-touch approach than switching to debounced URL sync (which would cause server requests on every keystroke in date inputs).
**Verify:** `npm test -- --run AuditLogs`

### Fix 3: ADM-UX-005 — Add navigation loading overlay to Feature Flags (P2, 0.5h est.)
**Problem:** `FeatureFlags/Index.tsx` doesn't use `useNavigationState`. Mutations show loading via ConfirmDialog but page content doesn't indicate in-progress state.
**Files:** `resources/js/Pages/Admin/FeatureFlags/Index.tsx`
**Implementation:**
1. Import `useNavigationState` from `@/hooks/useNavigationState`
2. Add `const isNavigating = useNavigationState();`
3. Apply transition to the content area (the Card wrapping the table):
```tsx
<Card className={isNavigating ? "opacity-50 pointer-events-none transition-opacity" : "transition-opacity"}>
```
**Verify:** `npm test -- --run FeatureFlags`

### Fix 4: ADM-DES-001 — Migrate chart pages to shared AdminCharts components (P1, 4h est.)
**Problem:** `AdminCharts.tsx` exports `AdminAreaChart`, `AdminBarChart`, `AdminPieChart` with built-in empty states, consistent heights, gradient/colors, and animations. Only the main Dashboard uses them. 6+ pages build charts inline with inconsistent heights (250/300), colors (hardcoded HSL vs semantic), and animation timings.

**Files to modify:**
- `resources/js/Pages/Admin/Billing/Dashboard.tsx`
- `resources/js/Pages/Admin/Webhooks/Dashboard.tsx`
- `resources/js/Pages/Admin/Notifications/Dashboard.tsx`
- `resources/js/Pages/Admin/TwoFactor/Dashboard.tsx`
- `resources/js/Pages/Admin/SocialAuth/Dashboard.tsx`
- `resources/js/Components/admin/AdminCharts.tsx` (may need minor extensions)

**Implementation strategy:**
First read `resources/js/Components/admin/AdminCharts.tsx` to understand the shared component API.

For each page:
1. Replace inline `AreaChart`/`BarChart`/`PieChart` blocks with `AdminAreaChart`/`AdminBarChart`/`AdminPieChart`
2. Pass the appropriate props (data, dataKey, name, emptyIcon, emptyTitle, emptyDescription)
3. For the Billing Dashboard's status breakdown bar chart which uses per-cell coloring from `SUBSCRIPTION_STATUS_COLORS`, you may need to extend `AdminBarChart` to accept a `cellColorFn` prop or `children` for custom Cell rendering
4. For the Webhooks Dashboard's stacked bar chart (success + failed), this may not fit the shared component pattern. If the shared components don't support multi-series or stacked bars, leave it inline but fix the hardcoded HSL colors to use `hsl(var(--success))` and `hsl(var(--destructive))`
5. Remove unused direct imports of Recharts primitives from each page after migration

Also fix ADM-DES-004 (hardcoded colors):
- `Webhooks/Dashboard.tsx:71-72`: Change `hsl(142 71% 45%)` to `hsl(var(--success))` and `hsl(0 84% 60%)` to `hsl(var(--destructive))`

Also fix ADM-DES-002 (Billing secondary stats):
- `Billing/Dashboard.tsx:69-103`: Replace the 4 hand-built secondary stat cards with a second `AdminStatsGrid` row

**Verify:** `npm test && npm run build`

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

Commit with: `git add -u && git commit -m "fix(admin): keyboard shortcuts on all list pages, chart component consolidation, filter UX improvements"`
