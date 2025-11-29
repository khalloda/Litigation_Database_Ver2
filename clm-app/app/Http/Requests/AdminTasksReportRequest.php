<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminTasksReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check(); // Authorization handled by permission middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lawyer_id' => ['nullable', 'exists:lawyers,id'],
            'case_id' => ['nullable', 'exists:cases,id'],
            'status' => ['nullable', 'string'],
            'show_overdue' => ['nullable', 'boolean'],
            'group_by' => ['nullable', 'string', 'in:lawyer,case,null'],
            'include_subtasks' => ['nullable', 'boolean'],
            'date_range_type' => ['nullable', 'string', 'in:today,yesterday,this_week,last_week,this_month,last_month,this_quarter,last_quarter,this_year,last_year,last_7_days,last_30_days,last_90_days,custom'],
            'start_date' => ['nullable', 'required_if:date_range_type,custom', 'date'],
            'end_date' => ['nullable', 'required_if:date_range_type,custom', 'date', 'after_or_equal:start_date'],
            'orientation' => ['nullable', 'string', 'in:portrait,landscape'],
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
            'lawyer_id.exists' => __('reports.lawyer_not_found'),
            'case_id.exists' => __('reports.case_not_found'),
            'start_date.required_if' => __('reports.date_range_start_required'),
            'end_date.required_if' => __('reports.date_range_end_required'),
            'end_date.after_or_equal' => __('reports.end_date_after_start'),
        ];
    }
}

