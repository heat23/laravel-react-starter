# Lifecycle Email Sequences

Only the welcome sequence is shipped. Prior iterations of this starter had onboarding-reminder, dunning, win-back, trial-nudge, re-engagement, and trial-ending sequences; all were intentionally removed (see `.claude/rules/lifecycle.md`) because maintaining seven schedulers and their deduplication logic wasn't a fit for a solo-operator template. Reintroduce purpose-built commands if you need deeper coverage.

---

## Welcome Sequence (3 emails)

**Gate:** Always active (no feature flag — every app needs a welcome email)
**Trigger:** `Registered` event via `SendWelcomeNotification` listener → dispatches `WelcomeSequenceNotification(1)` immediately.
**Follow-ups:** Emails 2 and 3 are sent by the `emails:send-welcome-sequence` scheduled command (daily at 09:00).
**Suppression:** `SendWelcomeSequence` skips email 1 if already sent by the event listener (checks `email_number=1` in notifications table). `EmailSendLog` prevents duplicates for emails 2 and 3.

### Email 1: Welcome (immediate)
- **Class:** `WelcomeSequenceNotification` with `emailNumber=1`
- **Subject:** "Welcome to {{ app.name }} — Let's Get You Started"
- **Goal:** Orient the user with specific, time-estimated activation steps
- **CTA:** "Go to Your Dashboard" → `route('dashboard')`
- **Channels:** database + mail (if verified)

### Email 2: Getting the Most Out of It (Day 1–2)
- **Class:** `WelcomeSequenceNotification` with `emailNumber=2`
- **Timing window:** `config('email-sequences.welcome_sequence.2')` — `days=1, max_days=2`
- **Goal:** Surface a key feature or tip
- **Channels:** mail only (drip emails do not create database notifications)

### Email 3: Check-in (Day 3–5)
- **Class:** `WelcomeSequenceNotification` with `emailNumber=3`
- **Timing window:** `config('email-sequences.welcome_sequence.3')` — `days=3, max_days=5`
- **Goal:** Soft re-engagement; surface docs/support
- **Channels:** mail only

---

## Implementation Notes

### Notification Classes
- `WelcomeSequenceNotification` — accepts `int $emailNumber` (1, 2, 3). Email 1 is triggered immediately by `Registered` event via `SendWelcomeNotification` listener. Emails 2 and 3 are sent by the `emails:send-welcome-sequence` scheduled command.

### Artisan Commands
- `emails:send-welcome-sequence` — finds users whose registration date falls in each follow-up window and sends the appropriate email. Scheduled daily in `routes/console.php`.

### Tracking "Already Sent"
`EmailSendLog` is the canonical deduplication table. Before sending, commands check whether the `(user_id, email_kind, email_number)` tuple exists, and insert on send. Welcome email 1 is additionally protected by a notifications-table lookup because the `Registered` listener can fire before the scheduler.

### Customization Points
- Welcome emails: product description, quick-start steps, feature highlights — all editable in `app/Notifications/WelcomeSequenceNotification.php`.
- Timing windows: edit `config/email-sequences.php`.

Copy is intentionally generic but functional — replace placeholders with product-specific content.
