<?php

namespace Database\Factories;

use App\Models\NpsResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NpsResponse>
 */
class NpsResponseFactory extends Factory
{
    protected $model = NpsResponse::class;

    public function definition(): array
    {
        $score = fake()->numberBetween(0, 10);

        return [
            'user_id' => User::factory(),
            'score' => $score,
            'comment' => fake()->optional(0.6)->sentence(),
            'survey_trigger' => fake()->randomElement(['post_onboarding', 'quarterly']),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function promoter(): static
    {
        return $this->state(['score' => fake()->numberBetween(9, 10)]);
    }

    public function passive(): static
    {
        return $this->state(['score' => fake()->numberBetween(7, 8)]);
    }

    public function detractor(): static
    {
        return $this->state(['score' => fake()->numberBetween(0, 6)]);
    }
}
