/v Fix the following audit findings for Laravel React Starter.

Read the project's CLAUDE.md first for architecture context, conventions, and quality gate commands.
Tech stack: Laravel 12 + Inertia.js + React 18 + TypeScript + Tailwind CSS v4.

## Findings to Fix

### Fix 1: FDBK-02 — Dead /contact link returns 404 (P1, 4h est.)
**Problem:** Pricing page and Billing page both link to /contact for enterprise inquiries and help requests. No /contact route or page exists. Enterprise leads hitting 404 is a direct revenue leak.
**Files:** routes/web.php, New: app/Http/Controllers/ContactController.php, New: resources/js/Pages/Contact.tsx, New: app/Http/Requests/ContactRequest.php
**Test first:** tests/Feature/ContactTest.php — test: `it('renders contact page')`, `it('submits contact form and sends notification')`, `it('validates required fields')`, `it('rate limits submissions')`.
**Implementation:**
1. Create ContactController with show() and store() methods.
2. Create ContactRequest with validation: name (required), email (required, email), subject (required), message (required, max:2000).
3. Create Contact.tsx page with a form using useForm(). Include subject dropdown: General inquiry, Enterprise pricing, Bug report, Feature request.
4. store() sends a notification to the admin email (or creates an audit log entry if no admin email configured).
5. Add route with rate limiting: Route::get/post('/contact', ...)->middleware('throttle:5,60').
6. Update navigation to include Contact link.
**Verify:** `php artisan test --filter=ContactTest`

### Fix 2: FDBK-01 — No feedback collection mechanism (P1, 8h est.)
**Problem:** No feedback widget, survey, NPS, or feature request system anywhere in the product.
**Files:** New: resources/js/Components/feedback/FeedbackWidget.tsx
**Test first:** resources/js/Components/feedback/FeedbackWidget.test.tsx — test renders trigger button, opens form, submits feedback, shows success message.
**Implementation:**
1. Create FeedbackWidget.tsx — a floating button (bottom-right) that opens a small modal with: feedback type (bug, feature, general), message textarea, optional screenshot upload.
2. Create app/Http/Controllers/FeedbackController.php with store() method. Store in audit_logs with action 'feedback.submitted' and metadata containing type/message.
3. Add route: Route::post('/feedback', [FeedbackController::class, 'store'])->middleware(['auth', 'throttle:10,60']).
4. Add FeedbackWidget to the authenticated layout so it's available on every page.
5. Add feedback entries to admin audit log view (filterable by 'feedback.submitted' action).
**Verify:** `php artisan test --filter=Feedback && npm test -- --run --filter=FeedbackWidget`

### Fix 3: FDBK-04 — No public changelog (P2, 6h est.)
**Problem:** No changelog page or "What's New" indicator. Users don't learn about new features.
**Files:** New: resources/js/Pages/Changelog.tsx, routes/web.php
**Test first:** tests/Feature/ChangelogTest.php — test that /changelog returns 200.
**Implementation:**
1. Create a simple Changelog page that renders markdown content from a changelog.json or changelog entries stored in the DB.
2. For simplicity, start with a static JSON file at public/changelog.json with entries: [{version, date, title, description, type: 'feature'|'fix'|'improvement'}].
3. Create Changelog.tsx that reads from this JSON (passed as Inertia prop).
4. Add a "What's New" badge in the sidebar navigation that shows a dot indicator when new entries exist since user's last visit (track via user_settings key 'last_changelog_viewed').
5. Add route: Route::get('/changelog', ...).
**Verify:** `php artisan test --filter=ChangelogTest`

### Fix 4: FDBK-PLG-01 — No community channel (P2, 2h est.)
**Problem:** No community forum, Discord, or social link in the product.
**Files:** resources/js/config/navigation.ts, resources/js/Components/sidebar/
**Implementation:**
1. Add a community/support link section to the sidebar navigation with configurable URL (env var COMMUNITY_URL or config value).
2. Add footer links on the Welcome page for community channels.
3. Make the link configurable so template deployers can set their own Discord/Slack/forum URL.
**Verify:** Visual check + `npm run build`

### Fix 5: FDBK-PLG-02 — No public roadmap (P3, 6h est.)
**Problem:** No public-facing roadmap where users can see planned features.
**Files:** New: resources/js/Pages/Roadmap.tsx, routes/web.php
**Implementation:**
1. Create a simple Roadmap page with categories: Planned, In Progress, Completed.
2. Start with static JSON (public/roadmap.json) with entries: [{title, description, status, votes}].
3. Optionally integrate with Canny or similar tool via iframe/link.
4. Add route and navigation link.
**Verify:** `php artisan test --filter=Roadmap`

### Fix 6: OPS-003 — No retention pruning for webhook deliveries (P3, 2h est.)
**Problem:** webhook_deliveries table grows unbounded. Only stale detection exists, no deletion of old records.
**Files:** app/Console/Commands/ (extend webhooks:prune-stale or new command)
**Test first:** tests/Feature/Webhook/WebhookPruneTest.php — test that old deliveries beyond retention period are deleted.
**Implementation:**
1. Add --delete-older-than=N option to existing webhooks:prune-stale command (or create webhooks:prune-old).
2. Delete webhook_deliveries older than N days (default 90) that are in terminal state (delivered, failed, abandoned).
3. Schedule in routes/console.php: daily.
**Verify:** `php artisan test --filter=WebhookPrune`

## After All Fixes

```bash
php artisan test --parallel
npm test -- --run
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
npm run lint
npm run build
```

Commit with: `git add -u && git commit -m "feat(feedback): contact page, feedback widget, changelog, community links, webhook pruning"`
