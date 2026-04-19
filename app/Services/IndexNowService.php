<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Jobs\SubmitIndexNowUrlsJob;
use App\Models\IndexNowSubmission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * IndexNow submission service.
 *
 * Downstream apps submit URLs via this service when content changes.
 * The service validates, dedupes, chunks, persists, and queues HTTP
 * delivery. The actual POST to the IndexNow endpoint happens in
 * {@see SubmitIndexNowUrlsJob} with retry/backoff.
 */
class IndexNowService
{
    /**
     * Submit a batch of URLs to IndexNow.
     *
     * Returns the IndexNowSubmission row for the first chunk queued, or
     * null when the service is not configured or no eligible URLs remain
     * after validation / debounce filtering. When URLs exceed the
     * per-submission cap, multiple rows/jobs are created and the first
     * row is returned for caller observability.
     *
     * @param  array<int, string>  $urls
     */
    public function submit(array $urls, ?string $trigger = null): ?IndexNowSubmission
    {
        if (! $this->isConfigured()) {
            Log::debug('IndexNow submit skipped: service not configured', [
                'feature_enabled' => (bool) config('features.indexnow.enabled'),
                'key_present' => (bool) config('indexnow.key'),
                'host_present' => (bool) config('indexnow.host'),
            ]);

            return null;
        }

        $eligible = $this->filterEligibleUrls($urls);

        if ($eligible === []) {
            return null;
        }

        $max = (int) config('features.indexnow.max_urls_per_submission', 10000);
        $chunks = array_chunk($eligible, $max);

        $first = null;
        foreach ($chunks as $chunk) {
            $submission = IndexNowSubmission::create([
                'uuid' => (string) Str::uuid(),
                'urls' => $chunk,
                'url_count' => count($chunk),
                'status' => 'pending',
                'attempts' => 0,
                'trigger' => $trigger,
            ]);

            SubmitIndexNowUrlsJob::dispatch($submission->id)
                ->onQueue(config('indexnow.queue', 'default'));

            $this->markDebounced($chunk);

            $first ??= $submission;
        }

        Cache::forget(AdminCacheKey::INDEXNOW_STATS->value);

        return $first;
    }

    /**
     * Convenience wrapper for submitting a single URL.
     */
    public function submitUrl(string $url, ?string $trigger = null): ?IndexNowSubmission
    {
        return $this->submit([$url], $trigger);
    }

    /**
     * Whether the service has everything it needs to make a submission.
     */
    public function isConfigured(): bool
    {
        return (bool) config('features.indexnow.enabled', false)
            && ! empty(config('indexnow.key'))
            && ! empty(config('indexnow.host'));
    }

    /**
     * The absolute URL where IndexNow can verify ownership of the host.
     */
    public function keyLocation(): string
    {
        $override = config('indexnow.key_location');
        if (! empty($override)) {
            return (string) $override;
        }

        $base = rtrim((string) config('app.url'), '/');
        $key = (string) config('indexnow.key');

        return $base.'/'.$key.'.txt';
    }

    /**
     * Filter submitted URLs down to ones that should actually be pinged:
     * absolute http/https URLs on the configured host, not recently
     * submitted (debounce), and deduplicated within this batch.
     *
     * @param  array<int, string>  $urls
     * @return array<int, string>
     */
    private function filterEligibleUrls(array $urls): array
    {
        $host = strtolower((string) config('indexnow.host'));
        $seen = [];
        $eligible = [];

        foreach ($urls as $url) {
            if ($url === '') {
                continue;
            }

            $parts = parse_url($url);
            if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
                Log::debug('IndexNow: skipping malformed URL', ['url' => $url]);

                continue;
            }

            if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
                continue;
            }

            if (strtolower($parts['host']) !== $host) {
                Log::info('IndexNow: rejecting cross-host URL', [
                    'url' => $url,
                    'expected_host' => $host,
                ]);

                continue;
            }

            // Strip fragments — search engines don't crawl them and two URLs
            // differing only in `#anchor` point to the same page.
            $normalized = $this->stripFragment($url, $parts);

            if (isset($seen[$normalized])) {
                continue;
            }
            $seen[$normalized] = true;

            if ($this->isDebounced($normalized)) {
                continue;
            }

            $eligible[] = $normalized;
        }

        return $eligible;
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    private function stripFragment(string $url, array $parts): string
    {
        if (! isset($parts['fragment'])) {
            return $url;
        }

        // Fast path: trim everything from the first '#'. Works because
        // URL fragments are always the last component per RFC 3986.
        $hashPos = strpos($url, '#');

        return $hashPos === false ? $url : substr($url, 0, $hashPos);
    }

    private function isDebounced(string $url): bool
    {
        $minutes = (int) config('features.indexnow.debounce_minutes', 10);
        if ($minutes <= 0) {
            return false;
        }

        return Cache::has($this->debounceKey($url));
    }

    /**
     * @param  array<int, string>  $urls
     */
    private function markDebounced(array $urls): void
    {
        $minutes = (int) config('features.indexnow.debounce_minutes', 10);
        if ($minutes <= 0) {
            return;
        }

        foreach ($urls as $url) {
            Cache::put($this->debounceKey($url), true, now()->addMinutes($minutes));
        }
    }

    private function debounceKey(string $url): string
    {
        return 'indexnow:recent:'.sha1($url);
    }
}
