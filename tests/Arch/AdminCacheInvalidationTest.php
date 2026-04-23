<?php

/**
 * Architectural guard: Cache::forget(AdminCacheKey::*) must only appear in CacheInvalidationManager.
 *
 * All cache invalidation for admin keys must go through CacheInvalidationManager semantic methods.
 * Direct Cache::forget(AdminCacheKey::*) calls in other files are a violation — they scatter
 * invalidation logic and risk missing related keys that the manager groups together.
 *
 * @group arch
 */
it('Cache::forget(AdminCacheKey) calls only appear in CacheInvalidationManager', function () {
    $pattern = 'Cache::forget(';
    $adminKeyPattern = 'AdminCacheKey';
    $allowedFile = 'app/Services/CacheInvalidationManager.php';

    $violations = [];

    $phpFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('app'))
    );

    foreach ($phpFiles as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $relativePath = str_replace(base_path('/'), '', $file->getPathname());

        if ($relativePath === $allowedFile) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        $lines = explode("\n", $contents);

        foreach ($lines as $lineNo => $line) {
            if (str_contains($line, $pattern) && str_contains($line, $adminKeyPattern)) {
                $violations[] = "{$relativePath}:".($lineNo + 1);
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Direct Cache::forget(AdminCacheKey::*) calls found outside CacheInvalidationManager.\n".
        "Use a semantic method on CacheInvalidationManager instead:\n".
        implode("\n", $violations)
    );
});
