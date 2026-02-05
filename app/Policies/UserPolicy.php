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
