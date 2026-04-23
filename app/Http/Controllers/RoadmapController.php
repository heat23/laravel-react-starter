<?php

namespace App\Http\Controllers;

use App\Models\RoadmapEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if (Schema::hasTable('roadmap_entries') && RoadmapEntry::exists()) {
            $entries = $this->entriesFromDatabase($user);
        } else {
            $entries = $this->entriesFromJson($user);
        }

        return Inertia::render('Public/Roadmap', [
            'entries' => $entries,
        ]);
    }

    public function vote(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $result = DB::transaction(function () use ($user, $slug) {
            $existing = DB::table('roadmap_votes')
                ->where('user_id', $user->id)
                ->where('entry_slug', $slug)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                DB::table('roadmap_votes')
                    ->where('id', $existing->id)
                    ->delete();

                return false;
            }

            DB::table('roadmap_votes')->insert([
                'user_id' => $user->id,
                'entry_slug' => $slug,
                'created_at' => now(),
            ]);

            return true;
        });

        $total = DB::table('roadmap_votes')
            ->where('entry_slug', $slug)
            ->count();

        return response()->json(['voted' => $result, 'votes' => $total]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entriesFromDatabase(?object $user): array
    {
        $entries = RoadmapEntry::withCount('feedbackSubmissions')
            ->orderBy('display_order')
            ->get();

        $slugs = $entries->pluck('slug')->all();

        $voteCounts = DB::table('roadmap_votes')
            ->whereIn('entry_slug', $slugs)
            ->groupBy('entry_slug')
            ->selectRaw('entry_slug, count(*) as total')
            ->pluck('total', 'entry_slug');

        $userVotes = $user
            ? DB::table('roadmap_votes')->where('user_id', $user->id)->pluck('entry_slug')->all()
            : [];

        return $entries->map(function (RoadmapEntry $entry) use ($voteCounts, $userVotes) {
            return [
                'slug' => $entry->slug,
                'title' => $entry->title,
                'description' => $entry->description,
                'status' => $entry->status,
                'votes' => (int) ($voteCounts[$entry->slug] ?? 0),
                'has_voted' => in_array($entry->slug, $userVotes, true),
                'feedback_count' => $entry->feedback_submissions_count,
            ];
        })->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entriesFromJson(?object $user): array
    {
        $rawEntries = [];
        $path = public_path('roadmap.json');

        if (file_exists($path)) {
            $rawEntries = json_decode(file_get_contents($path), true) ?? [];
        }

        $slugs = array_map(
            fn ($e) => $e['slug'] ?? Str::slug($e['title'] ?? ''),
            $rawEntries
        );

        $voteCounts = DB::table('roadmap_votes')
            ->whereIn('entry_slug', $slugs)
            ->groupBy('entry_slug')
            ->selectRaw('entry_slug, count(*) as total')
            ->pluck('total', 'entry_slug');

        $userVotes = $user
            ? DB::table('roadmap_votes')->where('user_id', $user->id)->pluck('entry_slug')->all()
            : [];

        return array_map(function (array $entry) use ($voteCounts, $userVotes) {
            $slug = $entry['slug'] ?? Str::slug($entry['title'] ?? '');

            return array_merge($entry, [
                'slug' => $slug,
                'votes' => (int) ($voteCounts[$slug] ?? 0),
                'has_voted' => in_array($slug, $userVotes, true),
            ]);
        }, $rawEntries);
    }
}
