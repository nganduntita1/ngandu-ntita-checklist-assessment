<?php

namespace Database\Factories;

use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistTemplate>
 */
class ChecklistTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Health & Safety Compliance Audit',
            'Data Protection Assessment',
            'Financial Controls Review',
            'Environmental Compliance Check',
            'IT Security Audit',
            'HR Policy Compliance',
            'Quality Management Review',
            'Supplier Due Diligence',
            'Fire Safety Inspection',
            'GDPR Readiness Assessment',
        ];

        return [
            'title' => fake()->unique()->randomElement($titles) . ' ' . fake()->year(),
            'description' => fake()->optional(0.8)->paragraph(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'created_by' => User::factory()->admin(),
        ];
    }

    /**
     * Indicate that the template is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Create the template with a set of questions.
     *
     * @param int $count Number of questions to create (default 3)
     */
    public function withQuestions(int $count = 3): static
    {
        return $this->has(
            ChecklistQuestionFactory::new()->count($count),
            'questions'
        );
    }
}
