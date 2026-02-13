<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminSocialAuthController extends Controller
{
    public function __invoke(): Response
    {
        $stats = Cache::remember(AdminCacheKey::SOCIAL_AUTH_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            $agg = DB::table('social_accounts')
                ->selectRaw('COUNT(*) as total_linked')
                ->selectRaw('COUNT(DISTINCT user_id) as users_with_social')
                ->selectRaw('SUM(CASE WHEN token_expires_at IS NOT NULL AND token_expires_at < ? THEN 1 ELSE 0 END) as expired_tokens', [now()])
                ->first();

            $byProvider = DB::table('social_accounts')
                ->select('provider', DB::raw('COUNT(*) as count'))
                ->groupBy('provider')
                ->pluck('count', 'provider')
                ->toArray();

            return [
                'total_linked' => (int) $agg->total_linked,
                'users_with_social' => (int) $agg->users_with_social,
                'by_provider' => $byProvider,
                'expired_tokens' => (int) $agg->expired_tokens,
            ];
        });

        return Inertia::render('Admin/SocialAuth/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
