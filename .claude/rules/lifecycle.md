---
description: Growth and lifecycle domain — lifecycle stages, welcome sequence, audit logging
globs:
  - app/Services/Lifecycle*
  - app/Services/AuditService*
  - app/Console/Commands/SendWelcomeSequence*
  - app/Console/Commands/CheckExpiredTrials*
  - app/Notifications/*Notification*
  - app/Enums/LifecycleStage*
  - app/Enums/AnalyticsEvent*
  - app/Http/Middleware/TrackLastActivity*
---

# Growth & Lifecycle Domain

**Lifecycle stages** (tracked via `LifecycleStage` enum + `UserStageHistory`): visitor -> trial -> activated -> paying -> expansion, with at_risk and churned as off-funnel states. Stage transitions are audited via `AuditService`. See `app/Enums/LifecycleStage.php` for canonical values.

**Key patterns:**
- `EmailSendLog` prevents duplicate lifecycle emails — always check before sending
- The welcome sequence (`emails:send-welcome-sequence`) is the only shipped lifecycle command; it is scheduled via `routes/console.php`. Stage-specific emails (dunning, win-back, trial-ending, re-engagement, onboarding reminders, trial nudges) were intentionally removed — reintroduce purpose-built commands if you need deeper lifecycle coverage.
- `AuditService::log()` is the single dispatch layer; events are persisted to `audit_logs` via the `PersistAuditLog` job. There is no GA4 forwarding, no separate analytics gateway, no engagement / lead / customer-health scoring pipeline, and no UTM capture middleware — all of those surfaces were removed in favor of a lean audit-only trail.
- NPS surveys and feedback are collected via dedicated models with admin dashboard integration.

**Gotcha:** Lifecycle email commands must be idempotent — `EmailSendLog` deduplication prevents double-sends but commands should still be safe to re-run.
