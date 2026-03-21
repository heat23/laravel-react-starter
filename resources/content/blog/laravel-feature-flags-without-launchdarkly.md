---
title: Laravel Feature Flags Without LaunchDarkly — The Env + Database Pattern
slug: laravel-feature-flags-without-launchdarkly
description: You don't need a $200/month feature flag service for most SaaS apps. Here's the two-layer pattern we use in production: env-based defaults with database overrides.
date: 2026-02-15
readingTime: 7 min read
tags: [Laravel, feature flags, SaaS]
---

## The Problem with Feature Flag Services

LaunchDarkly, Unleash, and Split.io are powerful tools. They're also expensive, add another external dependency, and require your team to learn another platform. For early-stage SaaS, they're overkill.

The good news: the 80% use case — enable/disable features per environment, override for specific users in production — is straightforward to implement in Laravel without any third-party service.

## The Two-Layer Architecture

```
Layer 1: Environment variable (config/features.php)
    ↓ can be overridden by
Layer 2: Database record (feature_flag_overrides table)
    ↓ resolved by
FeatureFlagService::isEnabled(string $flag, ?User $user): bool
```

**Layer 1: Environment variables** are the ground truth. They control defaults. Changing a feature flag for an entire deploy means changing a `.env` variable and redeploying.

**Layer 2: Database overrides** let you change a flag at runtime without a deploy. They can be global (apply to all users) or per-user (apply only to a specific user ID).

## config/features.php

```php
return [
    'billing' => [
        'enabled' => env('FEATURE_BILLING', false),
    ],
    'two_factor' => [
        'enabled' => env('FEATURE_TWO_FACTOR', false),
    ],
    'webhooks' => [
        'enabled' => env('FEATURE_WEBHOOKS', false),
    ],
    'api_tokens' => [
        'enabled' => env('FEATURE_API_TOKENS', true),
    ],
    // ... 11 flags total
];
```

The convention: dangerous/optional features default to `false`. Core features default to `true`.

## The FeatureFlagService

```php
class FeatureFlagService
{
    public function isEnabled(string $flag, ?User $user = null): bool
    {
        // Check per-user override first
        if ($user) {
            $userOverride = FeatureFlagOverride::query()
                ->where('flag', $flag)
                ->where('user_id', $user->id)
                ->first();

            if ($userOverride) {
                return $userOverride->enabled;
            }
        }

        // Check global override
        $globalOverride = Cache::remember(
            "feature_flag.{$flag}",
            300, // 5 minutes
            fn () => FeatureFlagOverride::query()
                ->where('flag', $flag)
                ->whereNull('user_id')
                ->first()
        );

        if ($globalOverride) {
            return $globalOverride->enabled;
        }

        // Fall back to config
        return config("features.{$flag}.enabled", false);
    }
}
```

Notice the cache on global overrides. Per-user overrides aren't cached (users are too numerous), but global overrides only change when an admin changes them, so a 5-minute TTL is fine.

## The helper function

```php
// app/Helpers/features.php (autoloaded via composer.json)
function feature_enabled(string $flag, ?User $user = null): bool
{
    return app(FeatureFlagService::class)->isEnabled($flag, $user);
}
```

Usage in controllers:

```php
public function __construct()
{
    if (! feature_enabled('two_factor')) {
        abort(404);
    }
}
```

Usage in Blade/Inertia props:

```php
// In HandleInertiaRequests middleware:
'features' => [
    'billing' => feature_enabled('billing'),
    'two_factor' => feature_enabled('two_factor'),
    // ...
]
```

Usage in React:

```tsx
const { features } = usePage<PageProps>().props;

{features.two_factor && (
  <Link href="/settings/security">Security</Link>
)}
```

## The Admin UI

The admin panel has a feature flags page where admins can toggle any flag globally or for a specific user, with a required reason field:

```php
// POST /admin/feature-flags/override
public function store(AdminFeatureFlagRequest $request): RedirectResponse
{
    FeatureFlagOverride::updateOrCreate(
        ['flag' => $request->flag, 'user_id' => $request->user_id],
        ['enabled' => $request->enabled, 'reason' => $request->reason, 'changed_by' => auth()->id()]
    );

    Cache::forget("feature_flag.{$request->flag}");

    return back()->with('success', 'Override saved.');
}
```

The reason field and `changed_by` create an audit trail. When something breaks after a flag change, you can see exactly who changed what.

## What You Don't Get (and Why That's OK)

Compared to LaunchDarkly, this approach lacks:

- **Percentage rollouts** — you can't gradually roll out to 10% of users
- **A/B testing** — no built-in experimentation framework
- **Real-time flag streaming** — flag changes take up to 5 minutes to propagate (cache TTL)
- **Feature analytics** — no built-in tracking of who hit each code path

For most SaaS products in the first two years, these aren't blockers. You're not doing sophisticated experimentation yet. You're toggling billing on and off, enabling beta features for specific customers, and deploying with confidence.

When you outgrow this pattern, the migration to LaunchDarkly is straightforward: replace `FeatureFlagService::isEnabled()` with the LaunchDarkly SDK. The rest of your codebase doesn't change.

This pattern ships in Laravel React Starter with 11 built-in flags, the admin UI, per-user overrides, and Pest test coverage.
