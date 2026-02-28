<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\CancelOrphanedStripeSubscription;
use App\Services\AuditService;
use App\Services\BillingService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private BillingService $billingService,
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'timezone' => $request->user()->getSetting('timezone', config('app.timezone')),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->authorize('update', $request->user());

        $user = $request->user();
        $user->fill($request->validated());

        $emailChanged = $user->isDirty('email');
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->auditService->log('profile.updated', [
            'email' => $user->email,
            'email_changed' => $emailChanged,
        ]);

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $this->authorize('delete', $request->user());

        $user = $request->user();

        // Cancel any active subscription before deletion
        if (config('features.billing.enabled')) {
            $user->loadMissing('subscriptions.items');

            if ($user->subscribed('default')) {
                try {
                    $this->billingService->cancelSubscription($user, immediately: true);
                } catch (\Throwable $e) {
                    Log::error('Failed to cancel subscription during account deletion', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);

                    if ($user->stripe_id) {
                        CancelOrphanedStripeSubscription::dispatch($user->stripe_id, $user->id);
                    }
                }
            }
        }

        $this->auditService->log('account.deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        Auth::logout();

        $user->purgePersonalData();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
