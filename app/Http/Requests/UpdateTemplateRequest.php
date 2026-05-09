<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                        => ['sometimes', 'required', 'string', 'max:255'],
            'description'                  => ['sometimes', 'nullable', 'string'],
            'status'                       => ['sometimes', 'nullable', 'string', 'in:active,inactive'],
            'questions'                    => ['sometimes', 'required', 'array', 'min:1'],
            'questions.*.question_text'    => ['required_with:questions', 'string'],
            'questions.*.answer_type'      => ['required_with:questions', 'string', 'in:text,textarea,boolean,number'],
            'questions.*.required'         => ['nullable', 'boolean'],
            'questions.*.sort_order'       => ['nullable', 'integer'],
        ];
    }
}
