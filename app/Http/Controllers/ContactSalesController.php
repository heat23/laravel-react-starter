<?php

namespace App\Http\Controllers;

use App\Enums\AuditEvent;
use App\Http\Requests\ContactSalesRequest;
use App\Models\ContactSubmission;
use App\Notifications\ContactNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\AnonymousNotifiable;

class ContactSalesController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function __invoke(ContactSalesRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $messageParts = [];
        $messageParts[] = "Company: {$validated['company']}";
        $messageParts[] = "Seats needed: {$validated['seats_needed']}";

        if (! empty($validated['message'])) {
            $messageParts[] = "Additional notes: {$validated['message']}";
        }

        $submission = ContactSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => 'Enterprise pricing',
            'message' => implode("\n", $messageParts),
            'status' => 'new',
        ]);

        $this->auditService->log(AuditEvent::CONTACT_SUBMITTED, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => 'Enterprise pricing',
            'company' => $validated['company'],
            'seats_needed' => $validated['seats_needed'],
            'submission_id' => $submission->id,
        ]);

        $notifyEmail = config('contact.notify_email');
        if ($notifyEmail) {
            (new AnonymousNotifiable)
                ->route('mail', $notifyEmail)
                ->notify(new ContactNotification($submission));
        }

        return redirect()->route('pricing')->with('success', 'Thanks for your interest! Our sales team will be in touch within one business day.');
    }
}
