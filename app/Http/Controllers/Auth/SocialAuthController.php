<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

/**
 * Social Authentication Controller
 *
 * Handles OAuth authentication via providers like Google and GitHub.
 * Only active when FEATURE_SOCIAL_AUTH=true in environment.
 *
 * Routes are registered in routes/auth.php when feature is enabled.
 */
class SocialAuthController extends Controller
{
    /**
     * Supported OAuth providers.
     */
    protected array $providers = ['google', 'github'];

    public function __construct(
        private SocialAuthService $socialAuthService
    ) {}

    /**
     * Redirect the user to the OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse
    {
        // Verify feature is enabled
        if (! config('features.social_auth.enabled', false)) {
            abort(404, 'Social authentication is not enabled.');
        }

        // Validate provider
        if (! in_array($provider, $this->providers)) {
            abort(404, 'Unsupported authentication provider.');
        }

        // Check if provider is configured
        if (! in_array($provider, config('features.social_auth.providers', []))) {
            abort(404, 'This authentication provider is not configured.');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth callback from the provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        // Verify feature is enabled
        if (! config('features.social_auth.enabled', false)) {
            abort(404, 'Social authentication is not enabled.');
        }

        // Validate provider
        if (! in_array($provider, $this->providers)) {
            abort(404, 'Unsupported authentication provider.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Unable to authenticate with '.$provider.'. Please try again.');
        }

        // Find or create user
        $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);

        // Link social account
        $this->socialAuthService->linkSocialAccount($user, $socialUser, $provider);

        // Log the user in
        Auth::login($user, remember: true);

        // Update last login timestamp
        if (method_exists($user, 'updateLastLogin')) {
            $user->updateLastLogin();
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Disconnect a social account from the user's profile.
     */
    public function disconnect(Request $request, string $provider): RedirectResponse
    {
        // Verify feature is enabled
        if (! config('features.social_auth.enabled', false)) {
            abort(404, 'Social authentication is not enabled.');
        }

        $user = $request->user();

        try {
            DB::transaction(function () use ($user, $provider) {
                // Don't allow disconnecting if user has no password and this is their only auth method
                if (! $user->hasPassword()) {
                    // Lock social accounts to prevent race conditions during count check
                    $socialAccountCount = $user->socialAccounts()->lockForUpdate()->count();
                    if ($socialAccountCount <= 1) {
                        throw new \Exception('Cannot disconnect last authentication method.');
                    }
                }

                // Delete the social account
                $user->socialAccounts()->where('provider', $provider)->delete();
            });

            return back()->with('status', ucfirst($provider).' account disconnected.');
        } catch (\Exception $e) {
            return back()->with('error', 'You must set a password before disconnecting your last social account.');
        }
    }
}
