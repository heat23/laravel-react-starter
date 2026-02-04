<?php

namespace App\Services;

use App\Models\User;

/**
 * Session Data Migration Service
 *
 * Placeholder service for migrating anonymous session data to a user account.
 *
 * Use this pattern when your app allows anonymous users to perform actions
 * (like creating a cart, running a scan, building a configuration) that
 * should be preserved when they register or log in.
 *
 * Example implementations:
 * - E-commerce: Migrate guest cart items to user's cart
 * - SaaS: Migrate anonymous scan results to user's project
 * - Wizard: Migrate form progress to user's draft
 */
class SessionDataMigrationService
{
    /**
     * Session key for storing anonymous user data.
     * Override this in your implementation.
     */
    protected string $sessionKey = 'anonymous_data';

    /**
     * Check if there is session data to migrate.
     *
     * Override this method to check your specific session keys.
     */
    public function hasSessionData(): bool
    {
        // Placeholder: always returns false
        // Implement: return session()->has($this->sessionKey);
        return false;
    }

    /**
     * Get a summary of the session data for display during registration.
     *
     * This is shown to users so they know their data will be preserved.
     *
     * @return array{items_count: int, description: string}|null
     */
    public function getSessionDataSummary(): ?array
    {
        if (! $this->hasSessionData()) {
            return null;
        }

        // Placeholder: return null
        // Implement: return [
        //     'items_count' => count(session($this->sessionKey, [])),
        //     'description' => 'Your saved items will be migrated to your account.',
        // ];
        return null;
    }

    /**
     * Migrate session data to the user's account.
     *
     * Called after successful registration or login.
     *
     * @return array{migrated: bool, items_count: int, project_items: int}
     */
    public function migrateSessionData(User $user): array
    {
        if (! $this->hasSessionData()) {
            return [
                'migrated' => false,
                'items_count' => 0,
                'project_items' => 0,
            ];
        }

        // Placeholder: return empty result
        // Implement your migration logic here, for example:
        //
        // $sessionData = session($this->sessionKey);
        //
        // foreach ($sessionData as $item) {
        //     $user->items()->create($item);
        // }
        //
        // session()->forget($this->sessionKey);
        //
        // return [
        //     'migrated' => true,
        //     'items_count' => count($sessionData),
        //     'project_items' => count($sessionData),
        // ];

        return [
            'migrated' => false,
            'items_count' => 0,
            'project_items' => 0,
        ];
    }

    /**
     * Clear session data without migrating.
     *
     * Use when user explicitly chooses not to migrate,
     * or when session data is no longer valid.
     */
    public function clearSessionData(): void
    {
        session()->forget($this->sessionKey);
    }
}
