<?php

namespace Database\Factories;

use App\Models\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'type' => $this->faker->randomElement(['bug', 'feature', 'general']),
            'message' => $this->faker->paragraph(),
            'status' => 'open',
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'admin_notes' => null,
            'resolved_at' => null,
        ];
    }
}
