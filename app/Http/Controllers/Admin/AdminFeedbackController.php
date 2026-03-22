<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalyticsEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminFeedbackIndexRequest;
use App\Models\Feedback;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminFeedbackController extends Controller
{
    public function __construct(
        private AuditService $auditService,
    ) {}

    public function index(AdminFeedbackIndexRequest $request): Response
    {
        $query = Feedback::with(['user' => fn ($q) => $q->withTrashed()])
            ->latest();

        if ($request->validated('type')) {
            $query->byType($request->validated('type'));
        }

        if ($request->validated('status')) {
            $query->byStatus($request->validated('status'));
        }

        $feedback = $query->paginate(config('pagination.admin.feedback', 50))
            ->withQueryString();

        return Inertia::render('Admin/Feedback/Index', [
            'feedback' => $feedback,
            'filters' => $request->only('type', 'status'),
            'counts' => [
                'open' => Feedback::byStatus('open')->count(),
                'in_review' => Feedback::byStatus('in_review')->count(),
                'resolved' => Feedback::byStatus('resolved')->count(),
            ],
        ]);
    }

    public function show(Feedback $feedback): Response
    {
        $feedback->load(['user' => fn ($q) => $q->withTrashed()]);

        return Inertia::render('Admin/Feedback/Show', [
            'feedback' => $feedback,
        ]);
    }

    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['open', 'in_review', 'resolved', 'declined'])],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high'])],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'roadmap_entry_id' => ['sometimes', 'nullable', 'integer', 'exists:roadmap_entries,id'],
        ]);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'resolved' && $feedback->status !== 'resolved') {
                $validated['resolved_at'] = now();
            } elseif ($validated['status'] !== 'resolved' && $feedback->status === 'resolved') {
                $validated['resolved_at'] = null;
            }
        }

        $feedback->update($validated);

        $this->auditService->log(AnalyticsEvent::ADMIN_FEEDBACK_UPDATED, [
            'feedback_id' => $feedback->id,
            'changes' => array_keys($validated),
        ]);

        return back()->with('success', 'Feedback updated.');
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $this->auditService->log(AnalyticsEvent::ADMIN_FEEDBACK_DELETED, [
            'feedback_id' => $feedback->id,
            'type' => $feedback->type,
            'status' => $feedback->status,
        ]);

        $feedback->delete();

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback deleted.');
    }
}
