<?php

test('security headers present on every response', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

test('hsts present only in production', function () {
    $response = $this->get('/');

    // In test/local env, HSTS should NOT be present
    $response->assertHeaderMissing('Strict-Transport-Security');
});

test('hsts present in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $response = $this->get('/');

    $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

test('csp absent when disabled', function () {
    config(['security.csp.enabled' => false]);

    $response = $this->get('/');

    $response->assertHeaderMissing('Content-Security-Policy');
    $response->assertHeaderMissing('Content-Security-Policy-Report-Only');
});

test('csp present when enabled', function () {
    config(['security.csp.enabled' => true]);
    config(['security.csp.report_only' => false]);

    $response = $this->get('/');

    $csp = $response->headers->get('Content-Security-Policy');
    $this->assertNotNull($csp);
    $this->assertStringContainsString("script-src 'self' 'nonce-", $csp);
});

test('csp report only mode', function () {
    config(['security.csp.enabled' => true]);
    config(['security.csp.report_only' => true]);

    $response = $this->get('/');

    $response->assertHeaderMissing('Content-Security-Policy');
    $this->assertNotNull($response->headers->get('Content-Security-Policy-Report-Only'));
});
