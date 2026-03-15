# ADMIN_AUDIT_REPORT
generated: 2026-03-15
status: ready
stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4
depth: thorough
admin_prefix: /admin

## FEATURE_INVENTORY_MATRIX

| Resource | List | Create | Edit | Delete | Search | Filter | Sort | Bulk | Export | Audit Log |
|----------|------|--------|------|--------|--------|--------|------|------|--------|-----------|
| User | Y | N | N | Y (soft) | Y | Y | Y | Y (deactivate) | N | Y |
| AuditLog | Y | N/A | N/A | N | N | Y | N | N | Y (CSV) | N/A |
| FeatureFlagOverride | Y | Y | Y | Y | Y (users) | N | N | Y (clear all) | N | Y |
| Subscription (Cashier) | Y | N | N | N | Y | Y | Y | N | N | Y |
| WebhookEndpoint | N (stats) | N | N | N | N | N | N | N | N | N |
| WebhookDelivery | N (failures) | N/A | N/A | N | N | N | N | N | N | N |
| PersonalAccessToken | N (stats) | N | N | N | N | N | N | N | N | N |
| SocialAccount | N (stats) | N | N | N | N | N | N | N | N | N |

## EXECUTIVE_SUMMARY

### Admin Panel Score: 7.4/10
### Coverage: 7/8 models with admin representation (88%)
### Critical Findings (P0): 0
### Important Findings (P1): 10
### Polish Items (P2): 14

### Domain Scores

| Domain | Persona | Score | Findings | Weight |
|--------|---------|-------|----------|--------|
| Functional Completeness | PM | 7.0 | 5 | 35% |
| Visual Craft & Consistency | Designer | 7.5 | 10 | 10% |
| Usability & Interaction | UX Developer | 8.0 | 7 | 15% |
| Edge Cases & Robustness | QA Engineer | 7.5 | 7 | 15% |
| Audit Trail & Security | Operations | 7.0 | 3 | 20% |
| AI-Built Blind Spots | Cross-cutting | 7.5 | 3 | 5% |

## FINDINGS

### P1_IMPORTANT

#### ADM-PM-001: No user CSV export from admin panel
- **Domain:** Functional Completeness
- **Confidence:** high
- **Description:** Admin Users Index has search, filter, sort, bulk deactivate but no export. Audit logs have CSV export via `ExportButton`. The `CsvExport` support class exists but is not used for users.
- **Evidence:** `app/Http/Controllers/Admin/AdminUsersController.php` — no export method. Compare with `AdminAuditLogController.php:44-79` which has the export pattern.
- **Fix:** Add export route, controller method, and `ExportButton` to Users/Index.tsx page header.
- **Effort:** 3h

#### ADM-PM-002: Feature-gated admin sections are read-only dashboards
- **Domain:** Functional Completeness
- **Confidence:** high
- **Description:** Webhooks, API Tokens, Social Auth, Two-Factor, and Notifications admin pages show aggregate statistics only. No way to manage individual records (revoke token, disable endpoint, unlink account).
- **Evidence:** `AdminTokensController.php` — single `__invoke` returning stats. `AdminWebhooksController.php` — stats + chart + failures list but no actions.
- **Fix:** Add list/detail views with management actions for tokens and webhook endpoints first.
- **Effort:** 16h

#### ADM-PM-003: No failed job management from admin panel
- **Domain:** Functional Completeness
- **Confidence:** high
- **Description:** System page shows failed_jobs count (`AdminSystemController.php:33`) but provides no way to view, retry, or delete failed jobs.
- **Fix:** Create AdminFailedJobsController with list/show/retry/delete.
- **Effort:** 8h

#### ADM-PM-004: Subscription list has no export capability
- **Domain:** Functional Completeness
- **Confidence:** high
- **Description:** Billing Subscriptions has search/filter/sort but no CSV export. `Subscriptions.tsx` PageHeader actions only contain "Back to Billing" link.
- **Fix:** Add export endpoint following audit log export pattern.
- **Effort:** 3h

