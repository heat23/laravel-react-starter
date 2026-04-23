# ADR 0007: UTM Capture and Analytics Gateway Removal

**Date:** 2026-04-22
**Status:** Active
**Deciders:** Sole operator review

## Context

The starter included:
- `CaptureUtmParameters` middleware — captures UTM cookies on web requests; `RegisteredUserController` and `SocialAuthController` read and persist them to the user record at registration.
- `AnalyticsGateway` — a thin wrapper that fans out analytics events to `AuditService` and an optional external sink.
- `DispatchAnalyticsEvent` job — dispatched from `StripeWebhookController` (7 event types) and other controllers to route events through the gateway.
- `analytics-thresholds.php` config — threshold constants consumed by scoring services and billing controllers.

These surfaces formed a marketing-analytics attribution pipeline: capture UTM source at acquisition, track lifecycle events, route them to external analytics tools.

## Decision

**Remove the UTM capture middleware, analytics gateway, and DispatchAnalyticsEvent job.** Wire all event logging directly through `AuditService::log()`.

### Removed

- `CaptureUtmParameters` middleware and its registration in `bootstrap/app.php`.
- All UTM column reads in `RegisteredUserController` and `SocialAuthController`.
- `AnalyticsGateway` class.
- `DispatchAnalyticsEvent` job — replaced with direct `AuditService->log()` calls at each former dispatch site.
- `analytics-thresholds.php` config file.
- All `ANALYTICS_*` and `LIFECYCLE_*` cases from the `AnalyticsEvent` enum that were purely marketing-analytics (not audit-trail) events.

### Kept

- `AuditService::log()` — the single event-logging entry point.
- `AuditLog` model and `audit_logs` table — the persistent audit trail.
- Remaining `AuditEvent` (formerly `AnalyticsEvent`) cases representing meaningful security and billing audit points.

## Rationale

For a sole operator at 0–1,000 users:
- UTM attribution is only actionable at scale (hundreds of signups/month minimum).
- An analytics gateway is only useful if routing to multiple sinks; a single `AuditService` call achieves the same thing with fewer moving parts.
- A simpler audit trail that captures "what happened and when" is more valuable than an analytics pipeline with no downstream consumers.

External attribution (PostHog, Plausible, Mixpanel) via a frontend snippet handles UTM and funnel analysis without any backend code.

## Consequences

### Positive
- Removes one middleware from the request path.
- Removes one job from the queue for every analytics event (previously all Stripe webhook events dispatched a job through the gateway).
- Simplifies the `StripeWebhookController` — direct `AuditService` calls instead of indirect job dispatch.
- `analytics-thresholds.php` gone; the one config file that lived purely for scoring is removed.

### Negative
- UTM attribution data is no longer captured in the database. Projects that need campaign attribution must add it back explicitly or use an external tool.
- Projects that previously relied on `AnalyticsGateway` for routing to external sinks must wire that routing directly when re-enabling.

## Testing Requirements

- All former `DispatchAnalyticsEvent` dispatch sites must have feature tests verifying the corresponding `AuditLog` record is created directly.
- `CaptureUtmParametersTest.php` was deleted.
- No test should reference `AnalyticsGateway` or `DispatchAnalyticsEvent`.
