<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login Form Request
 *
 * Handles validation and authentication for login requests.
 * Includes rate limiting to prevent brute force attacks.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $hits = RateLimiter::hit($this->throttleKey(), 3600);

            if ($hits >= 5) {
                // Threshold reached — apply progressive lockout and reset attempt counter
                $decay = $this->applyProgressiveLockout();
                RateLimiter::clear($this->throttleKey());
                $lockedUntil = now()->addSeconds($decay)->timestamp;
                // Store email+IP scoped key (blocks this exact attacker pair)
                Cache::put($this->lockoutKey(), $lockedUntil, $decay + 60);
                // Store email-only key so IP rotation cannot bypass the active lockout
                Cache::put($this->lockoutEmailKey(), $lockedUntil, $decay + 60);
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Cache::forget($this->lockoutKey());
        Cache::forget($this->lockoutEmailKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * Checks both the email+IP key and the email-only key so that an attacker
     * cannot bypass an active lockout simply by rotating IP addresses.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Email+IP key covers the originating pair; email-only key covers IP rotation
        $lockedUntil = Cache::get($this->lockoutKey()) ?? Cache::get($this->lockoutEmailKey());

        if (! $lockedUntil || now()->timestamp >= $lockedUntil) {
            return;
        }

        event(new Lockout($this));

        $seconds = max(0, $lockedUntil - now()->timestamp);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * Increments the lockout event counter and returns the decay for the new tier.
     *
     * Lockout events are tracked per email (not IP) so rotating IPs cannot
     * reset the counter. Escalation: 5 min → 30 min → 2 hours → 24 hours.
     * The 24-hour TTL on the event counter resets the escalation naturally.
     */
    public function applyProgressiveLockout(): int
    {
        $key = $this->lockoutEventKey();
        // Cache::add sets the TTL only on first creation (no-op if key exists).
        // Cache::increment is atomic, preventing under-counting under concurrent requests.
        Cache::add($key, 0, now()->addDay());
        $count = Cache::increment($key);

        return $this->decayForLockoutCount($count);
    }

    /**
     * Maps cumulative lockout count to lockout duration in seconds.
     */
    public function decayForLockoutCount(int $count): int
    {
        return match (true) {
            $count >= 4 => 86400,  // 24 hours
            $count >= 3 => 7200,   // 2 hours
            $count >= 2 => 1800,   // 30 minutes
            default => 300,    // 5 minutes (first lockout)
        };
    }

    /**
     * Cache key for the active lockout expiry timestamp (email+IP scoped).
     */
    public function lockoutKey(): string
    {
        return 'login_locked:'.Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * Cache key for the active lockout expiry timestamp (email-only scoped).
     * Checked alongside lockoutKey() so IP rotation cannot bypass an active lockout.
     */
    public function lockoutEmailKey(): string
    {
        return 'login_locked_email:'.Str::transliterate(Str::lower($this->string('email')));
    }

    /**
     * Cache key for tracking cumulative lockout events per email address.
     * Email-only (no IP) prevents bypass by rotating IP addresses.
     */
    public function lockoutEventKey(): string
    {
        return 'login_lockout_events:'.Str::transliterate(Str::lower($this->string('email')));
    }
}
