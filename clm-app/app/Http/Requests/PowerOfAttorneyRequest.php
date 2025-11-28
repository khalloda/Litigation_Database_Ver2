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
            'client_print_name' => 'nullable|string|max:255',
            'principal_name' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'capacity' => 'nullable|string|max:255',
            'authorized_lawyers' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'inventory' => 'nullable|boolean',
            'issuing_authority' => 'nullable|string|max:255',
            'letter' => 'nullable|string|max:255',
            'poa_number' => 'nullable|integer',
            'principal_capacity' => 'nullable|string|max:255',
            'copies_count' => 'nullable|integer',
            'serial' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => __('app.client_required'),
            'client_id.exists' => __('app.client_not_found'),
            'principal_name.required' => __('app.principal_name_required'),
            'year.integer' => __('app.year_invalid'),
            'issue_date.date' => __('app.issue_date_invalid'),
            'poa_number.integer' => __('app.poa_number_invalid'),
            'copies_count.integer' => __('app.copies_count_invalid'),
        ];
    }
}
