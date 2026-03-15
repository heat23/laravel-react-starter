/v Fix the following audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.

## Findings to Fix

### Fix 1: ACT-01 — Onboarding defaults OFF (P1, 0.5h est.)
**Problem:** config/features.php has onboarding.enabled defaulting to false. New users land on empty dashboard with no guidance.
**Files:** config/features.php (line 106-108)
**Test first:** tests/Feature/Middleware/EnsureOnboardingCompletedTest.php — verify existing tests pass with onboarding enabled.
**Implementation:** Change default: `'enabled' => env('FEATURE_ONBOARDING', true)`.
**Verify:** `php artisan test --filter=EnsureOnboardingCompleted`

### Fix 2: ACT-02 — No defined activation milestone (P1, 8h est.)
**Problem:** Onboarding step 3 shows static feature cards with no actionable CTA. No activation milestone defined. No first-success celebration.
**Files:** resources/js/Pages/Onboarding.tsx (lines 162-193), resources/js/Pages/Dashboard.tsx
**Test first:** resources/js/Components/onboarding/ActivationChecklist.test.tsx — test that checklist renders with correct items, marks completed items, shows celebration on all complete.
**Implementation:**
1. Create resources/js/Components/onboarding/ActivationChecklist.tsx — a checklist component showing: Profile completed, Settings configured, First API token created (if feature enabled), Explored dashboard.
2. In Onboarding step 3, replace static feature cards with actionable CTAs: "Create your first API token", "Customize your settings", "Explore the dashboard".
3. On Dashboard, show ActivationChecklist for users who completed onboarding <7 days ago but haven't hit all milestones.
4. Track completion via user_settings (key: 'activation_checklist_completed').
**Verify:** `npm test -- --run --filter=ActivationChecklist`

### Fix 3: ACT-03 — Dashboard shows hardcoded placeholder data (P2, 4h est.)
**Problem:** Dashboard.tsx has hardcoded zero-value stats array with comment "// Placeholder stats - replace with real data". No server data.
**Files:** resources/js/Pages/Dashboard.tsx (lines 10-40), app/Http/Controllers/DashboardController.php
**Test first:** tests/Feature/DashboardTest.php — test that dashboard returns Inertia props with user-specific stats.
**Implementation:**
1. In DashboardController, compute real stats: account age, settings count, token count (if feature enabled), last login from audit logs.
2. Pass as Inertia props.
3. In Dashboard.tsx, replace hardcoded stats with props. For new users with no data, show the ActivationChecklist from Fix 2 instead of zero stats.
**Verify:** `php artisan test --filter=DashboardTest`

### Fix 4: ACT-04 — Empty states lack actionable CTAs (P2, 2h est.)
**Problem:** Dashboard EmptyState components show informational text but no action buttons.
**Files:** resources/js/Pages/Dashboard.tsx (lines 80-84)
**Implementation:** Add action props to EmptyState usage: "Create your first project" → links to relevant page, "Generate API token" → links to settings/tokens.
**Verify:** Visual check + `npm test -- --run`

### Fix 5: ACT-05 — No persistent trial countdown (P2, 3h est.)
**Problem:** Trial info only visible on Billing page. No urgency during normal product usage.
**Files:** resources/js/Layouts/ (dashboard layout), app/Http/Middleware/HandleInertiaRequests.php
**Test first:** Create resources/js/Components/billing/TrialBanner.test.tsx — test banner renders with days remaining, shows upgrade CTA, hides when not on trial.
**Implementation:**
1. In HandleInertiaRequests, add `trial_ends_at` and `is_on_trial` to shared auth props when billing is enabled.
2. Create TrialBanner component that shows "X days left in your trial" with upgrade button.
3. Add TrialBanner to the authenticated layout, conditionally rendered when is_on_trial is true.
**Verify:** `npm test -- --run --filter=TrialBanner`

### Fix 6: BILL-01 — Pro tier defaults to coming_soon (P2, 2h est.)
**Problem:** PRO_TIER_COMING_SOON defaults to true, blocking the primary conversion path. Deployers may forget to flip it.
**Files:** config/features.php (line 22), app/Services/HealthCheckService.php
**Implementation:** Add a health check warning: if billing is enabled but coming_soon is true, warn "Billing is enabled but Pro tier is set to 'Coming Soon'. Set PRO_TIER_COMING_SOON=false to enable purchases."
**Verify:** `php artisan test --filter=HealthCheckServiceTest`

### Fix 7: BILL-PLG-01 — No contextual upgrade prompts at limits (P1, 6h est.)
**Problem:** PlanLimitService enforces limits but no UI shows upgrade prompts when users approach/hit limits.
**Files:** New: resources/js/Components/billing/UpgradePrompt.tsx
**Test first:** resources/js/Components/billing/UpgradePrompt.test.tsx — test renders with limit info, shows upgrade CTA, links to pricing.
**Implementation:**
1. Create UpgradePrompt component showing: "You've used X of Y [resource]. Upgrade to [tier] for [limit]." with a CTA button.
2. In PlanLimitService, add a `getUsagePercentage()` method.
3. Controllers that enforce limits should return limit info in Inertia props when user is at >80%.
4. Feature pages show UpgradePrompt inline when approaching limits.
**Verify:** `npm test -- --run --filter=UpgradePrompt`

## After All Fixes

```bash
php artisan test --parallel
npm test -- --run
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "feat(activation): onboarding defaults, activation checklist, real dashboard data, trial banner, upgrade prompts"`
