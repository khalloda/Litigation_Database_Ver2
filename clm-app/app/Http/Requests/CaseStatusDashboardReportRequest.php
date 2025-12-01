<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CaseStatusDashboardReportRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(['all', 'سارية', 'منتهية', 'active', 'closed'])],
            'category_id' => ['nullable', 'exists:option_values,id'],
            'court_id' => ['nullable', 'exists:courts,id'],
            'lawyer_id' => ['nullable', 'exists:lawyers,id'],
            'show_attention_required' => ['nullable', 'boolean'],
            'show_recent_activity' => ['nullable', 'boolean'],
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
            'category_id.exists' => __('reports.category_not_found'),
            'court_id.exists' => __('reports.court_not_found'),
            'lawyer_id.exists' => __('reports.lawyer_not_found'),
        ];
    }
}

