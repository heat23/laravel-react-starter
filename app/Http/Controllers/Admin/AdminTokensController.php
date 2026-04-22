<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Enums\AuditEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminTokenExportRequest;
use App\Http\Requests\Admin\AdminTokenIndexRequest;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use App\Support\CsvExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminTokensController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

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

    public function index(AdminTokenIndexRequest $request): Response
    {
        $query = DB::table('personal_access_tokens')
            ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->where('personal_access_tokens.tokenable_type', 'App\\Models\\User')
            ->select(
                'personal_access_tokens.id',
                'personal_access_tokens.name as token_name',
                'personal_access_tokens.abilities',
                'personal_access_tokens.last_used_at',
                'personal_access_tokens.expires_at',
                'personal_access_tokens.created_at',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
            )
            ->orderByDesc('personal_access_tokens.last_used_at');

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->whereRaw("personal_access_tokens.name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereRaw("users.email LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereRaw("users.name LIKE ? ESCAPE '|'", ["%{$escaped}%"]);
            });
        }

        $tokens = $query
            ->paginate(config('pagination.admin.users', 25))
            ->through(fn ($row) => [
                'id' => $row->id,
                'token_name' => $row->token_name,
                'abilities' => json_decode($row->abilities, true),
                'last_used_at' => $row->last_used_at,
                'expires_at' => $row->expires_at,
                'created_at' => $row->created_at,
                'user_id' => $row->user_id,
                'user_name' => $row->user_name,
                'user_email' => $row->user_email,
            ]);

        return Inertia::render('Admin/Tokens/Index', [
            'tokens' => $tokens,
            'filters' => $request->only('search'),
        ]);
    }

    public function export(AdminTokenExportRequest $request): StreamedResponse
    {
        $this->auditService->log(AuditEvent::ADMIN_TOKENS_EXPORTED, [
            'filters' => $request->validated(),
        ]);

        $query = DB::table('personal_access_tokens')
            ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->where('personal_access_tokens.tokenable_type', 'App\\Models\\User')
            ->select(
                'personal_access_tokens.id',
                'personal_access_tokens.name as token_name',
                'personal_access_tokens.abilities',
                'personal_access_tokens.last_used_at',
                'personal_access_tokens.expires_at',
                'personal_access_tokens.created_at',
                'users.name as user_name',
                'users.email as user_email',
            )
            ->orderByDesc('personal_access_tokens.last_used_at');

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->whereRaw("personal_access_tokens.name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereRaw("users.email LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereRaw("users.name LIKE ? ESCAPE '|'", ["%{$escaped}%"]);
            });
        }

        $query->limit(config('pagination.export.max_rows', 10000));

        return (new CsvExport([
            'ID' => 'id',
            'Token Name' => 'token_name',
            'User Name' => 'user_name',
            'User Email' => 'user_email',
            'Abilities' => fn ($row) => is_string($row->abilities)
                ? implode(', ', json_decode($row->abilities, true) ?? [])
                : '',
            'Last Used' => fn ($row) => $row->last_used_at ?? '',
            'Expires' => fn ($row) => $row->expires_at ?? '',
            'Created' => 'created_at',
        ]))->filename('tokens-'.now()->format('Y-m-d').'.csv')
            ->fromCollection($query->cursor());
    }

    public function revoke(int $id): RedirectResponse
    {
        $token = DB::table('personal_access_tokens')->where('id', $id)->first();
        abort_unless((bool) $token, 404);

        DB::table('personal_access_tokens')->where('id', $id)->delete();

        $this->auditService->log(AuditEvent::ADMIN_TOKEN_REVOKED, [
            'token_id' => $id,
            'token_name' => $token->name,
            'user_id' => $token->tokenable_id,
        ]);

        $this->cacheManager->invalidateTokens();

        return redirect()->route('admin.tokens.index')
            ->with('success', 'Token revoked successfully.');
    }
}
