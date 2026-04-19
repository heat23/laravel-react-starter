<?php

use App\Jobs\SubmitIndexNowUrlsJob;
use App\Models\IndexNowSubmission;
use App\Services\IndexNowService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config([
        'features.indexnow.enabled' => true,
        'features.indexnow.max_urls_per_submission' => 10000,
        'features.indexnow.debounce_minutes' => 10,
        'indexnow.key' => 'testkeytestkeytestkeytestkey1234',
        'indexnow.host' => 'example.test',
        'indexnow.endpoint' => 'https://api.indexnow.org/indexnow',
        'indexnow.queue' => 'default',
        'app.url' => 'https://example.test',
    ]);
});

it('is configured when feature enabled + key + host set', function () {
    expect(app(IndexNowService::class)->isConfigured())->toBeTrue();
});

it('is not configured when feature flag is off', function () {
    config(['features.indexnow.enabled' => false]);

    expect(app(IndexNowService::class)->isConfigured())->toBeFalse();
});

it('is not configured when API key is missing', function () {
    config(['indexnow.key' => null]);

    expect(app(IndexNowService::class)->isConfigured())->toBeFalse();
});

it('is not configured when host is missing', function () {
    config(['indexnow.host' => null]);

    expect(app(IndexNowService::class)->isConfigured())->toBeFalse();
});

it('computes key location from app url and key by default', function () {
    expect(app(IndexNowService::class)->keyLocation())
        ->toBe('https://example.test/testkeytestkeytestkeytestkey1234.txt');
});

it('honours explicit key_location override', function () {
    config(['indexnow.key_location' => 'https://cdn.example.test/verify.txt']);

    expect(app(IndexNowService::class)->keyLocation())
        ->toBe('https://cdn.example.test/verify.txt');
});

it('no-ops and returns null when service not configured', function () {
    Queue::fake();
    config(['features.indexnow.enabled' => false]);

    $result = app(IndexNowService::class)->submit(['https://example.test/foo']);

    expect($result)->toBeNull()
        ->and(IndexNowSubmission::count())->toBe(0);
    Queue::assertNotPushed(SubmitIndexNowUrlsJob::class);
});

it('creates a submission and queues a job for a valid URL', function () {
    Queue::fake();

    $submission = app(IndexNowService::class)->submit(
        ['https://example.test/new-post'],
        'test-trigger',
    );

    expect($submission)->not->toBeNull()
        ->and($submission->status)->toBe('pending')
        ->and($submission->url_count)->toBe(1)
        ->and($submission->urls)->toBe(['https://example.test/new-post'])
        ->and($submission->trigger)->toBe('test-trigger');

    Queue::assertPushed(SubmitIndexNowUrlsJob::class, 1);
});

it('rejects cross-host URLs', function () {
    Queue::fake();

    $result = app(IndexNowService::class)->submit([
        'https://other.test/page',
        'https://example.test/keeper',
    ]);

    expect($result)->not->toBeNull()
        ->and($result->urls)->toBe(['https://example.test/keeper'])
        ->and($result->url_count)->toBe(1);
});

it('returns null when no URLs are eligible after filtering', function () {
    Queue::fake();

    $result = app(IndexNowService::class)->submit([
        'https://other.test/page',
        'ftp://example.test/page',
        'not-a-url',
    ]);

    expect($result)->toBeNull()
        ->and(IndexNowSubmission::count())->toBe(0);
    Queue::assertNotPushed(SubmitIndexNowUrlsJob::class);
});

it('dedupes URLs within a single batch', function () {
    Queue::fake();

    $submission = app(IndexNowService::class)->submit([
        'https://example.test/a',
        'https://example.test/a',
        'https://example.test/b',
    ]);

    expect($submission->url_count)->toBe(2)
        ->and($submission->urls)->toBe([
            'https://example.test/a',
            'https://example.test/b',
        ]);
});

it('skips URLs pinged within the debounce window on subsequent submissions', function () {
    Queue::fake();
    Cache::flush();
    $service = app(IndexNowService::class);

    $first = $service->submit(['https://example.test/x']);
    expect($first)->not->toBeNull();

    $second = $service->submit(['https://example.test/x']);
    expect($second)->toBeNull();

    Queue::assertPushed(SubmitIndexNowUrlsJob::class, 1);
});

it('disables debounce when minutes = 0', function () {
    Queue::fake();
    Cache::flush();
    config(['features.indexnow.debounce_minutes' => 0]);
    $service = app(IndexNowService::class);

    $service->submit(['https://example.test/y']);
    $second = $service->submit(['https://example.test/y']);

    expect($second)->not->toBeNull();
    Queue::assertPushed(SubmitIndexNowUrlsJob::class, 2);
});

it('chunks URLs over the max per-submission cap', function () {
    Queue::fake();
    config(['features.indexnow.max_urls_per_submission' => 2]);
    config(['features.indexnow.debounce_minutes' => 0]);

    $urls = [
        'https://example.test/a',
        'https://example.test/b',
        'https://example.test/c',
        'https://example.test/d',
        'https://example.test/e',
    ];

    $first = app(IndexNowService::class)->submit($urls);

    expect(IndexNowSubmission::count())->toBe(3)
        ->and($first->url_count)->toBe(2);
    Queue::assertPushed(SubmitIndexNowUrlsJob::class, 3);
});

it('submitUrl dispatches a single URL', function () {
    Queue::fake();

    $submission = app(IndexNowService::class)->submitUrl(
        'https://example.test/single',
        'convenience',
    );

    expect($submission)->not->toBeNull()
        ->and($submission->url_count)->toBe(1)
        ->and($submission->trigger)->toBe('convenience');
});

it('rejects http+https mismatches from cross-host check by host only', function () {
    // IndexNow supports both http and https; host match is the only gate.
    Queue::fake();
    config(['indexnow.host' => 'example.test']);

    $result = app(IndexNowService::class)->submit([
        'http://example.test/insecure',
        'https://example.test/secure',
    ]);

    expect($result->url_count)->toBe(2);
});

it('strips URL fragments so identical pages dedupe correctly', function () {
    // Adversarial review: two URLs differing only in #anchor are the same
    // page to any search engine — dedupe them.
    Queue::fake();

    $result = app(IndexNowService::class)->submit([
        'https://example.test/doc',
        'https://example.test/doc#section-1',
        'https://example.test/doc#section-2',
    ]);

    expect($result->url_count)->toBe(1)
        ->and($result->urls)->toBe(['https://example.test/doc']);
});

it('preserves query string — distinct canonicals are not conflated', function () {
    // Fragment-only normalization: ?variant=a and ?variant=b stay distinct
    // because in some apps those are genuinely different pages/canonicals.
    Queue::fake();

    $result = app(IndexNowService::class)->submit([
        'https://example.test/p?variant=a',
        'https://example.test/p?variant=b',
    ]);

    expect($result->url_count)->toBe(2);
});
