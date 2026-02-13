<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AdminConfigController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Config', [
            'feature_flags' => collect(config('features'))->map(fn ($feature, $key) => [
                'key' => $key,
                'enabled' => $feature['enabled'] ?? false,
                'env_var' => 'FEATURE_'.strtoupper($key),
            ])->values()->toArray(),

            'warnings' => array_values(array_filter([
                config('app.debug') && config('app.env') === 'production'
                    ? ['level' => 'critical', 'message' => 'APP_DEBUG is enabled in production']
                    : null,
                config('app.env') === 'production' && config('session.driver') === 'file'
                    ? ['level' => 'warning', 'message' => 'Using file session driver in production (consider redis/database)']
                    : null,
                config('app.env') === 'production' && config('cache.default') === 'file'
                    ? ['level' => 'warning', 'message' => 'Using file cache driver in production (consider redis)']
                    : null,
                config('app.env') === 'production' && config('queue.default') === 'sync'
                    ? ['level' => 'warning', 'message' => 'Queue is running synchronously in production (configure async driver)']
                    : null,
            ])),

            'environment' => [
                'app_env' => config('app.env'),
                'timezone' => config('app.timezone'),
            ],
        ]);
    }
}
