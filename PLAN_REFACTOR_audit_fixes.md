# PLAN_REFACTOR: Audit Fixes Implementation
generated: 2026-02-04
status: ready
source: AUDIT_REPORT_2026-02-04_1030.md

## Summary

Implement all P0, P1, and P2 fixes from the security audit. Focus areas:
1. **Security (P0):** Rate limiting on auth endpoints, token deletion fix, atomic social disconnect
2. **Authorization (P0):** Add UserPolicy for explicit authorization
3. **Tests (P1):** Password reset, email verification, profile tests
4. **UX (P1/P2):** Error messages, loading states, empty states, form validation

## Current State

### Security Gaps
- Registration endpoint: No rate limiting
- Password reset request: No rate limiting
- Password reset store: No rate limiting
- Token deletion: Returns 200 even for non-existent tokens
- Social disconnect: Race condition between check and delete
- Profile controller: No explicit authorization (relies on middleware only)

### Test Coverage Gaps
- No password reset tests
- No email verification tests
- No profile management tests

### UX Issues
- Generic error messages without context
- Missing loading spinner on timezone save
- Dashboard empty states as plain text
- Profile form lacks client-side validation

## Proposed Changes

### Phase 1: Security Fixes (P0)

#### 1.1 Add Rate Limiting to Auth Routes

**File:** `routes/auth.php`

Add throttle middleware following existing pattern (`throttle:X,1`):

```php
// Registration - 5 attempts per minute
Route::post('register', [RegisteredUserController::class, 'store'])
    ->middleware('throttle:5,1');

// Password reset request - 3 attempts per minute (stricter for email enumeration)
Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('password.email');

// Password reset store - 5 attempts per minute
Route::post('reset-password', [NewPasswordController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('password.store');
```

#### 1.2 Fix Token Deletion Response

**File:** `routes/api.php:79-82`

Change from always returning success to proper 404:

```php
Route::delete('/{tokenId}', function (Request $request, $tokenId) {
    $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

    if (!$deleted) {
        return response()->json(['message' => 'Token not found.'], 404);
    }

    return response()->json(['success' => true]);
});
```

#### 1.3 Make Social Disconnect Atomic

**File:** `app/Http/Controllers/Auth/SocialAuthController.php:95-116`

Wrap check and delete in database transaction with row locking:

```php
public function disconnect(Request $request, string $provider): RedirectResponse
{
    if (! config('features.social_auth.enabled', false)) {
        abort(404, 'Social authentication is not enabled.');
    }

    $user = $request->user();

    try {
        DB::transaction(function () use ($user, $provider) {
            // Lock user row to prevent concurrent modifications
            $user->lockForUpdate();

            if (! $user->hasPassword()) {
                $socialAccountCount = $user->socialAccounts()->count();
                if ($socialAccountCount <= 1) {
                    throw new \Exception('Cannot disconnect last authentication method.');
                }
            }

            $user->socialAccounts()->where('provider', $provider)->delete();
        });

        return back()->with('status', ucfirst($provider).' account disconnected.');
    } catch (\Exception $e) {
        return back()->with('error', 'You must set a password before disconnecting your last social account.');
    }
}
```

#### 1.4 Add UserPolicy for Explicit Authorization

**Create:** `app/Policies/UserPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can update their profile.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine if the user can delete their account.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }
}
```

**Modify:** `app/Providers/AppServiceProvider.php` (or AuthServiceProvider if exists)

Register the policy in boot():
```php
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(User::class, UserPolicy::class);
}
```

**Modify:** `app/Http/Controllers/ProfileController.php`

Add authorization calls:
```php
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $this->authorize('update', $request->user());

    $request->user()->fill($request->validated());
    // ... rest unchanged
}

public function destroy(Request $request): RedirectResponse
{
    $this->authorize('delete', $request->user());

    $request->validate([
        'password' => ['required', 'current_password'],
    ]);
    // ... rest unchanged
}
```

---

### Phase 2: Test Coverage (P1)

#### 2.1 Password Reset Tests

**Create:** `tests/Feature/Auth/PasswordResetTest.php`

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);
            $response->assertStatus(200);
            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

            $response->assertSessionHasNoErrors();
            return true;
        });
    }
}
```

#### 2.2 Email Verification Tests

**Create:** `tests/Feature/Auth/EmailVerificationTest.php`

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Only run these tests if email verification is enabled
        if (!config('features.email_verification.enabled', true)) {
            $this->markTestSkipped('Email verification feature is disabled.');
        }
    }

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
```

#### 2.3 Profile Tests

**Create:** `tests/Feature/ProfileTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_email_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/profile')->delete('/profile', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password')->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
```

---

### Phase 3: UX Improvements (P1/P2)

#### 3.1 Improve Error Messages

**File:** `resources/js/Pages/Profile/Edit.tsx:36`

```tsx
catch (error) {
    const message = error instanceof Error
        ? error.message
        : "Please check your connection and try again";
    toast.error(`Failed to update timezone: ${message}`);
}
```

**File:** `resources/js/hooks/useTimezone.tsx:42-44`

```tsx
catch (err) {
    const message = err instanceof Error
        ? err.message
        : "Unable to save preference. Please try again.";
    setError(message);
    toast.error(`Timezone update failed: ${message}`);
}
```

#### 3.2 Add Loading State to Timezone Selector

**File:** `resources/js/Pages/Profile/Edit.tsx`

