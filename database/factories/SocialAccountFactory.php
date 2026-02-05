<?php

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement(['google', 'github']),
            'provider_id' => $this->faker->uuid(),
            'token' => $this->faker->sha256(),
            'refresh_token' => $this->faker->sha256(),
            'token_expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
        ];
    }

    /**
     * Indicate that the social account is for Google.
     */
    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'google',
            'provider_id' => 'google-' . $this->faker->uuid(),
        ]);
    }

    /**
     * Indicate that the social account is for GitHub.
     */
    public function github(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'github',
            'provider_id' => 'github-' . $this->faker->numberBetween(1000000, 9999999),
        ]);
    }

    /**
     * Indicate that the token is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 hour'),
        ]);
    }

    /**
     * Indicate that the token has no expiration (never expires).
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => null,
        ]);
    }

    /**
     * Indicate that there is no refresh token.
     */
    public function withoutRefreshToken(): static
    {
        return $this->state(fn (array $attributes) => [
            'refresh_token' => null,
        ]);
    }
}
