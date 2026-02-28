<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminTokensController extends Controller
{
    public function __invoke(): Response
    {
        $stats = Cache::remember(AdminCacheKey::TOKENS_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            $now = now();
            $sevenDaysAgo = $now->copy()->subDays(7);

            $agg = DB::table('personal_access_tokens')
                ->selectRaw('COUNT(*) as total_tokens')
                ->selectRaw('COUNT(DISTINCT tokenable_id) as users_with_tokens')
                ->selectRaw('SUM(CASE WHEN last_used_at IS NOT NULL AND last_used_at >= ? THEN 1 ELSE 0 END) as recently_used', [$sevenDaysAgo])
                ->selectRaw('SUM(CASE WHEN expires_at IS NOT NULL AND expires_at < ? THEN 1 ELSE 0 END) as expired_tokens', [$now])
                ->selectRaw('SUM(CASE WHEN last_used_at IS NULL THEN 1 ELSE 0 END) as never_used')
                ->first();

            return [
                'total_tokens' => (int) $agg->total_tokens,
                'users_with_tokens' => (int) $agg->users_with_tokens,
                'recently_used' => (int) $agg->recently_used,
                'expired_tokens' => (int) $agg->expired_tokens,
                'never_used' => (int) $agg->never_used,
            ];
        });

        $mostActive = Cache::remember(AdminCacheKey::TOKENS_MOST_ACTIVE->value, AdminCacheKey::DEFAULT_TTL, function () {
            return DB::table('personal_access_tokens')
                ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
                ->whereNotNull('personal_access_tokens.last_used_at')
                ->orderByDesc('personal_access_tokens.last_used_at')
                ->limit(config('pagination.admin.recent_activity', 15))
                ->select(
                    'personal_access_tokens.name as token_name',
                    'personal_access_tokens.last_used_at',
                    'personal_access_tokens.abilities',
                    'users.name as user_name',
                    'users.email as user_email',
                )
                ->get()
                ->map(fn ($row) => [
                    'token_name' => $row->token_name,
                    'last_used_at' => $row->last_used_at,
                    'abilities' => json_decode($row->abilities, true),
                    'user_name' => $row->user_name,
                    'user_email' => $row->user_email,
                ])
                ->toArray();
        });

        return Inertia::render('Admin/Tokens/Dashboard', [
            'stats' => $stats,
            'most_active' => $mostActive,
        ]);
    }
}
