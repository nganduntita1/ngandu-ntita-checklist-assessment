<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
            'template_id' => ['nullable', 'integer', 'exists:checklist_templates,id'],
            'auditor_id'  => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
