<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Http\Controllers\Controller;
use App\Jobs\SubmitIndexNowUrlsJob;
use App\Models\IndexNowSubmission;
use App\Services\CacheInvalidationManager;
use App\Services\IndexNowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminIndexNowController extends Controller
{
    public function __construct(
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function __invoke(Request $request): Response
    {
        abort_unless(config('features.indexnow.enabled', false), 404);

        $stats = Cache::remember(AdminCacheKey::INDEXNOW_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            $row = DB::table('indexnow_submissions')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count")
                ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count")
                ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
                ->selectRaw('SUM(url_count) as total_urls')
                ->where('created_at', '>=', now()->subDays(30))
                ->first();

            $total = (int) ($row->total ?? 0);
            $failed = (int) ($row->failed_count ?? 0);

            return [
                'total_submissions_30d' => $total,
                'successful_submissions_30d' => (int) ($row->success_count ?? 0),
                'failed_submissions_30d' => $failed,
                'pending_submissions_30d' => (int) ($row->pending_count ?? 0),
                'total_urls_30d' => (int) ($row->total_urls ?? 0),
                'failure_rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0,
            ];
        });

        $status = $request->string('status')->toString() ?: null;
        $trigger = $request->string('trigger')->toString() ?: null;

        if ($status !== null && ! in_array($status, ['pending', 'success', 'failed'], true)) {
            $status = null;
        }

        if ($trigger !== null && ! preg_match('/^[A-Za-z0-9:_\-]{1,50}$/', $trigger)) {
            $trigger = null;
        }

        $query = IndexNowSubmission::query()->latest();
        if ($status !== null) {
            $query->where('status', $status);
        }
        if ($trigger !== null) {
            $query->where('trigger', $trigger);
        }

        $submissions = $query
            ->paginate(config('pagination.admin.recent_events', 25))
            ->withQueryString()
            ->through(fn (IndexNowSubmission $s) => [
                'id' => $s->id,
                'uuid' => $s->uuid,
                'url_count' => $s->url_count,
                'status' => $s->status,
                'response_code' => $s->response_code,
                'attempts' => $s->attempts,
                'trigger' => $s->trigger,
                'submitted_at' => $s->submitted_at?->toISOString(),
                'created_at' => $s->created_at->toISOString(),
            ]);

        $triggers = IndexNowSubmission::query()
            ->whereNotNull('trigger')
            ->distinct()
            ->orderBy('trigger')
            ->pluck('trigger')
            ->toArray();

        return Inertia::render('Admin/IndexNow/Dashboard', [
            'stats' => $stats,
            'submissions' => $submissions,
            'triggers' => $triggers,
            'filters' => array_filter([
                'status' => $status,
                'trigger' => $trigger,
            ]),
            'key_location' => app(IndexNowService::class)->keyLocation(),
            'configured' => app(IndexNowService::class)->isConfigured(),
        ]);
    }

    public function show(IndexNowSubmission $submission): Response
    {
        abort_unless(config('features.indexnow.enabled', false), 404);

        return Inertia::render('Admin/IndexNow/SubmissionDetail', [
            'submission' => [
                'id' => $submission->id,
                'uuid' => $submission->uuid,
                'urls' => $submission->urls ?? [],
                'url_count' => $submission->url_count,
                'status' => $submission->status,
                'response_code' => $submission->response_code,
                'response_body' => $submission->response_body,
                'attempts' => $submission->attempts,
                'trigger' => $submission->trigger,
                'submitted_at' => $submission->submitted_at?->toISOString(),
                'created_at' => $submission->created_at->toISOString(),
            ],
        ]);
    }

    public function retry(IndexNowSubmission $submission): RedirectResponse
    {
        abort_unless(config('features.indexnow.enabled', false), 404);
        abort_unless($submission->status === 'failed', 422, 'Only failed submissions can be retried.');

        // Trigger column is VARCHAR(50). Appending ':retry' to a long original
        // trigger would silently truncate on MySQL and destroy the audit link.
        $retryTrigger = $submission->trigger === null
            ? 'retry'
            : substr($submission->trigger, 0, 50 - 6).':retry';

        $retry = IndexNowSubmission::create([
            'uuid' => (string) Str::uuid(),
            'urls' => $submission->urls ?? [],
            'url_count' => $submission->url_count,
            'status' => 'pending',
            'attempts' => 0,
            'trigger' => $retryTrigger,
        ]);

        SubmitIndexNowUrlsJob::dispatch($retry->id)
            ->onQueue(config('indexnow.queue', 'default'));

        $this->cacheManager->invalidateIndexNow();

        return redirect()->route('admin.indexnow.show', $retry->id)
            ->with('success', 'IndexNow submission re-queued.');
    }
}
