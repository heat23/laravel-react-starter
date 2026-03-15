# Lifecycle Email Sequences

3 sequences, 7 emails total. All notifications follow existing codebase conventions: queued, dual-channel (database + mail for verified users), feature-gated.

---

## Sequence 1: Welcome (1 email)

**Gate:** Always active (no feature flag — every app needs a welcome email)
**Trigger:** `Registered` event (same as email verification, but separate notification)
**Suppression:** None — every new user gets this

### Email 1: Welcome
- **Subject:** Welcome to {{ config('app.name') }}
- **Timing:** Immediate (on registration, via `Registered` event listener)
- **Goal:** Orient the user — where to go first
- **CTA:** "Go to Your Dashboard" → `route('dashboard')`
- **Body:**
  1. Greeting with user's name
  2. One sentence: what the product helps them do (customizable)
  3. Quick-start: 3 bullet points of first actions (customize these for your product)
  4. CTA button to dashboard
- **Channels:** database + mail (if verified)
- **Success metric:** Dashboard visit within 24h of signup

---

## Sequence 2: Onboarding (3 emails)

**Gate:** `features.onboarding.enabled`
**Trigger:** Scheduled command checks for users who signed up N days ago and haven't completed onboarding
**Suppression:** Skip if user has `onboarding_completed_at` set (or equivalent activation signal)

### Email 1: Getting Started (Day 1)
- **Subject:** 3 things to set up in your first 5 minutes
- **Timing:** 24 hours after registration
- **Goal:** Drive first key action
- **CTA:** "Complete Your Setup" → `route('onboarding')`
- **Body:**
  1. "You signed up yesterday — here's how to get the most out of {{ app.name }}"
  2. Three numbered steps (customize for your product): set up profile, configure settings, try the core feature
  3. CTA button
- **Channels:** mail only (no database — avoid notification clutter for drip emails)
- **Success metric:** Onboarding page visit

### Email 2: Key Feature Highlight (Day 3)
- **Subject:** Did you know you can [key feature]?
- **Timing:** 72 hours after registration
- **Suppression:** Skip if user completed onboarding
- **Goal:** Show value of a specific feature
- **CTA:** "Try It Now" → `route('dashboard')`
- **Body:**
  1. Highlight one specific feature that drives the "aha moment"
  2. Brief description of the benefit (1-2 sentences)
  3. CTA button
- **Channels:** mail only

### Email 3: Need Help? (Day 7)
- **Subject:** Quick question — is everything working?
- **Timing:** 7 days after registration
- **Suppression:** Skip if user has been active in last 3 days (has any audit_log entry)
- **Goal:** Catch at-risk users before they churn
- **CTA:** "Reply to this email" (support touch) or "Visit Dashboard" → `route('dashboard')`
- **Body:**
  1. "It's been a week since you joined — just checking in"
  2. Offer help: reply to this email, or link to docs/support
  3. Brief reminder of what they can do
- **Channels:** mail only

---

## Sequence 3: Dunning — Payment Recovery (3 emails)

**Gate:** `features.billing.enabled`
**Trigger:** Existing `PaymentFailedNotification` is email 0. This sequence adds escalating follow-ups.
**Relationship to existing:** `PaymentFailedNotification` fires immediately on webhook. These follow up if the payment method hasn't been updated.

### Email 1: Gentle Reminder (Day 3)
- **Subject:** Your payment method needs updating
- **Timing:** 3 days after initial payment failure
- **Suppression:** Skip if subscription is now `active` (payment method was updated)
- **Goal:** Recover payment before service interruption
- **CTA:** "Update Payment Method" → `route('billing.index')`
- **Body:**
  1. "We tried to charge your card 3 days ago and it didn't go through"
  2. Reassure: "Your account is still active — update your card to keep it that way"
  3. CTA button
- **Channels:** database + mail (if verified)

### Email 2: Urgency (Day 7)
- **Subject:** Action needed — your subscription will be paused
- **Timing:** 7 days after initial payment failure
- **Suppression:** Skip if subscription is now `active`
- **Goal:** Create urgency without being aggressive
- **CTA:** "Update Payment Method" → `route('billing.index')`
- **Body:**
  1. "It's been a week since your payment failed"
  2. Explain consequence: "If we can't process payment soon, your subscription will be paused and you'll lose access to [premium features]"
  3. CTA button
  4. "Having trouble? Reply to this email — we can help."
- **Channels:** database + mail (if verified)

### Email 3: Final Notice (Day 12)
- **Subject:** Final notice — subscription will cancel tomorrow
- **Timing:** 12 days after initial payment failure
- **Suppression:** Skip if subscription is now `active`
- **Goal:** Last chance recovery
- **CTA:** "Save My Subscription" → `route('billing.index')`
- **Body:**
  1. "This is our last reminder before your subscription is cancelled"
  2. What they'll lose (reference their plan name)
  3. CTA button
  4. "We'd hate to see you go. If there's an issue with billing, just reply."
- **Channels:** database + mail (if verified)

---

## Implementation Notes

### Notification Classes
- `WelcomeNotification` — triggered by `Registered` event listener
- `OnboardingReminderNotification` — accepts `int $emailNumber` (1, 2, 3) to control subject/body
- `DunningReminderNotification` — accepts `int $emailNumber` (1, 2, 3) and `string $planName`

### Artisan Commands
- `notifications:send-onboarding` — finds users who registered N days ago, haven't completed onboarding, and haven't received this email yet. Runs daily via scheduler.
- `notifications:send-dunning` — finds users with past_due subscriptions, checks days since failure, sends appropriate dunning email. Runs daily via scheduler.

### Tracking "Already Sent"
Use the existing `notifications` table — each notification has a unique `type` field. Before sending, check if user already has a notification of that type. This prevents duplicate sends without needing a new table.

### Customization Points
Developers using this starter should customize:
1. Welcome email: product description and quick-start steps
2. Onboarding emails: feature highlights specific to their product
3. Dunning emails: what "premium features" the user will lose

Copy is intentionally generic but functional — replace placeholders with product-specific content.
