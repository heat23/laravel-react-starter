<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test case.
     *
     * Ensures clean state by clearing all Laravel caches that could
     * interfere with tests (route cache, config cache, etc.).
     */
    protected function setUp(): void
    {
        // Clear Laravel bootstrap cache before parent::setUp()
        // This prevents memory exhaustion from stale route cache
        $this->clearLaravelCache();

        parent::setUp();

        // Prevent Vite manifest lookups during testing
        // Tests should not require frontend assets to be built
        $this->withoutVite();

        // Laravel 12: Explicitly disable CSRF for testing
        // Session driver 'array' doesn't persist tokens across requests
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * Clear Laravel bootstrap cache files.
     *
     * Prevents stale cached routes/config from causing test failures
     * or memory exhaustion in parallel test execution.
     */
    private function clearLaravelCache(): void
    {
        $bootstrapCache = __DIR__.'/../bootstrap/cache';

        $cacheFiles = [
            $bootstrapCache.'/config.php',
            $bootstrapCache.'/routes-v7.php',
            $bootstrapCache.'/events.php',
        ];

        foreach ($cacheFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }
}
