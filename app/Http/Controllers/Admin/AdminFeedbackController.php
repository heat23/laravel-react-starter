<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminFeedbackController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Feedback::with(['user' => fn ($q) => $q->withTrashed()])
            ->latest();

        if ($request->filled('type')) {
            $query->byType($request->input('type'));
        }

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
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

        if (isset($validated['status']) && $validated['status'] === 'resolved' && $feedback->status !== 'resolved') {
            $validated['resolved_at'] = now();
        }

        $feedback->update($validated);

        return back()->with('success', 'Feedback updated.');
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback deleted.');
    }
}
