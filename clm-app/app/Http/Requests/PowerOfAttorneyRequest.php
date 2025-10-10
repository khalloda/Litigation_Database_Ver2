<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PowerOfAttorneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'poa_type' => 'required|string|max:255',
            'poa_number' => 'required|string|max:255|unique:power_of_attorneys,poa_number,' . ($this->powerOfAttorney->id ?? 'NULL') . ',id',
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
            'poa_type.required' => __('app.poa_type_required'),
            'poa_number.required' => __('app.poa_number_required'),
            'poa_number.unique' => __('app.poa_number_exists'),
            'issue_date.required' => __('app.issue_date_required'),
            'expiry_date.required' => __('app.expiry_date_required'),
            'expiry_date.after' => __('app.expiry_date_after_issue'),
        ];
    }
}
