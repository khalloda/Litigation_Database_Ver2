<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DocumentInventoryReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->can('reports.view');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:clients,id'],
            'case_id' => ['nullable', 'exists:cases,id'],
            'document_type' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'storage_type' => ['nullable', 'string', Rule::in(['physical', 'digital', 'both', 'all'])],
            'show_missing' => ['nullable', 'boolean'],
            'group_by' => ['nullable', 'string', Rule::in(['client', 'case', 'location', null])],
            'orientation' => ['nullable', 'string', Rule::in(['portrait', 'landscape'])],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.exists' => __('reports.client_not_found'),
            'case_id.exists' => __('reports.case_not_found'),
        ];
    }
}

