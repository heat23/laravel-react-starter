<?php

namespace App\Http\Controllers\Admin\Users;

use App\Enums\AuditEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSendPasswordResetRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class AdminUserPasswordResetController extends Controller
{
    public function __construct(
        private AuditService $auditService,
    ) {}

    public function sendPasswordReset(AdminSendPasswordResetRequest $request, User $user): RedirectResponse
    {
        if (! $user->hasPassword()) {
            return back()->with('error', 'User has no password (OAuth-only account).');
        }

        /** @var PasswordBroker $broker */
        $broker = Password::broker();
        $token = $broker->createToken($user);
        $user->sendPasswordResetNotification($token);

        $this->auditService->log(AuditEvent::ADMIN_PASSWORD_RESET_SENT, [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ]);

        return back()->with('success', 'Password reset email sent.');
    }
}