#### ADM-OPS-001: No before/after value capture in audit trail
- **Domain:** Audit Trail & Security
- **Confidence:** high
- **Description:** Admin mutations log the action and new state but not previous state. `AdminUsersController.php:125-129` logs `is_admin => $user->is_admin` (new value only). AuditService accepts flat metadata with no before/after structure.
- **Fix:** Capture old values before mutation, pass as structured `{old: ..., new: ...}` metadata.
- **Effort:** 4h

#### ADM-OPS-002: No failed job operational tooling
- **Domain:** Audit Trail & Security
- **Confidence:** high
- **Description:** System page shows count only (`AdminSystemController.php:33`). No list, detail, retry, or clear. Forces SSH for job management.
- **Fix:** Dedicated Failed Jobs admin page with full management.
- **Effort:** 8h (shared with ADM-PM-003)

#### ADM-UX-001: Admin pages not wrapped in ErrorBoundary
- **Domain:** Usability & Interaction
- **Confidence:** high
- **Description:** `Components/ui/error-boundary.tsx` exists with polished fallback UI but is unused. `AdminLayout.tsx` renders children directly. Runtime errors crash to white screen.
- **Fix:** Wrap `{children}` with `<ErrorBoundary>` in AdminLayout.tsx.
- **Effort:** 0.5h

#### ADM-UX-002: Keyboard shortcuts only on Users Index
- **Domain:** Usability & Interaction
- **Confidence:** high
- **Description:** `useAdminKeyboardShortcuts` (/ for search, n/p for pagination) only used in `Users/Index.tsx:33`. Missing from AuditLogs/Index and Billing/Subscriptions.
- **Fix:** Add hook to all list pages with search and pagination.
- **Effort:** 1h

#### ADM-UX-003: Billing Subscriptions has no Clear Filters button
- **Domain:** Usability & Interaction
- **Confidence:** high
- **Description:** Empty state says "No subscriptions match your current filters" but has no `emptyAction`. Users and AuditLogs pages both provide Clear Filters. UX dead end.
- **Fix:** Destructure `clearFilters` from `useAdminFilters`, pass `emptyAction` to `AdminDataTable`.
- **Effort:** 0.5h

#### ADM-QA-006: Single-admin lockout risk
- **Domain:** Edge Cases & Robustness
- **Confidence:** high
- **Description:** `toggleAdmin` prevents self-demotion (`AdminUsersController.php:116-118`) but not demoting the last other admin. Two admins → one demotes the other → single point of failure.
- **Fix:** Check admin count before demotion. If `User::where('is_admin', true)->count() <= 2`, prevent.
- **Effort:** 1h

### P2_POLISH

#### ADM-PM-005: No admin-initiated password reset
- Missing "Send Password Reset" on User Show page. Effort: 2h

#### ADM-OPS-003: No audit log for admin data views/exports
- Viewing user details and exporting audit logs not themselves logged. Effort: 2h

#### ADM-AI-002: No data integrity tools
- No orphan detection or consistency checks. Effort: 8h

#### ADM-AI-003: No admin alerting for critical events
- Health check failures, webhook spike, failed job accumulation — no proactive alerts. Effort: 12h

#### ADM-QA-001: toggleAdmin route missing withTrashed — UI shows action for deleted users
- Route 404s on soft-deleted users but dropdown still shows Make Admin. Fix: hide in UI. Effort: 1h

#### ADM-QA-002: AuditLog::user() missing withTrashed — deleted users show as System
- Misleading audit trail. Fix: `->withTrashed()` on relationship. Effort: 0.5h

#### ADM-QA-003: bulkDeactivate uses inline validation
- Breaks Form Request convention. Fix: Create `AdminBulkDeactivateRequest`. Effort: 1h

#### ADM-QA-004: Cross-page selection persists invisibly
- Selected IDs from previous pages remain counted but invisible. Effort: 1h

#### ADM-QA-005: No frontend self-action prevention on User Show page
- Buttons shown even for current user. Backend prevents but UX is poor. Effort: 1h

#### ADM-QA-007: Feature Flags fetch() calls don't check response.ok
- HTTP error responses could be silently processed as data. Effort: 0.5h

