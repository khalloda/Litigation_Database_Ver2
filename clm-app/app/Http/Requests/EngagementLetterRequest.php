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
            'client_name' => 'nullable|string|max:255',
            'contract_date' => 'nullable|date',
            'contract_details' => 'nullable|string',
            'contract_structure' => 'nullable|string',
            'contract_type' => 'nullable|string|max:255',
            'matters' => 'nullable|string',
            'status' => 'nullable|string|max:255',
            'mfiles_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => __('app.client_required'),
            'client_id.exists' => __('app.client_not_found'),
            'contract_date.date' => __('app.contract_date_invalid'),
            'mfiles_id.integer' => __('app.mfiles_id_invalid'),
        ];
    }
}
