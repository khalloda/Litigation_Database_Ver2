<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EngagementLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'contract_number' => 'required|string|max:255|unique:engagement_letters,contract_number,' . ($this->engagementLetter->id ?? 'NULL') . ',id',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => __('app.client_required'),
            'client_id.exists' => __('app.client_not_found'),
            'contract_number.required' => __('app.contract_number_required'),
            'contract_number.unique' => __('app.contract_number_exists'),
            'issue_date.required' => __('app.issue_date_required'),
            'expiry_date.required' => __('app.expiry_date_required'),
            'expiry_date.after' => __('app.expiry_date_after_issue'),
        ];
    }
}
