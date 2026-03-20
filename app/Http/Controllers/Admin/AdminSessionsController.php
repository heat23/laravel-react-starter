<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminSessionsController extends Controller
{
    public function index(): Response
    {
        $driver = Config::get('session.driver');
        $sessions = collect();

        if ($driver === 'database') {
            $sessions = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
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
                    'user_name' => $row->user_name,
                    'user_email' => $row->user_email,
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

    public function destroy(int $userId): RedirectResponse
    {
        if (Config::get('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $userId)->delete();
        }

        return redirect()->route('admin.sessions.index')
            ->with('success', 'User sessions terminated.');
    }
}
