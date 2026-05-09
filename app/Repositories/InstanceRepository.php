<?php

namespace App\Repositories;

use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Repositories\Contracts\InstanceRepositoryInterface;

class InstanceRepository implements InstanceRepositoryInterface
{
    /**
     * Create and persist a new checklist instance.
     */
    public function create(array $data): ChecklistInstance
    {
        return ChecklistInstance::create($data);
    }

    /**
     * Find a checklist instance by ID or throw a ModelNotFoundException.
     */
    public function findOrFail(int $id): ChecklistInstance
    {
        return ChecklistInstance::findOrFail($id);
    }

    /**
     * Upsert answers for a checklist instance.
     * For each answer, updates an existing record or creates a new one
     * matched by instance_id + question_id.
     */
    public function upsertAnswers(ChecklistInstance $instance, array $answers): void
    {
        foreach ($answers as $answer) {
            ChecklistAnswer::updateOrCreate(
                [
                    'instance_id' => $instance->id,
                    'question_id' => $answer['question_id'],
                ],
                [
                    'answer_value' => $answer['answer_value'],
                ]
            );
        }
    }

    /**
     * Mark a checklist instance as completed.
     * Sets status to 'completed' and records the completed_at timestamp.
     */
    public function markCompleted(ChecklistInstance $instance): ChecklistInstance
    {
        $instance->status       = 'completed';
        $instance->completed_at = now();
        $instance->save();

        return $instance;
    }
}
