<?php

namespace Database\Factories;

use App\Models\IndexNowSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<IndexNowSubmission>
 */
class IndexNowSubmissionFactory extends Factory
{
    public function definition(): array
    {
        $urls = ['https://example.test/page-1', 'https://example.test/page-2'];

        return [
            'uuid' => Str::uuid()->toString(),
            'urls' => $urls,
            'url_count' => count($urls),
            'status' => 'pending',
            'attempts' => 0,
            'trigger' => 'manual',
        ];
    }

    public function success(): static
    {
        return $this->state(fn () => [
            'status' => 'success',
            'response_code' => 200,
            'attempts' => 1,
            'submitted_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'response_code' => 422,
            'response_body' => 'Invalid URL format',
            'attempts' => 1,
        ]);
    }
}
