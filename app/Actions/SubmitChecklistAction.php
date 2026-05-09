<?php

namespace App\Actions;

use App\Models\ChecklistInstance;
use Illuminate\Validation\ValidationException;

class SubmitChecklistAction
{
    /**
     * Validate that all required questions in the instance have a non-empty answer.
     *
     * @throws ValidationException  when one or more required questions are unanswered
     */
    public function execute(ChecklistInstance $instance): void
    {
        // Eager-load template questions and existing answers
        $instance->loadMissing(['template.questions', 'answers']);

        $requiredQuestions = $instance->template->questions->where('required', true);

        // Index existing answers by question_id for fast lookup
        $answeredQuestionIds = $instance->answers
            ->filter(fn ($answer) => $answer->answer_value !== null && $answer->answer_value !== '')
            ->pluck('question_id')
            ->flip();

        $unanswered = $requiredQuestions
            ->filter(fn ($question) => ! $answeredQuestionIds->has($question->id))
            ->map(fn ($question) => [
                'id'            => $question->id,
                'question_text' => $question->question_text,
            ])
            ->values()
            ->all();

        if (! empty($unanswered)) {
            throw ValidationException::withMessages([
                'unanswered_questions' => $unanswered,
            ]);
        }
    }
}
