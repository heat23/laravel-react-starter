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
        return Inertia::render('Admin/System', [
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'node_version' => $this->getNodeVersion(),
                'server' => [
                    'os' => PHP_OS_FAMILY.' '.php_uname('r'),
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
                ],
                'database' => [
                    'driver' => config('database.default'),
                    'version' => $this->getDatabaseVersion(),
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
                ->map(fn ($pkg) => ['name' => $pkg['name'], 'version' => $pkg['version']])
                ->values()
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }
}
