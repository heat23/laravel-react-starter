<?php

use Illuminate\Support\Str;

if (! function_exists('extractJsonLdBlocks')) {
    /**
     * Parses all application/ld+json script blocks from HTML and returns decoded arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    function extractJsonLdBlocks(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $matches);

        return array_values(array_filter(
            array_map(fn (string $json) => json_decode($json, true), $matches[1] ?? []),
            fn ($decoded) => is_array($decoded)
        ));
    }
}

if (! function_exists('findByType')) {
    /**
     * Finds the first block with the given @type from a list of ld+json blocks.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<string, mixed>|null
     */
    function findByType(array $blocks, string $type): ?array
    {
        foreach ($blocks as $block) {
            if (($block['@type'] ?? '') === $type) {
                return $block;
            }
        }

        return null;
    }
}

it('SoftwareApplication has numeric Offer.price', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $app = findByType($blocks, 'SoftwareApplication');

    expect($app)->not->toBeNull()
        ->and($app['offers']['price'])->toBeInt();
});

it('SoftwareApplication has @id and publisher cross-reference', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $app = findByType($blocks, 'SoftwareApplication');

    expect($app)->not->toBeNull()
        ->and($app)->toHaveKey('@id')
        ->and($app['@id'])->toContain('#')
        ->and($app['publisher'])->toHaveKey('@id');
});

it('Organization has @id', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $org = findByType($blocks, 'Organization');

    expect($org)->not->toBeNull()
        ->and($org)->toHaveKey('@id')
        ->and(Str::contains($org['@id'], '#'))->toBeTrue();
});

it('Organization.logo is an ImageObject with width and height', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $org = findByType($blocks, 'Organization');

    expect($org)->not->toBeNull()
        ->and($org['logo'])->toBeArray()
        ->and($org['logo']['@type'])->toBe('ImageObject')
        ->and($org['logo'])->toHaveKey('width')
        ->and($org['logo'])->toHaveKey('height');
});

it('WebSite block exists with @id and publisher reference', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $site = findByType($blocks, 'WebSite');

    expect($site)->not->toBeNull()
        ->and($site)->toHaveKey('@id')
        ->and($site['@id'])->toEndWith('#website')
        ->and($site['publisher'])->toHaveKey('@id');
});

it('WebPage block exists with @id, isPartOf, and publisher on homepage', function () {
    $baseUrl = rtrim(config('app.url'), '/');
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $page = findByType($blocks, 'WebPage');

    expect($page)->not->toBeNull()
        ->and($page)->toHaveKey('@id')
        ->and($page['isPartOf']['@id'])->toBe($baseUrl.'#website')
        ->and($page['publisher']['@id'])->toBe($baseUrl.'#organization');
});

it('WebPage @id includes #webpage fragment', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $page = findByType($blocks, 'WebPage');

    expect($page)->not->toBeNull()
        ->and($page['@id'])->toContain('#webpage');
});

it('entities are cross-referenced: Organization @id matches SoftwareApplication publisher', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $org = findByType($blocks, 'Organization');
    $app = findByType($blocks, 'SoftwareApplication');

    expect($org['@id'])->toBe($app['publisher']['@id']);
});

it('entities are cross-referenced: Organization @id matches WebSite publisher', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $org = findByType($blocks, 'Organization');
    $site = findByType($blocks, 'WebSite');

    expect($org['@id'])->toBe($site['publisher']['@id']);
});

it('entities are cross-referenced: WebSite @id matches WebPage isPartOf', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);
    $site = findByType($blocks, 'WebSite');
    $page = findByType($blocks, 'WebPage');

    expect($site['@id'])->toBe($page['isPartOf']['@id']);
});

it('JSON-LD is valid on features page', function () {
    $content = $this->get('/features')->getContent();
    $blocks = extractJsonLdBlocks($content);

    expect($blocks)->not->toBeEmpty();
    $org = findByType($blocks, 'Organization');
    expect($org)->not->toBeNull()->and($org)->toHaveKey('@id');

    $webPage = findByType($blocks, 'WebPage');
    expect($webPage)->not->toBeNull()->and($webPage)->toHaveKey('@id');
});

it('JSON-LD is valid on compare page', function () {
    $content = $this->get('/compare')->getContent();
    $blocks = extractJsonLdBlocks($content);

    $webPage = findByType($blocks, 'WebPage');
    expect($webPage)->not->toBeNull()
        ->and($webPage)->toHaveKey('@id')
        ->and($webPage['@id'])->toContain('#webpage');
});

it('all JSON-LD blocks have @context set to schema.org', function () {
    $content = $this->get('/')->getContent();
    $blocks = extractJsonLdBlocks($content);

    foreach ($blocks as $block) {
        expect($block['@context'])->toBe('https://schema.org');
    }
});
