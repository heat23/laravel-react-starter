<?php

beforeEach(function () {
    config([
        'features.indexnow.enabled' => true,
        'indexnow.key' => 'testkeytestkeytestkeytestkey1234',
        'indexnow.host' => 'example.test',
    ]);
});

it('returns the configured key as plain text at /{key}.txt', function () {
    $response = $this->get('/testkeytestkeytestkeytestkey1234.txt');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    expect($response->getContent())->toBe('testkeytestkeytestkeytestkey1234');
});

it('returns 404 when the feature flag is disabled', function () {
    // Route was registered at boot (phpunit.xml has FEATURE_INDEXNOW=true) but
    // the controller guard still kicks in and 404s.
    config(['features.indexnow.enabled' => false]);

    $response = $this->get('/testkeytestkeytestkeytestkey1234.txt');

    $response->assertNotFound();
});

it('returns 404 when the requested key does not match the configured key', function () {
    $response = $this->get('/wrongkeywrongkeywrongkeywrongkey.txt');

    $response->assertNotFound();
});

it('returns 404 when no key is configured', function () {
    config(['indexnow.key' => null]);

    $response = $this->get('/testkeytestkeytestkeytestkey1234.txt');

    $response->assertNotFound();
});

it('route regex rejects keys shorter than 8 characters', function () {
    $response = $this->get('/short.txt');

    $response->assertNotFound();
});

it('route regex rejects keys containing illegal characters', function () {
    $response = $this->get('/bad!key!has!symbols.txt');

    $response->assertNotFound();
});

it('rejects keys shorter than 8 chars even if route regex somehow allowed them', function () {
    // Defense in depth for adversarial finding: if the route regex were ever
    // loosened or bypassed, the controller still refuses any sub-8-char key.
    config(['indexnow.key' => 'short']);

    $response = $this->get('/short.txt');

    $response->assertNotFound();
});

it('rejects whitespace-only keys in config', function () {
    // Adversarial finding: strlen("        ") is 8, which would have passed
    // the naive length check. trim() guard catches whitespace-only configs.
    config(['indexnow.key' => '        ']);

    $response = $this->get('/        .txt');

    $response->assertNotFound();
});
