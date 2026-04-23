<?php

/**
 * Architectural guards for the per-metric billing stats calculator classes
 * introduced in Sitting 5 of the refactor plan.
 *
 * Each calculator must:
 *  - Live in the App\Services\Billing\Stats namespace
 *  - Have a class name ending in "Calculator" or "QueryBuilder"
 *  - Never import the Cache facade (caching is the facade's responsibility)
 *
 * @group arch
 */
it('all classes in Billing/Stats namespace have Calculator or QueryBuilder suffix', function () {
    $statsDir = realpath(__DIR__.'/../../app/Services/Billing/Stats')
        ?: base_path('app/Services/Billing/Stats');
    $files = glob("{$statsDir}/*.php") ?: [];

    expect($files)->not->toBeEmpty("No classes found in {$statsDir}/");

    $violations = [];

    foreach ($files as $file) {
        $className = basename($file, '.php');
        if (! str_ends_with($className, 'Calculator') && ! str_ends_with($className, 'QueryBuilder')) {
            $violations[] = $className;
        }
    }

    expect($violations)->toBeEmpty(
        'Classes in App\\Services\\Billing\\Stats must end with "Calculator" or "QueryBuilder": '.
        implode(', ', $violations)
    );
});

it('calculator classes do not import Cache facade', function () {
    $statsDir = realpath(__DIR__.'/../../app/Services/Billing/Stats')
        ?: base_path('app/Services/Billing/Stats');
    $files = glob("{$statsDir}/*.php") ?: [];

    expect($files)->not->toBeEmpty("No classes found in {$statsDir}/");

    $violations = [];

    foreach ($files as $file) {
        $content = file_get_contents($file);
        if (str_contains($content, 'Illuminate\Support\Facades\Cache')) {
            $violations[] = basename($file);
        }
    }

    expect($violations)->toBeEmpty(
        'Calculator classes must not import Cache — caching belongs in AdminBillingStatsService facade: '.
        implode(', ', $violations)
    );
});

it('admin billing stats facade delegates to calculators rather than querying DB directly', function () {
    $facadePath = realpath(__DIR__.'/../../app/Services/AdminBillingStatsService.php')
        ?: base_path('app/Services/AdminBillingStatsService.php');
    $content = file_get_contents($facadePath);

    // The facade must not contain direct DB:: calls (all queries live in calculators)
    expect($content)->not->toContain('DB::table(', 'AdminBillingStatsService should delegate to calculators, not query DB directly');
});
