<?php

/**
 * Guard against reintroduction of the dead `PrimaryButton` component.
 * See refactor plan 2026-04-23 sitting-8 (finding M7).
 */
it('has no references to the removed PrimaryButton component', function () {
    $root = base_path('resources/js');

    expect(is_dir($root))->toBeTrue();

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $offenders = [];
    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        if (! in_array($file->getExtension(), ['ts', 'tsx', 'js', 'jsx'], true)) {
            continue;
        }
        $contents = file_get_contents($file->getPathname());
        if ($contents !== false && str_contains($contents, 'PrimaryButton')) {
            $offenders[] = $file->getPathname();
        }
    }

    expect($offenders)->toBe(
        [],
        'PrimaryButton was deleted in the 2026-04-23 refactor. '
        .'Use the shared Button primitive in resources/js/Components/ui/ instead.'
    );
});
