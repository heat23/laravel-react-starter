<?php

namespace App\Http\Controllers;

use App\Enums\AnalyticsEvent;
use App\Http\Requests\ContactRequest;
use App\Http\Requests\ContactSalesRequest;
use App\Models\ContactSubmission;
use App\Notifications\ContactNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\AnonymousNotifiable;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function show(): Response
    {
        return Inertia::render('Contact');
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $submission = ContactSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        // Audit log (regression guard — keep existing behaviour)
        $this->auditService->log(AnalyticsEvent::CONTACT_SUBMITTED, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'submission_id' => $submission->id,
        ]);

        // Notify operator
        $notifyEmail = config('contact.notify_email');
        if ($notifyEmail) {
            (new AnonymousNotifiable)
                ->route('mail', $notifyEmail)
                ->notify(new ContactNotification($submission));
        }

        return redirect()->route('contact.show')->with('success', 'Your message has been sent. We\'ll get back to you soon.');
    }

    public function sales(ContactSalesRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $submission = ContactSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => 'Enterprise pricing',
            'message' => "Company: {$validated['company']}\nSeats needed: {$validated['seats_needed']}\n\n{$validated['message']}",
            'status' => 'new',
        ]);

        $this->auditService->log(AnalyticsEvent::CONTACT_SUBMITTED, [
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

        return back()->with('success', 'Thank you! Our sales team will be in touch shortly.');
    }
}