Import Loader2 and show spinner when saving:
```tsx
import { Loader2 } from "lucide-react";

// In the TimezoneSelector or surrounding UI:
{isSaving && (
    <div className="flex items-center gap-2 text-muted-foreground">
        <Loader2 className="h-4 w-4 animate-spin" />
        <span className="text-sm">Saving...</span>
    </div>
)}
```

#### 3.3 Dashboard Empty States

**File:** `resources/js/Pages/Dashboard.tsx:91-96`

Replace plain text with EmptyState component:
```tsx
import { Activity } from "lucide-react";
import { EmptyState } from "@/Components/ui/empty-state";

// Replace the plain text:
<EmptyState
    icon={Activity}
    title="No Recent Activity"
    description="Your recent actions will appear here as you use the app."
    size="sm"
/>
```

**File:** `resources/js/Pages/Dashboard.tsx:76-79`

Replace chart placeholder:
```tsx
import { BarChart3 } from "lucide-react";

<EmptyState
    icon={BarChart3}
    title="Analytics Coming Soon"
    description="Charts and insights will appear here once you have activity data."
    size="sm"
/>
```

#### 3.4 Profile Form Client-Side Validation (P2)

**File:** `resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.tsx`

Add Zod schema following Login.tsx pattern:
```tsx
import { z } from "zod";

const profileSchema = z.object({
    name: z.string().min(1, "Name is required").max(255, "Name is too long"),
    email: z.string().email("Please enter a valid email address"),
});

// Validate on blur or before submit
const validateField = (field: string, value: string) => {
    try {
        profileSchema.shape[field].parse(value);
        return null;
    } catch (e) {
        if (e instanceof z.ZodError) {
            return e.errors[0].message;
        }
        return "Invalid value";
    }
};
```

#### 3.5 Remove TODO Comment (P2)

**File:** `app/Services/PlanLimitService.php:76`

Replace TODO with documented stub:
```php
/**
 * Get the user's current plan tier.
 *
 * Returns 'pro' during trial, otherwise 'free'.
 * When billing feature is enabled, integrate Laravel Cashier here.
 */
public function getUserPlan(User $user): string
{
    // During trial, user has pro access
    if ($this->isOnTrial($user)) {
        return config('plans.trial.tier', 'pro');
    }

    // Cashier integration point:
    // if (config('features.billing.enabled') && $user->subscribed('default')) {
    //     return 'pro';
    // }

    return 'free';
}
```

---

## Files Affected

### Files to Create
| File | Purpose |
|------|---------|
| `app/Policies/UserPolicy.php` | Explicit user authorization |
| `tests/Feature/Auth/PasswordResetTest.php` | Password reset flow tests |
| `tests/Feature/Auth/EmailVerificationTest.php` | Email verification tests |
| `tests/Feature/ProfileTest.php` | Profile management tests |

### Files to Modify
| File | Change |
|------|--------|
| `routes/auth.php` | Add throttle middleware to 3 routes |
| `routes/api.php` | Fix token deletion response |
| `app/Http/Controllers/Auth/SocialAuthController.php` | Atomic disconnect with transaction |
| `app/Http/Controllers/ProfileController.php` | Add authorize() calls |
| `app/Providers/AppServiceProvider.php` | Register UserPolicy |
| `resources/js/Pages/Profile/Edit.tsx` | Error messages + loading state |
| `resources/js/hooks/useTimezone.tsx` | Better error handling |
| `resources/js/Pages/Dashboard.tsx` | EmptyState components |
| `resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.tsx` | Zod validation |
| `app/Services/PlanLimitService.php` | Clean up TODO comment |

---

## Risk Analysis

| Risk | Mitigation |
|------|------------|
| Rate limiting too aggressive | Use conservative limits (5/min for most, 3/min for email-related) |
| Policy breaks existing functionality | Policy only checks user owns resource, middleware unchanged |
| Transaction deadlocks | Short transaction, single row lock |
| Tests depend on feature flags | setUp() checks feature flag, skips if disabled |

---

## Verification Criteria

### Security
- [ ] Rate limiting returns 429 after threshold exceeded
- [ ] Token deletion returns 404 for non-existent tokens
- [ ] Social disconnect fails atomically when last auth method
- [ ] Profile update/delete respects authorization

### Tests
- [ ] `php artisan test --filter=PasswordResetTest` passes
- [ ] `php artisan test --filter=EmailVerificationTest` passes
- [ ] `php artisan test --filter=ProfileTest` passes

### UX
- [ ] Timezone save shows loading spinner
- [ ] Error messages include context
- [ ] Dashboard uses EmptyState components

---

## Rollback Plan

All changes are additive or low-risk modifications:

1. **Rate limiting:** Remove `->middleware('throttle:X,Y')` from routes
2. **Token fix:** Revert to always returning success
3. **Transaction:** Remove DB::transaction wrapper
4. **Policy:** Remove policy registration, remove authorize() calls
5. **Tests:** Delete test files (no impact on production)
6. **UX:** Revert component changes

---

## Implementation Order

Execute in this sequence for safest rollout:

1. **Create UserPolicy** (no functional change until registered)
2. **Register policy** in AppServiceProvider
3. **Add authorize() calls** to ProfileController
4. **Add rate limiting** to routes/auth.php
5. **Fix token deletion** response
6. **Add transaction** to social disconnect
7. **Create test files** (all 3)
8. **Run tests** to verify
9. **UX improvements** (error messages, loading states)
10. **Dashboard empty states**
11. **Profile form validation** (P2)
12. **Clean up TODO** (P2)

---

## Next Steps

Run: `/vibe-build PLAN_REFACTOR_audit_fixes.md`
