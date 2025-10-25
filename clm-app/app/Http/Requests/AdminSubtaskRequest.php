<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_id' => ['required', 'exists:admin_tasks,id'],
            'lawyer_id' => ['nullable', 'exists:lawyers,id'],
            'performer' => ['nullable', 'string', 'max:191'],
            'next_date' => ['nullable', 'date'],
            'result' => ['nullable', 'string'],
            'procedure_date' => ['nullable', 'date'],
            'report' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'task_id.required' => __('app.task_id_required'),
            'task_id.exists' => __('app.task_id_invalid'),
            'lawyer_id.exists' => __('app.lawyer_id_invalid'),
            'next_date.date' => __('app.next_date_invalid'),
            'procedure_date.date' => __('app.procedure_date_invalid'),
        ];
    }
}

