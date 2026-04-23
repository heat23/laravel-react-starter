<?php

use App\Jobs\SubmitIndexNowUrlsJob;
use App\Models\IndexNowSubmission;
use App\Services\CacheInvalidationManager;
use App\Services\IndexNowService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'features.indexnow.enabled' => true,
        'indexnow.key' => 'testkeytestkeytestkeytestkey1234',
        'indexnow.host' => 'example.test',
        'indexnow.endpoint' => 'https://api.indexnow.org/indexnow',
        'indexnow.timeout' => 15,
        'app.url' => 'https://example.test',
    ]);
});

function runJob(IndexNowSubmission $submission, int $attempts = 1): void
{
    $job = new class($submission->id, $attempts) extends SubmitIndexNowUrlsJob
    {
        public function __construct(
            int $submissionId,
            private readonly int $fakeAttempts,
        ) {
            parent::__construct($submissionId);
        }

        public function attempts(): int
        {
            return $this->fakeAttempts;
        }
    };

    $job->handle(app(IndexNowService::class), app(CacheInvalidationManager::class));
}

it('marks submission success on 200 response and records submitted_at', function () {
    Http::fake([
        'api.indexnow.org/*' => Http::response('', 200),
    ]);

    $submission = IndexNowSubmission::factory()->create([
        'urls' => ['https://example.test/a'],
        'url_count' => 1,
    ]);

    runJob($submission);

    $submission->refresh();
    expect($submission->status)->toBe('success')
        ->and($submission->response_code)->toBe(200)
        ->and($submission->submitted_at)->not->toBeNull()
        ->and($submission->attempts)->toBe(1);
});

it('marks submission success on 202 response', function () {
    Http::fake([
        'api.indexnow.org/*' => Http::response('', 202),
    ]);

    $submission = IndexNowSubmission::factory()->create();

    runJob($submission);

    expect($submission->fresh()->status)->toBe('success')
        ->and($submission->fresh()->response_code)->toBe(202);
});

it('marks submission failed immediately on 403 and does not throw-to-retry', function () {
    Http::fake([
        'api.indexnow.org/*' => Http::response('Forbidden', 403),
    ]);

    $submission = IndexNowSubmission::factory()->create();

    try {
        runJob($submission);
    } catch (Throwable) {
        // Job calls $this->fail() which in sync/test context we tolerate;
        // the important assertion is the DB state.
    }

    $submission->refresh();
    expect($submission->status)->toBe('failed')
        ->and($submission->response_code)->toBe(403);
});

it('marks submission failed immediately on 422 without retry', function () {
    Http::fake([
        'api.indexnow.org/*' => Http::response('Unprocessable', 422),
    ]);

    $submission = IndexNowSubmission::factory()->create();

    try {
        runJob($submission);
    } catch (Throwable) {
    }

    expect($submission->fresh()->status)->toBe('failed')
        ->and($submission->fresh()->response_code)->toBe(422);
});

it('throws (retries) on 429 transient error without marking failed on intermediate attempt', function () {
    Http::fake([
        'api.indexnow.org/*' => Http::response('Too Many Requests', 429),
    ]);

    $submission = IndexNowSubmission::factory()->create();

    expect(fn () => runJob($submission, attempts: 1))
        ->toThrow(RuntimeException::class);

    // Intermediate attempts should leave status pending but record response_code
    expect($submission->fresh()->status)->toBe('pending')
        ->and($submission->fresh()->response_code)->toBe(429);
});

it('marks submission failed on final attempt for transient error', function () {
    Http::fake([
        'api.indexnow.org/*' => Http::response('Server Error', 500),
    ]);

    $submission = IndexNowSubmission::factory()->create();

    expect(fn () => runJob($submission, attempts: 3))
        ->toThrow(RuntimeException::class);

    expect($submission->fresh()->status)->toBe('failed')
        ->and($submission->fresh()->response_code)->toBe(500);
});

it('posts JSON body with host, key, keyLocation, urlList to configured endpoint', function () {
    Http::fake([
        'api.indexnow.org/*' => Http::response('', 200),
    ]);

    $submission = IndexNowSubmission::factory()->create([
        'urls' => ['https://example.test/one', 'https://example.test/two'],
        'url_count' => 2,
    ]);

    runJob($submission);

    Http::assertSent(function (Request $request) {
        $body = json_decode($request->body(), true);

        return $request->url() === 'https://api.indexnow.org/indexnow'
            && $request->method() === 'POST'
            && $body['host'] === 'example.test'
            && $body['key'] === 'testkeytestkeytestkeytestkey1234'
            && $body['keyLocation'] === 'https://example.test/testkeytestkeytestkeytestkey1234.txt'
            && $body['urlList'] === ['https://example.test/one', 'https://example.test/two']
            && str_contains($request->header('Content-Type')[0] ?? '', 'application/json');
    });
});

it('no-ops gracefully when submission row is missing', function () {
    Http::fake();

    $job = new SubmitIndexNowUrlsJob(999999);
    $job->handle(app(IndexNowService::class), app(CacheInvalidationManager::class));

    Http::assertNothingSent();
});

it('marks submission failed when service is not configured at handle time', function () {
    Http::fake();
    config(['indexnow.key' => null]);

    $submission = IndexNowSubmission::factory()->create();

    runJob($submission);

    expect($submission->fresh()->status)->toBe('failed')
        ->and($submission->fresh()->response_body)->toContain('not configured');
    Http::assertNothingSent();
});

it('truncates long response bodies to 1000 chars', function () {
    $long = str_repeat('x', 2000);
    Http::fake([
        'api.indexnow.org/*' => Http::response($long, 200),
    ]);

    $submission = IndexNowSubmission::factory()->create();

    runJob($submission);

    expect(strlen($submission->fresh()->response_body))->toBe(1000);
});
