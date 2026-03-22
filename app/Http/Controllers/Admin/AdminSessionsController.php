<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalyticsEvent;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
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
    ) {}

    public function index(): Response
    {
        $driver = Config::get('session.driver');
        $sessions = collect();

        if ($driver === 'database') {
            $sessions = DB::table('sessions')
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
                ->whereNotNull('sessions.user_id')
                ->orderByDesc('sessions.last_activity')
                ->paginate(config('pagination.admin.users', 25))
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

        return redirect()->route('admin.sessions.index')
            ->with('success', 'User sessions terminated.');
    }
}
