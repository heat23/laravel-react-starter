<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminSystemController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('App/Admin/System', [
            'system' => [
                'php_version' => $this->redactVersion(PHP_VERSION),
                'laravel_version' => $this->redactVersion(app()->version()),
                'node_version' => $this->redactNullableVersion($this->getNodeVersion()),
                'server' => [
                    'os' => PHP_OS_FAMILY,
                    'server_software' => $this->redactServerSoftware($_SERVER['SERVER_SOFTWARE'] ?? 'CLI'),
                ],
                'database' => [
                    'driver' => config('database.default'),
                    'version' => $this->redactNullableVersion($this->getDatabaseVersion()),
                ],
                'queue' => [
                    'driver' => config('queue.default'),
                    'pending_jobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : null,
                    'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null,
                ],
                'packages' => $this->getKeyPackages(),
            ],
        ]);
    }

    /**
     * Reduce a version string to major.minor.x, stripping patch/hash/pre-release tags.
     * Exposed externally on /admin/system; exact patch versions leak CVE-matchable build info.
     */
    private function redactVersion(string $version): string
    {
        if (preg_match('/^v?(\d+)\.(\d+)/', $version, $m)) {
            return "{$m[1]}.{$m[2]}.x";
        }

        return 'unknown';
    }

    /** Strip the version token from "nginx/1.24.0" → "nginx", "Apache/2.4.x" → "Apache". */
    private function redactServerSoftware(string $software): string
    {
        return preg_replace('/\/[\d.]+.*$/', '', $software);
    }

    private function redactNullableVersion(?string $version): ?string
    {
        return $version === null ? null : $this->redactVersion($version);
    }

    private function getNodeVersion(): ?string
    {
        try {
            $result = Process::run('node --version');

            return $result->successful() ? trim($result->output()) ?: null : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function getDatabaseVersion(): ?string
    {
        try {
            return DB::selectOne('SELECT VERSION() as version')?->version;
        } catch (\Throwable) {
            return null;
        }
    }

    private function getKeyPackages(): array
    {
        try {
            $lockFile = base_path('composer.lock');
            if (! file_exists($lockFile)) {
                return [];
            }

            $lock = json_decode(file_get_contents($lockFile), true);
            $keyPackages = [
                'laravel/framework',
                'laravel/cashier',
                'laravel/sanctum',
                'laravel/socialite',
                'inertiajs/inertia-laravel',
                'laragear/two-factor',
            ];

            return collect($lock['packages'] ?? [])
                ->filter(fn ($pkg) => in_array($pkg['name'], $keyPackages))
                ->map(fn ($pkg) => ['name' => $pkg['name'], 'version' => $this->redactVersion($pkg['version'])])
                ->values()
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }
}
