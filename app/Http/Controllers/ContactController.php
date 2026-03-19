<?php

namespace App\Http\Controllers;

use App\Enums\AnalyticsEvent;
use App\Http\Requests\ContactRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
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

        $this->auditService->log(AnalyticsEvent::CONTACT_SUBMITTED, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
        ]);

        return redirect()->route('contact.show')->with('success', 'Your message has been sent. We\'ll get back to you soon.');
    }
}
