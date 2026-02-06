<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialUser;

/**
 * Social Authentication Service
 *
 * Handles the logic for OAuth user management:
 * - Finding existing users by social account or email
 * - Creating new users from OAuth data
 * - Linking/updating social account credentials
 *
 * Only used when FEATURE_SOCIAL_AUTH=true
 */
class SocialAuthService
{
    /**
     * Find an existing user or create a new one from OAuth data.
     */
    public function findOrCreateUser(SocialUser $socialUser, string $provider): User
    {
        // First, check if we have a social account for this provider/ID
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            return $socialAccount->user;
        }

        // Check if user exists with this email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            return $user;
        }

        // Create new user
        return User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'password' => null, // No password for OAuth-only users
            'email_verified_at' => now(), // OAuth users are considered verified
            'signup_source' => $provider,
        ]);
    }

    /**
     * Link or update a social account for a user.
     */
    public function linkSocialAccount(User $user, SocialUser $socialUser, string $provider): SocialAccount
    {
        return SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_id' => $socialUser->getId(),
                'token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'token_expires_at' => isset($socialUser->expiresIn)
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
            ]
        );
    }
}
