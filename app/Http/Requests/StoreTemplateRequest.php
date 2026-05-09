<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                        => ['required', 'string', 'max:255'],
            'description'                  => ['nullable', 'string'],
            'status'                       => ['nullable', 'string', 'in:active,inactive'],
            'questions'                    => ['required', 'array', 'min:1'],
            'questions.*.question_text'    => ['required', 'string'],
            'questions.*.answer_type'      => ['required', 'string', 'in:text,textarea,boolean,number'],
            'questions.*.required'         => ['nullable', 'boolean'],
            'questions.*.sort_order'       => ['nullable', 'integer'],
        ];
    }
}
