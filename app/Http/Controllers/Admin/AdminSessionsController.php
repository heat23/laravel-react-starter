<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalyticsEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSessionIndexRequest;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminSessionsController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheInvalidation,
    ) {}

    public function index(AdminSessionIndexRequest $request): Response
    {
        $driver = Config::get('session.driver');
        $sessions = (object) [];

        if ($driver === 'database') {
            $allowedSorts = ['last_activity', 'ip_address'];
            $sort = in_array($request->validated('sort'), $allowedSorts, true)
                ? $request->validated('sort')
                : 'last_activity';
            $dir = ($request->validated('dir') ?? 'desc') === 'asc' ? 'asc' : 'desc';

            $query = DB::table('sessions')
                ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
                ->select(
                    'sessions.id as session_id',
                    'sessions.user_id',
                    'users.name as user_name',
                    'users.email as user_email',
                    'sessions.ip_address',
                    'sessions.user_agent',
                    'sessions.last_activity',
                )
                ->whereNotNull('sessions.user_id');

            if ($search = $request->validated('search')) {
                $escaped = QueryHelper::escapeLike($search);
                $query->where(function ($q) use ($escaped): void {
                    $q->whereRaw("users.name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                        ->orWhereRaw("users.email LIKE ? ESCAPE '|'", ["%{$escaped}%"]);
                });
            }

            $sessions = $query
                ->orderBy("sessions.{$sort}", $dir)
                ->paginate(config('pagination.admin.users', 25))
                ->withQueryString()
                ->through(fn ($row) => [
                    'session_id' => $row->session_id,
                    'user_id' => $row->user_id,
                    'user_name' => $row->user_name ?? '[Deleted User]',
                    'user_email' => $row->user_email ?? '',
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => date('c', $row->last_activity),
                ]);
        }

        return Inertia::render('Admin/Sessions/Index', [
            'sessions' => $sessions,
            'driver' => $driver,
            'driverSupported' => $driver === 'database',
            'filters' => $request->only('search', 'sort', 'dir'),
        ]);
    }

    public function destroy(int $userId, Request $request): RedirectResponse
    {
        if ($userId === $request->user()?->id) {
            return redirect()->route('admin.sessions.index')
                ->with('error', 'You cannot terminate your own sessions.');
        }

        if (Config::get('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $userId)->delete();
        }

        $this->auditService->log(AnalyticsEvent::ADMIN_SESSION_TERMINATED, [
            'target_user_id' => $userId,
        ]);

        $this->cacheInvalidation->invalidateDashboard();

        return redirect()->route('admin.sessions.index')
            ->with('success', 'User sessions terminated.');
    }
}
