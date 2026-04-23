<?php

namespace App\Http\Controllers\Admin\Users\Concerns;

use App\Models\User;

trait InvalidatesUserCaches
{
    private function invalidateUserCaches(User $user): void
    {
        $this->cacheManager->invalidateUser($user->id);
    }
}
