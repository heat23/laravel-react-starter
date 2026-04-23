<?php

namespace App\Jobs;

use App\Models\IndexNowSubmission;
use App\Services\CacheInvalidationManager;
use App\Services\IndexNowService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubmitIndexNowUrlsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly int $submissionId,
    ) {}

    public function handle(IndexNowService $service, CacheInvalidationManager $cacheManager): void
    {
        $submission = IndexNowSubmission::find($this->submissionId);

        if (! $submission) {
            return;
        }

        if (! $service->isConfigured()) {
            $submission->update([
                'status' => 'failed',
                'response_body' => 'IndexNow not configured (feature flag off or key missing).',
            ]);
            $cacheManager->invalidateIndexNow();

            return;
        }

        $submission->increment('attempts');

        $body = [
            'host' => config('indexnow.host'),
            'key' => config('indexnow.key'),
            'keyLocation' => $service->keyLocation(),
            'urlList' => $submission->urls ?? [],
        ];

        try {
            $response = Http::timeout((int) config('indexnow.timeout', 15))
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                    'User-Agent' => config('app.name').' IndexNow/1.0',
                ])
                ->withBody(json_encode($body), 'application/json; charset=utf-8')
                ->post((string) config('indexnow.endpoint'));

            $status = $response->status();
            $responseBody = substr($response->body(), 0, 1000);

            if (in_array($status, [200, 202], true)) {
                $submission->update([
                    'status' => 'success',
                    'response_code' => $status,
                    'response_body' => $responseBody,
                    'submitted_at' => now(),
                ]);
                $cacheManager->invalidateIndexNow();

                return;
            }

            // Non-retryable client errors: bad payload, bad key, cross-host URLs.
            // IndexNow docs class these as configuration bugs, not transient.
            if (in_array($status, [400, 403, 422], true)) {
                $submission->update([
                    'status' => 'failed',
                    'response_code' => $status,
                    'response_body' => $responseBody,
                ]);
                $cacheManager->invalidateIndexNow();
                Log::channel('single')->warning('IndexNow submission rejected (non-retryable)', [
                    'submission_id' => $submission->id,
                    'status' => $status,
                    'url_count' => $submission->url_count,
                ]);
                $this->fail(new \RuntimeException("IndexNow rejected submission with status {$status}"));

                return;
            }

            // 429 + 5xx → retryable
            $submission->update([
                'response_code' => $status,
                'response_body' => $responseBody,
            ]);

            if ($this->attempts() >= $this->tries) {
                $submission->update(['status' => 'failed']);
                $cacheManager->invalidateIndexNow();
            }

            throw new \RuntimeException("IndexNow returned transient status {$status}");
        } catch (\RuntimeException $e) {
            // Re-throw known non-HTTP runtime exceptions we raised above so the
            // queue can retry or mark as failed. Log only on final attempt.
            if ($this->attempts() >= $this->tries) {
                Log::channel('single')->warning('IndexNow submission failed (final attempt)', [
                    'submission_id' => $submission->id,
                    'error' => $e->getMessage(),
                    'attempt' => $this->attempts(),
                    'max_tries' => $this->tries,
                ]);
            }
            throw $e;
        } catch (\Throwable $e) {
            // Connection errors, timeouts, etc. Retryable until final attempt.
            Log::channel('single')->warning('IndexNow submission error', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
            ]);

            if ($this->attempts() >= $this->tries) {
                $submission->update([
                    'status' => 'failed',
                    'response_body' => substr($e->getMessage(), 0, 1000),
                ]);
                $cacheManager->invalidateIndexNow();
            }

            throw $e;
        }
    }
}
