<?php

/**
 * Architectural guard: billing files must not contain bare tier string literals.
 *
 * After the PlanTier enum migration (Sitting 2), string literals like 'free', 'pro',
 * 'team', or 'enterprise' should never appear standalone in billing code — callers
 * must use PlanTier::Xxx->value so the type system enforces valid values.
 *
 * Whitelist: lines containing config( are excluded (config defaults are external
 * configuration, not magic strings authored in billing logic). Enum case definitions
 * and comments are also excluded.
 *
 * @group arch
 */

use Illuminate\Support\Str;

it('billing files contain no bare tier string literals', function () {
    $relativePaths = [
        'app/Services/BillingService.php',
        'app/Services/PlanLimitService.php',
        'app/Services/AdminBillingStatsService.php',
    ];

    // Discover all controller files under Billing/
    $controllerDir = base_path('app/Http/Controllers/Billing');
    if (is_dir($controllerDir)) {
        foreach (glob("{$controllerDir}/*.php") as $file) {
            $relativePaths[] = str_replace(base_path('/'), '', $file);
        }
    }

    $tierPattern = "/(?<!['\"\w])'(free|pro|pro_team|team|enterprise)'(?!['\"\w])/";

    $violations = [];

    foreach ($relativePaths as $relativePath) {
        $absolutePath = base_path($relativePath);

        if (! file_exists($absolutePath)) {
            continue;
        }

        $lines = file($absolutePath, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $lineNo => $line) {
            $trimmed = ltrim($line);

            // Skip comment lines
            if (
                Str::startsWith($trimmed, '//') ||
                Str::startsWith($trimmed, '#') ||
                Str::startsWith($trimmed, '*')
            ) {
                continue;
            }

            // Strip inline comments before matching
            $codePart = preg_replace('/\s*\/\/.*$/', '', $line) ?? $line;

            // Whitelist: config() calls — defaults are driven by external config, not magic strings
            if (preg_match('/\bconfig\s*\(/', $codePart)) {
                continue;
            }

            // Whitelist: enum case definitions
            if (preg_match('/^\s*case\s+\w+\s*=/', $codePart)) {
                continue;
            }

            if (preg_match($tierPattern, $codePart, $match)) {
                $violations[] = "{$relativePath}:".($lineNo + 1)." — bare '{$match[1]}' literal";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Bare tier string literals found. Use PlanTier::Xxx->value instead:\n".
        implode("\n", $violations)
    );
});
