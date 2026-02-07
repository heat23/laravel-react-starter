<?php

namespace Database\Factories;

use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebhookDelivery>
 */
class WebhookDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'webhook_endpoint_id' => WebhookEndpoint::factory(),
            'uuid' => Str::uuid()->toString(),
            'event_type' => 'user.created',
            'payload' => ['user_id' => 1, 'event' => 'user.created'],
            'status' => 'pending',
            'attempts' => 0,
        ];
    }

    public function success(): static
    {
        return $this->state(fn () => [
            'status' => 'success',
            'response_code' => 200,
            'attempts' => 1,
            'delivered_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'response_code' => 500,
            'response_body' => 'Internal Server Error',
            'attempts' => 3,
        ]);
    }
}
