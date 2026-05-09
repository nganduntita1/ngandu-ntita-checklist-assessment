<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers'                    => ['required', 'array'],
            'answers.*.question_id'      => ['required', 'integer', 'exists:checklist_questions,id'],
            'answers.*.answer_value'     => ['nullable', 'string'],
        ];
    }
}
