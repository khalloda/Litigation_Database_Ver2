<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matter_id' => ['required', 'exists:cases,id'],
            'lawyer_id' => ['nullable', 'exists:lawyers,id'],
            'last_follow_up' => ['nullable', 'string'],
            'last_date' => ['nullable', 'date'],
            'authority' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'string', 'max:191'],
            'circuit' => ['nullable', 'string', 'max:191'],
            'required_work' => ['nullable', 'string'],
            'performer' => ['nullable', 'string', 'max:191'],
            'previous_decision' => ['nullable', 'string'],
            'court' => ['nullable', 'string', 'max:191'],
            'result' => ['nullable', 'string'],
            'creation_date' => ['nullable', 'date'],
            'execution_date' => ['nullable', 'date'],
            'alert' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'matter_id.required' => __('app.matter_id_required'),
            'matter_id.exists' => __('app.matter_id_invalid'),
            'lawyer_id.exists' => __('app.lawyer_id_invalid'),
            'last_date.date' => __('app.last_date_invalid'),
            'creation_date.date' => __('app.creation_date_invalid'),
            'execution_date.date' => __('app.execution_date_invalid'),
        ];
    }
}

