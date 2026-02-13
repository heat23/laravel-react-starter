<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminTwoFactorController extends Controller
{
    public function __invoke(): Response
    {
        $stats = Cache::remember(AdminCacheKey::TWO_FACTOR_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            $totalUsers = User::count();
            $enabledCount = 0;

            if (Schema::hasTable('two_factor_authentications')) {
                $enabledCount = DB::table('two_factor_authentications')
                    ->whereNotNull('enabled_at')
                    ->count();
            }

            return [
                'total_users' => $totalUsers,
                'two_factor_enabled' => $enabledCount,
                'adoption_rate' => $totalUsers > 0
                    ? round(($enabledCount / $totalUsers) * 100, 1)
                    : 0,
                'without_two_factor' => $totalUsers - $enabledCount,
            ];
        });

        return Inertia::render('Admin/TwoFactor/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
