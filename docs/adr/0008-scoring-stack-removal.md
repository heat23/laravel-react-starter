# ADR 0008: Scoring Stack Removal

**Date:** 2026-04-22
**Status:** Active
**Deciders:** Sole operator review

## Context

The starter included five scoring/analytics services:

| Service | LOC | Consumers |
|---------|-----|-----------|
| `EngagementScoringService` | ~196 | `AdminUsersController` only |
| `LeadScoringService` | ~120 | `users:qualify-leads` command, `CustomerHealthService` |
| `CustomerHealthService` | ~384 | End-user `DashboardController`, `AdminDashboardController`, `LeadScoringService`, `AdminHealthAlertCommand` |
| `CohortService` | ~90 | Admin screens |
| `ProductAnalyticsService` | ~110 | `AdminProductAnalyticsController` only |

Together with their Artisan commands (`scores:compute`, `leads:qualify`, `lifecycle:send-*` for segment-based sends), React admin pages, and score columns on the `users` table, these services formed a marketing-analytics platform inside a Laravel app.

`CustomerHealthService` was the most deeply embedded: the end-user dashboard injected it to compute a health score displayed to users, making it a visible product surface rather than a pure internal tool.

## Decision

**Remove all five scoring services and their associated commands, admin pages, and score columns.**

### Removed

- `EngagementScoringService`, `LeadScoringService`, `CustomerHealthService`, `CohortService`, `ProductAnalyticsService`.
- `SessionDataMigrationService` (session-data bridging utility used by scoring).
- Artisan commands: `scores:compute`, `leads:qualify`, and all scheduled `lifecycle:send-*` commands except `lifecycle:send-welcome`.
- `AdminProductAnalyticsController` + route + React page + test.
- Score columns on `users` table: `engagement_score`, `lead_score` and related columns (dropped via migration in a second deploy after code removal).
- Scheduled console entries for deleted commands.
- All tests under `tests/Unit/Services/*Scoring*`, `tests/Feature/Commands/Send*`, `tests/Feature/Console/*`, `tests/Feature/Admin/AdminProductAnalyticsTest.php`, and related notification tests.

### Kept

- `LifecycleService` + `LifecycleStage` + `UserStageHistory` — lightweight state machine, not scoring (see ADR 0006).
- `CustomerHealthService` was replaced in `DashboardController` with a simpler inline check (subscription status + recent activity) that doesn't require a dedicated service.
- `AdminDashboardController` had its health-funnel block removed; user/subscription/audit stats remain.

## Rationale

The scoring stack is a marketing-analytics platform grafted onto a Laravel app. For a sole operator:
- Engagement scores are only actionable once you have hundreds of users to segment.
- Lead qualification requires sales capacity to act on the output.
- Customer health scores displayed to end users before PMF create confusion rather than value.
- Each service is ~200–400 LOC that requires understanding before touching anything near user data.

A `users.last_seen_at` column and a PostHog/Mixpanel snippet does 95% of this at 1% of the maintenance cost.

## Consequences

### Positive
- Starter loses ~1,100 LOC of service code, ~10 Artisan commands, one admin page, and ~25 test files.
- `DashboardController` is simpler — no injected health service.
- `AdminDashboardController` is simpler — no funnel chart or health segmentation.
- No score columns to maintain or migrate.

### Negative
- Projects at scale (>100 paying users) that want engagement segmentation must build or re-add scoring services per-project.
- The admin users index no longer shows engagement score — this column was useful for triage.
- Health alerts (`admin:health-alert`) no longer use health score thresholds; they rely on direct subscription and queue checks instead.

## Re-enabling guidance

When a project reaches sufficient user volume (suggested trigger: >50 paying users with churn to analyze):

1. Add `engagement_score` column via a nullable migration.
2. Create a purpose-built `EngagementService` scoped to that project's specific signals.
3. Connect score output to a dashboard widget or an admin filter — not to a visible end-user metric.
4. Wire `scores:compute` as a scheduled command once data is reliable enough to act on.

## Testing Requirements

- `DashboardController` test must verify the billing summary renders correctly without `CustomerHealthService`.
- `AdminDashboardController` test must verify user and subscription stats render without the health block.
- No test should reference deleted services.
