---
description: Growth and lifecycle domain — scoring, lifecycle emails, analytics, UTM
globs:
  - app/Services/*Scoring*
  - app/Services/Lifecycle*
  - app/Services/CustomerHealth*
  - app/Services/Analytics*
  - app/Services/ProductAnalytics*
  - app/Console/Commands/*
  - app/Notifications/*Notification*
  - app/Jobs/DispatchAnalyticsEvent*
  - app/Enums/LifecycleStage*
  - app/Enums/AnalyticsEvent*
  - app/Http/Middleware/CaptureUtmParameters*
  - app/Http/Middleware/TrackLastActivity*
---

# Growth & Lifecycle Domain

**Lifecycle stages** (tracked via `LifecycleStage` enum + `UserStageHistory`): visitor -> trial -> active -> at-risk -> churned. Stage transitions are audited.

**Key patterns:**
- `EmailSendLog` prevents duplicate lifecycle emails — always check before sending
- Lifecycle commands (`lifecycle:send-*`) are designed for scheduled execution via `routes/console.php`
- Scoring services (`EngagementScoringService`, `LeadScoringService`, `CustomerHealthService`) compute scores from user activity data — recompute via `scores:compute` command
- `AnalyticsGateway` + `AnalyticsEvent` enum provide a unified analytics dispatch layer — events fired via `DispatchAnalyticsEvent` job
- UTM attribution captured at middleware level (`CaptureUtmParameters`) for funnel analysis
- NPS surveys and feedback collected via dedicated models with admin dashboard integration

**Gotcha:** Lifecycle email commands must be idempotent — `EmailSendLog` deduplication prevents double-sends but commands should still be safe to re-run.

**Related config:** `config/analytics-thresholds.php` (engagement/health scoring thresholds)
