# ADR 0006: Lifecycle Simplification

**Date:** 2026-04-22
**Status:** Active
**Deciders:** Sole operator review

## Context

The starter inherited a full lifecycle state machine (`LifecycleService`, `LifecycleStage` enum, `UserStageHistory`) alongside a marketing-analytics scoring platform (`EngagementScoringService`, `LeadScoringService`, `CustomerHealthService`, `CohortService`, `ProductAnalyticsService`, `AnalyticsGateway`).

The analytics platform was braided into auth, onboarding, Stripe webhooks, the end-user dashboard, and admin screens. It represented a marketing-analytics platform grafted onto a Laravel app, requiring understanding ~10 distinct services before writing any product code.

For a sole-operator SaaS at 0–1,000 users, this level of instrumentation adds maintenance cost well before there are enough users to derive signal from it.

## Decision

**Keep the lifecycle state machine. Remove the marketing-analytics scoring platform.**

### Keep

- `LifecycleService` — state transitions (visitor → trial → active → at-risk → churned) wired to registration, onboarding, and Stripe payment events.
- `LifecycleStage` enum — canonical state values used in the `users` table and admin dashboard.
- `UserStageHistory` model — lightweight audit of stage transitions.
- `EmailSendLog` model — shared deduplication layer for lifecycle and billing dunning emails.
- `AuditService` + `AuditLog` — the single event-logging layer; all audit events route through here.
- NPS and Feedback models — user-initiated; useful regardless of scoring stack.

### Remove

See ADR 0007 (UTM/analytics removal) and ADR 0008 (scoring removal) for the specific deletions.

### Remaining lifecycle commands

Only the welcome sequence (`lifecycle:send-welcome`) is shipped and scheduled. Stage-specific lifecycle email commands (dunning, re-engagement, win-back, trial nudges, trial ending, onboarding reminders) were removed. Reintroduce purpose-built commands when there are enough users to make them worth sending.

## Consequences

### Positive
- Starter loses ~1,500 LOC and 20+ test files.
- Audit logging, billing, onboarding, and dashboard still work.
- The state machine survives for projects that need it.
- No marketing-analytics platform to maintain before PMF.

### Negative
- Lifecycle segmentation (engagement scores, lead scores, cohorts) must be rebuilt per-project when needed.
- First project to enable analytics will need to wire up a simple `last_seen_at` column and an external tool (PostHog, Mixpanel).

## Testing Requirements

- `LifecycleService` transitions must be tested for all stage paths (registration, onboarding completion, payment success, payment failure).
- `EmailSendLog` dedup must be tested for both lifecycle and billing dunning paths.
- Contract tests for `AuditService::log()` must not be weakened.
