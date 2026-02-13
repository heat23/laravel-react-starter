<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class AdminImpersonationController extends Controller
{
    public function __construct(
        private AuditService $auditService,
    ) {}

    public function start(Request $request, User $user): RedirectResponse
    {
        if ($request->session()->has('admin_impersonating_from')) {
            return back()->with('error', 'Already impersonating a user. Stop current impersonation first.');
        }

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Cannot impersonate yourself.');
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot impersonate another admin.');
        }

        if ($user->trashed()) {
            return back()->with('error', 'Cannot impersonate a deactivated user.');
        }

        $this->auditService->log('admin.impersonation_started', [
            'admin_id' => $request->user()->id,
            'admin_email' => $request->user()->email,
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ]);

        $adminId = $request->user()->id;
        $adminName = $request->user()->name;
        Auth::login($user);
        $request->session()->put('admin_impersonating_from', Crypt::encryptString((string) $adminId));
        $request->session()->put('admin_impersonating_name', $adminName);

        return redirect()->route('dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $encryptedAdminId = $request->session()->get('admin_impersonating_from');

        if (! $encryptedAdminId) {
            return redirect()->route('dashboard');
        }

        try {
            $adminId = (int) Crypt::decryptString($encryptedAdminId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            $request->session()->forget(['admin_impersonating_from', 'admin_impersonating_name']);
            Auth::logout();

            return redirect()->route('login');
        }

        $admin = User::withTrashed()->find($adminId);

        if (! $admin || $admin->trashed() || ! $admin->isAdmin()) {
            $request->session()->forget(['admin_impersonating_from', 'admin_impersonating_name']);
            Auth::logout();

            return redirect()->route('login');
        }

        $this->auditService->log('admin.impersonation_stopped', [
            'admin_id' => $adminId,
            'impersonated_user_id' => $request->user()->id,
            'impersonated_email' => $request->user()->email,
        ]);

        $request->session()->forget(['admin_impersonating_from', 'admin_impersonating_name']);
        Auth::login($admin);

        return redirect()->route('admin.users.index');
    }
}
