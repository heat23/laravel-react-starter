<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoadmapEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminRoadmapController extends Controller
{
    public function index(): Response
    {
        $entries = RoadmapEntry::withCount('feedbackSubmissions')
            ->orderBy('display_order')
            ->orderBy('status')
            ->get();

        return Inertia::render('Admin/Roadmap/Index', [
            'entries' => $entries,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Roadmap/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', Rule::in(['planned', 'in_progress', 'completed'])],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        RoadmapEntry::create($validated);

        return redirect()->route('admin.roadmap.index')->with('success', 'Roadmap entry created.');
    }

    public function update(Request $request, RoadmapEntry $roadmapEntry): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'string', Rule::in(['planned', 'in_progress', 'completed'])],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $roadmapEntry->update($validated);

        return back()->with('success', 'Roadmap entry updated.');
    }

    public function destroy(RoadmapEntry $roadmapEntry): RedirectResponse
    {
        $roadmapEntry->delete();

        return redirect()->route('admin.roadmap.index')->with('success', 'Roadmap entry deleted.');
    }
}
