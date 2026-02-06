<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditLog> */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'event' => fake()->randomElement(['auth.login', 'auth.logout', 'auth.register']),
            'user_id' => User::factory(),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'metadata' => ['email' => fake()->email()],
        ];
    }
}
