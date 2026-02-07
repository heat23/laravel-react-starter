<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebhookEndpoint>
 */
class WebhookEndpointFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'url' => fake()->url(),
            'events' => ['user.created', 'user.updated'],
            'secret' => Str::random(32),
            'description' => fake()->sentence(),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
