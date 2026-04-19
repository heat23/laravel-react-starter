<?php

namespace App\Facades;

use App\Models\IndexNowSubmission;
use App\Services\IndexNowService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static IndexNowSubmission|null submit(array $urls, ?string $trigger = null)
 * @method static IndexNowSubmission|null submitUrl(string $url, ?string $trigger = null)
 * @method static bool isConfigured()
 * @method static string keyLocation()
 *
 * @see IndexNowService
 */
class IndexNow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IndexNowService::class;
    }
}
