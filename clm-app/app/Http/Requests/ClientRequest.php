<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'mfiles_id' => ['nullable', 'integer', 'min:1'],
            'client_code' => ['nullable', 'string', 'max:50', 'unique:clients,client_code,' . ($this->client ? $this->client->id : 'NULL')],
            'client_name_ar' => ['nullable', 'string', 'max:255', 'required_without:client_name_en'],
            'client_name_en' => ['nullable', 'string', 'max:255', 'required_without:client_name_ar'],
            'client_print_name' => ['nullable', 'string', 'max:255'],
            'cash_or_probono_id' => ['nullable', 'exists:option_values,id'],
            'status_id' => ['nullable', 'exists:option_values,id'],
            'client_start' => ['nullable', 'date'],
            'client_end' => ['nullable', 'date', 'after_or_equal:client_start'],
            'contact_lawyer_id' => ['nullable', 'exists:lawyers,id'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'power_of_attorney_location_id' => ['nullable', 'exists:option_values,id'],
            'documents_location_id' => ['nullable', 'exists:option_values,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'mfiles_id.integer' => __('app.mfiles_id_must_be_number'),
            'mfiles_id.min' => __('app.mfiles_id_must_be_positive'),
            'client_code.max' => __('app.client_code_max_length'),
            'client_code.unique' => __('app.client_code_already_exists'),
            'client_name_ar.required_without' => __('app.at_least_one_name_required'),
            'client_name_en.required_without' => __('app.at_least_one_name_required'),
            'client_end.after_or_equal' => __('app.client_end_must_be_after_start'),
            'logo.image' => __('app.logo_must_be_image'),
            'logo.max' => __('app.logo_max_size'),
        ];
    }
}