#### ADM-UX-004: Audit Logs mixed local/URL filter state
- Date/userId filters only sync to URL on Apply click. Effort: 2h

#### ADM-UX-005: Feature Flags page lacks navigation loading overlay
- No `useNavigationState` usage during mutations. Effort: 0.5h

#### ADM-UX-006: Focus lost after bulk deactivation
- Action bar removed from DOM, focus target gone. Effort: 0.5h

#### ADM-DES-001: Shared chart components underutilized
- `AdminCharts.tsx` exports `AdminAreaChart`, `AdminBarChart`, `AdminPieChart` but only Dashboard uses them. 6+ pages build charts inline with inconsistent heights, colors, animations. Effort: 4h

## VERIFIED_GOOD

- [x] Admin routes protected by auth + verified + admin middleware with rate limiting (60/min base, 10/min mutations, 5/min impersonation)
- [x] Comprehensive self-action prevention at backend level (cannot toggle own admin, deactivate self, impersonate self)
- [x] Impersonation safety: cannot impersonate admins, deactivated users, or nest impersonation; encrypted admin ID in session
- [x] All SQL inputs properly parameterized — no injection risk
- [x] No dangerouslySetInnerHTML in admin pages — React JSX escaping handles all output
- [x] CSRF protection via Inertia router for all mutations
- [x] Flash message sanitization with e() helper
- [x] Empty state handling across all tables and charts with contextual messages
- [x] Pagination with proper URL persistence and page reset on filter change
- [x] Debounced search (300ms) via useAdminFilters hook
- [x] Confirmation dialogs on all destructive actions with loading states
- [x] Bulk deactivation excludes self, admins, already-deactivated users in DB transaction with max:100 limit
- [x] Feature flag route parameter constrained with regex `[a-z_]+`
- [x] Sort columns validated against allowlists
- [x] Audit logging on all admin mutations with IP and user agent
- [x] CSV export has formula injection protection
- [x] Admin sidebar navigation properly feature-gated
- [x] Billing admin handles soft-deleted users with withTrashed and null-safe operators
- [x] >90% structural uniformity using shared AdminLayout, PageHeader, AdminDataTable, AdminStatsGrid
- [x] Consistent icon library (Lucide React) and sizing
- [x] No placeholder content across any admin page
- [x] WCAG 2.1 Level AA: ARIA labels, sr-only legends, keyboard-navigable tables, aria-live regions

## IMPLEMENTATION_ORDER

Execute fixes in this order:

1. **ADM-UX-001** — Wrap AdminLayout in ErrorBoundary (0.5h, highest impact per effort)
2. **ADM-QA-002** — AuditLog::user() withTrashed (0.5h, data correctness)
3. **ADM-QA-006** — Minimum admin count check (1h, prevents lockout)
4. **ADM-QA-001** — Hide toggle-admin for deleted users (1h, prevents 404)
5. **ADM-QA-005** — Self-action prevention on Show page (1h, UX improvement)
6. **ADM-UX-003** — Clear Filters on Subscriptions (0.5h, quick UX fix)
7. **ADM-UX-002** — Keyboard shortcuts on all list pages (1h, consistency)
8. **ADM-OPS-001** — Before/after audit capture (4h, compliance)
9. **ADM-PM-001** — User CSV export (3h, admin workflow)
10. **ADM-PM-004** — Subscription CSV export (3h, admin workflow)
11. **ADM-PM-003 / ADM-OPS-002** — Failed job management (8h, operational tooling)
12. **ADM-DES-001** — Migrate to shared chart components (4h, consistency)
13. **ADM-PM-002 / ADM-AI-001** — Feature section management actions (16h, largest scope)

## NEXT_STEPS

Admin audit complete. Implementation prompts are in `v-admin-prompts/`:

1. Review the session map in `v-admin-prompts/00-README.md`
2. Open parallel Claude sessions
3. Copy-paste each prompt file into its own session
4. Sessions marked "Can Parallel? Yes" can run simultaneously
5. After all sessions merge, run the quality gate commands from CLAUDE.md

Estimated effort:
- P1 fixes: 10 items, ~38h total
- P2 fixes: 14 items, ~34h total
