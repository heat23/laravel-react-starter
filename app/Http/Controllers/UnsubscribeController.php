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
        // hasValidSignature() accepts both permanent signed URLs (no `expires` param, used
        // in emails sent before the switch to temporary URLs) and temporary signed URLs
        // (with `expires` param, used going forward). Both formats share the same HMAC key,
        // so old links remain valid indefinitely while new links expire after 1 year.
        // If validation fails the controller aborts with 403 directly — no signed middleware.
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired unsubscribe link.');
        }

        // Safe: route is intentionally unauthenticated. Security is provided by the
        // signed URL — only the server can generate a valid signature for a given user ID.
        $user = User::where('id', $userId)->firstOrFail();

        UserSetting::setValue($user->id, 'marketing_emails', false);

        return Inertia::render('App/Unsubscribe', [
            'email' => $user->email,
        ]);
    }
}
