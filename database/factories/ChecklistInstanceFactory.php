<?php

namespace Database\Factories;

use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistInstance>
 */
class ChecklistInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => ChecklistTemplate::factory(),
            'auditor_id' => User::factory()->auditor(),
            'status' => 'draft',
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the instance is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the instance is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }
}
