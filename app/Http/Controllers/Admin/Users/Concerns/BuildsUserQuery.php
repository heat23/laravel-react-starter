<?php

namespace App\Http\Controllers\Admin\Users\Concerns;

use App\Helpers\QueryHelper;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait BuildsUserQuery
{
    /** @return Builder<User> */
    protected function buildUserQuery(array $validated): Builder
    {
        $status = $validated['status'] ?? 'all';
        $query = match ($status) {
            'active' => User::query(),
            'deactivated' => User::onlyTrashed(),
            default => User::withTrashed(),
        };

        if (! empty($validated['search'])) {
            $query->where(function ($q) use ($validated) {
                QueryHelper::whereLike($q, 'name', $validated['search']);
                QueryHelper::whereLike($q, 'email', $validated['search'], 'or');
            });
        }

        if (isset($validated['admin']) && $validated['admin'] !== '') {
            $query->where('is_admin', (bool) $validated['admin']);
        }

        if (isset($validated['verified']) && $validated['verified'] !== '') {
            if ((int) $validated['verified'] === 1) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        return $query;
    }
}
