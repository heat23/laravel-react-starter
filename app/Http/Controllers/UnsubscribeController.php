<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnsubscribeController extends Controller
{
    public function unsubscribe(Request $request, int $userId): Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired unsubscribe link.');
        }

        // Safe: route is intentionally unauthenticated. Security is provided by the
        // signed URL — only the server can generate a valid signature for a given user ID.
        $user = User::where('id', $userId)->firstOrFail();

        UserSetting::setValue($user->id, 'marketing_emails', false);

        return Inertia::render('Unsubscribe', [
            'email' => $user->email,
        ]);
    }
}
