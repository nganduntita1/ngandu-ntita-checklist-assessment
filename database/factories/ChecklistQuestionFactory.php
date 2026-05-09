<?php

namespace Database\Factories;

use App\Models\ChecklistTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistQuestion>
 */
class ChecklistQuestionFactory extends Factory
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
            'question_text' => fake()->sentence(fake()->numberBetween(6, 12)) . '?',
            'answer_type' => fake()->randomElement(['text', 'textarea', 'boolean', 'number']),
            'required' => fake()->boolean(75),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Set answer_type to text.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_type' => 'text',
        ]);
    }

    /**
     * Set answer_type to textarea.
     */
    public function textarea(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_type' => 'textarea',
        ]);
    }

    /**
     * Set answer_type to boolean.
     */
    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_type' => 'boolean',
        ]);
    }

    /**
     * Set answer_type to number.
     */
    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_type' => 'number',
        ]);
    }
}
