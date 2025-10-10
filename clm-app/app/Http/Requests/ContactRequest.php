<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'contact_name' => 'required|string|max:255',
            'contact_type' => 'required|string|in:phone,email,fax,mobile,address,website,other',
            'contact_value' => 'required|string|max:500',
            'is_primary' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => __('app.client_required'),
            'client_id.exists' => __('app.client_not_found'),
            'contact_name.required' => __('app.contact_name_required'),
            'contact_type.required' => __('app.contact_type_required'),
            'contact_type.in' => __('app.contact_type_invalid'),
            'contact_value.required' => __('app.contact_value_required'),
        ];
    }
}
