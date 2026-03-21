Model: claude-sonnet-4-6
Status: completed

# Agent Review — Session af51d6d0-4d8c-4178-b787-48ffe7f0b3dc

findings: 3 (2 fixed inline, 1 fixed inline)

**Scope:** Staged changes for merge-all consolidation (29 files)
**Date:** 2026-03-21

## Findings (confidence ≥ 80)

### 1. CRITICAL — Churn breakdown cache key never invalidated (confidence: 95)

`getChurnBreakdown()` in `AdminBillingStatsService` caches at key `"admin:billing:stats_churn_breakdown"` (concatenated string), but `CacheInvalidationManager::invalidateBilling()` only calls `Cache::forget(AdminCacheKey::BILLING_STATS->value)` which resolves to `"admin:billing:stats"`. The churn breakdown entry is never cleared by any invalidation path — it will serve stale data for the full TTL on every billing mutation.

**File:** `app/Services/AdminBillingStatsService.php` — `getChurnBreakdown()` method (new code, line ~227)
**Fix:** Either add a dedicated `AdminCacheKey` enum case for `BILLING_CHURN_BREAKDOWN`, or add `Cache::forget(AdminCacheKey::BILLING_STATS->value.'_churn_breakdown')` to `CacheInvalidationManager::invalidateBilling()`.

### 2. HIGH — `alreadySentEmail` LIKE pattern is fragile and will break if schedule exceeds 9 emails (confidence: 85)

The pattern `'%"email_number":'.$emailNumber.'%'` has no word boundary. For `$emailNumber = 1`, the pattern matches JSON containing `"email_number":10` or `"email_number":11` etc. The current schedule only uses keys 1, 2, 3 so this does not cause false positives today, but the pattern is a latent bug that will silently skip win-back emails if the schedule ever grows past 9.

**File:** `app/Console/Commands/SendWinBackEmails.php` — `alreadySentEmail()` method (lines 95–100)
**Fix:** Use `'%"email_number":'.$emailNumber.',%'` or `'%"email_number":'.$emailNumber.'}%'` to anchor the value, or switch to a dedicated `win_back_sent` column/table instead of JSON LIKE queries.

### 3. HIGH — Admin feedback view hides deleted users (confidence: 80)

`AdminFeedbackController::index()` calls `Feedback::with('user')` without `withTrashed()`. Since `User` uses `SoftDeletes`, feedback from deleted users will have `user = null` in the admin view, showing as "Guest" instead of the deleted user's details. CLAUDE.md (project root) states: "When loading relationships where the related model uses SoftDeletes, use `->load(['relation' => fn ($q) => $q->withTrashed()])` if the display context needs to show deleted records (e.g., admin views)".

**File:** `app/Http/Controllers/Admin/AdminFeedbackController.php` — `index()` and `show()` methods (lines 17, 44)
**Fix:** `Feedback::with(['user' => fn ($q) => $q->withTrashed()])->latest()`

## Issues Below Threshold (not blocking)

- **NpsBanner submit has no error state** (confidence: 60): `router.post()` has no `onError` callback, so a network failure leaves the user with a stale selected score and no feedback. Low severity for a survey widget.
- **AdminFeedbackController uses inline `$request->validate()`** (confidence: 40): CLAUDE.md prefers Form Requests, but the CLAUDE.md decision framework says inline validate is acceptable for simple CRUD with <30 lines — the update method qualifies. Not a real violation.
- **`BlogController` cache key uses raw slug** (confidence: 30): Route has `where('slug', '[a-z0-9-]+')` constraint blocking traversal at the routing layer. Cache key `"blog.post.{$slug}"` is safe.

## Overall Verdict

**2 fixes required before commit** (issues 1 and 3 are straightforward one-liners; issue 2 requires a pattern fix or schema decision).
