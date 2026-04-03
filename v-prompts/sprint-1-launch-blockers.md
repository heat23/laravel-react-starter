# Sprint 1 — Launch Blockers
# Run each section as a separate /v prompt, or pass this file to /v for sequential execution.

---

## Fix SEC-001 + OPSRISK-001: Redis requirement for billing + queue

**Finding:** BillingService uses Cache::lock() but .env.example defaults CACHE_STORE=database and QUEUE_CONNECTION=database. The database driver does not support atomic locks. Both must be Redis for production billing safety.

**Files to change:**
- `.env.example` — change defaults: CACHE_STORE=redis, QUEUE_CONNECTION=redis
- `app/Providers/AppServiceProvider.php` — add boot-time assertions

**Exact changes:**

In `.env.example`, find the cache/queue section and change defaults to redis, keeping database as a clearly-labeled development fallback comment.

In `AppServiceProvider::boot()`, add:
```php
if (config('features.billing.enabled')) {
    if (config('cache.default') !== 'redis') {
        Log::warning('PRODUCTION WARNING: BillingService requires Redis cache driver for atomic locks. Current driver: ' . config('cache.default'));
    }
    if (config('queue.default') !== 'redis') {
        Log::warning('PRODUCTION WARNING: Redis queue driver recommended for billing workloads. Current driver: ' . config('queue.default'));
    }
}
```

**Acceptance criteria:**
- `.env.example` shows CACHE_STORE=redis and QUEUE_CONNECTION=redis as defaults
- `AppServiceProvider::boot()` logs warnings in non-local environments when drivers are not redis and billing is enabled
- Existing tests still pass (they use database driver in phpunit.xml — the check should only warn in non-test environments or when billing is enabled)

---

## Fix LEGAL-001: Gate legal disclaimer behind development environment only

**Finding:** `resources/js/Components/legal/LegalContent.tsx` renders a red "Template Content — Do Not Use As-Is" Alert unconditionally. This is visible in production.

**File to change:** `resources/js/Components/legal/LegalContent.tsx`

**Exact change:** Wrap the disclaimer Alert in an environment check:
```tsx
{import.meta.env.DEV && (
  <Alert variant="destructive" className="mb-6">
    <AlertTitle>Template Content — Do Not Use As-Is</AlertTitle>
    <AlertDescription>...</AlertDescription>
  </Alert>
)}
```

Also update `scripts/init.sh` to include legal content replacement in the setup checklist output.

**Acceptance criteria:**
- `npm run build` produces an output where the disclaimer Alert is not present
- `npm run dev` still shows the disclaimer

---

## Fix LAUNCH-001: Install and configure spatie/laravel-backup

**Finding:** No database backup strategy. No package, no command, no scheduled job.

**Changes:**
1. `composer require spatie/laravel-backup`
2. Publish config: `php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"`
3. Configure `config/backup.php`:
   - Set `backup.name` to app name
   - Set `backup.source.databases` to the MySQL connection
   - Set `backup.destination.disks` to ['s3'] (add S3 disk to config/filesystems.php)
   - Set `backup.cleanup.defaultStrategy.keepAllBackupsForDays` to 7
   - Set `backup.cleanup.defaultStrategy.keepWeeklyBackupsForWeeks` to 4
4. Add to `routes/console.php` or `bootstrap/app.php`:
   ```php
   Schedule::command('backup:run --only-db')->daily()->at('02:00');
   Schedule::command('backup:clean')->daily()->at('02:30');
   ```
5. Add to `.env.example`:
   ```
   # Backups (required for production)
   BACKUP_DISK=s3
   AWS_ACCESS_KEY_ID=
   AWS_SECRET_ACCESS_KEY=
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=your-backup-bucket
   ```

**Acceptance criteria:**
- `php artisan backup:run --only-db` completes successfully in local environment (with local disk fallback)
- `php artisan schedule:list` shows backup:run and backup:clean

---

## Fix FUNNEL-001: Fix CheckExpiredTrials toISOString() crash

**Finding:** `app/Console/Commands/CheckExpiredTrials.php` calls `->toISOString()` on a value PHPStan confirms is a string. This crashes the trial expiration flow.

**Fix option A (preferred):** Add `trial_ends_at` to User model casts:
```php
// app/Models/User.php
protected function casts(): array
{
    return [
        ...
        'trial_ends_at' => 'datetime',
        ...
    ];
}
```

**Fix option B:** Wrap the specific call in `Carbon::parse()`:
```php
Carbon::parse($user->trial_ends_at)->toISOString()
```

Also fix `SendTrialNudges.php` where `string|null` is passed where `Carbon|null` is required — use the same `Carbon::parse()` pattern.

After fixing, remove both entries from `phpstan-baseline.neon`.

**Acceptance criteria:**
- `vendor/bin/phpstan analyse app/Console/Commands/CheckExpiredTrials.php` reports zero errors
- `vendor/bin/phpstan analyse app/Console/Commands/SendTrialNudges.php` reports zero errors
- `php artisan test --filter CheckExpiredTrials` passes

---

## Fix COPY-001: Dunning email plan name resolver uses wrong config key

**Finding:** `SendDunningReminders::resolvePlanName()` calls `config('plans.tiers', [])` but plans.php has no 'tiers' key. All dunning emails show "your plan" instead of the plan name.

**File:** `app/Console/Commands/SendDunningReminders.php:107`

**Fix:** Replace `resolvePlanName()` implementation:
```php
private function resolvePlanName(string $stripePriceId): string
{
    $tier = app(BillingService::class)->resolveTierFromPrice($stripePriceId);
    if ($tier) {
        return config("plans.{$tier}.name", ucfirst($tier));
    }
    return 'your plan';
}
```

Ensure `BillingService` is imported at the top of the file.

**Acceptance criteria:**
- `php artisan test --filter SendDunningReminders` passes
- Dunning notification test asserts plan name is correct (not "your plan") for a known price ID
