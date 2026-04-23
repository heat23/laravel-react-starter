<?php

use App\Webhooks\UrlPolicy;

dataset('blocked_internal_urls', [
    'loopback 127.0.0.1' => ['http://127.0.0.1/hook'],
    'loopback 127.0.0.2' => ['http://127.0.0.2/hook'],
    'RFC-1918 class A' => ['http://10.0.0.1/hook'],
    'RFC-1918 class B' => ['http://172.16.0.1/hook'],
    'RFC-1918 class B upper' => ['http://172.31.255.255/hook'],
    'RFC-1918 class C' => ['http://192.168.1.1/hook'],
    'IMDS 169.254.169.254' => ['http://169.254.169.254/latest/meta-data/'],
    'CGNAT 100.64.x.x' => ['http://100.100.100.100/hook'],
]);

it('blocks internal/private IP addresses', function (string $url) {
    $error = (new UrlPolicy)->check($url);

    expect($error)->not->toBeNull("Expected URL {$url} to be blocked");
    expect($error)->toContain('blocked range');
})->with('blocked_internal_urls');

it('blocks non-resolving hostnames', function () {
    $error = (new UrlPolicy)->check('https://this-host-does-not-exist-ssrf-xyz.invalid/hook');

    expect($error)->toBe('Hostname does not resolve');
});

it('blocks non-http and non-https schemes', function () {
    expect((new UrlPolicy)->check('ftp://example.com/hook'))->toBe('Scheme must be http or https');
    // file:/// has no host component — caught as invalid URL (equally safe, still blocked)
    expect((new UrlPolicy)->check('file:///etc/passwd'))->not->toBeNull();
    expect((new UrlPolicy)->check('gopher://evil.example.com/'))->toBe('Scheme must be http or https');
});

it('rejects malformed urls', function () {
    expect((new UrlPolicy)->check('not-a-url'))->toBe('Invalid URL');
});

it('blocks well-known sensitive ports outside local and testing environments', function () {
    app()->detectEnvironment(fn () => 'production');

    try {
        foreach ([22, 25, 3306, 5432, 6379, 9200, 11211] as $port) {
            $error = (new UrlPolicy)->check("https://example.com:{$port}/hook");

            expect($error)->not->toBeNull("Port {$port} should be blocked in production");
            expect($error)->toContain((string) $port);
        }
    } finally {
        app()->detectEnvironment(fn () => 'testing');
    }
});

it('does not block sensitive ports in testing environment', function () {
    // In the testing env ports are allowed — the test suite itself uses local services.
    $error = (new UrlPolicy)->check('http://127.0.0.1:6379/hook');

    // Port should NOT be the blocking reason (the IP range is).
    expect($error)->not->toContain('Port 6379');
});

it('returns null for publicly routable https urls when dns resolves', function () {
    $policy = new UrlPolicy;
    $error = $policy->check('https://example.com/webhook');

    // In offline CI gethostbynamel may fail; only assert no range-block error.
    if ($error !== null) {
        expect($error)->not->toContain('blocked range');
    } else {
        expect($error)->toBeNull();
        expect($policy->resolvedIps())->not->toBeEmpty();
    }
});
