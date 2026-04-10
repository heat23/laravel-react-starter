<?php

use Sentry\Laravel\Integration;
use Sentry\Laravel\ServiceProvider;

it('has sentry-laravel package installed', function (): void {
    expect(class_exists(Integration::class))->toBeTrue();
    expect(class_exists(ServiceProvider::class))->toBeTrue();
});

it('loads sentry config with expected keys', function (): void {
    $config = config('sentry');

    expect($config)->not->toBeNull('config/sentry.php is missing — run: php artisan vendor:publish --provider="Sentry\\Laravel\\ServiceProvider"')
        ->and($config)->toBeArray()
        ->and($config)->toHaveKeys([
            'dsn',
            'release',
            'environment',
            'breadcrumbs',
            'traces_sample_rate',
            'profiles_sample_rate',
            'send_default_pii',
        ]);
});

it('sentry environment defaults to app env', function (): void {
    expect(config('sentry.environment'))->toBe(config('app.env'));
});

it('sentry service provider is registered', function (): void {
    $loadedProviders = app()->getLoadedProviders();

    expect($loadedProviders)->toHaveKey(ServiceProvider::class);
});

it('Integration::handles() is wired in bootstrap/app.php exception handler', function (): void {
    $source = file_get_contents(base_path('bootstrap/app.php'));

    expect($source)->toContain('use Sentry\\Laravel\\Integration;');
    expect($source)->toContain('Integration::handles($exceptions)');
});
