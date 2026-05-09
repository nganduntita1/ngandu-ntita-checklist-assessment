<?php

namespace Database\Factories;

use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistAnswer>
 */
class ChecklistAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instance_id' => ChecklistInstance::factory(),
            'question_id' => ChecklistQuestion::factory(),
            'answer_value' => fake()->sentence(),
        ];
    }

    /**
     * Set a text answer value.
     */
    public function withTextAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_value' => fake()->words(fake()->numberBetween(2, 6), true),
        ]);
    }

    /**
     * Set a textarea answer value.
     */
    public function withTextareaAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_value' => fake()->paragraph(),
        ]);
    }

    /**
     * Set a boolean answer value.
     */
    public function withBooleanAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_value' => fake()->randomElement(['true', 'false']),
        ]);
    }

    /**
     * Set a number answer value.
     */
    public function withNumberAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_value' => (string) fake()->numberBetween(0, 1000),
        ]);
    }

    /**
     * Set a null (unanswered) answer value.
     */
    public function unanswered(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_value' => null,
        ]);
    }
}
