<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HearingScheduleReportRequest extends FormRequest
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
            'date_range_type' => ['nullable', 'string', 'in:today,yesterday,this_week,last_week,this_month,last_month,this_quarter,last_quarter,this_year,last_year,last_7_days,last_30_days,last_90_days,custom'],
            'start_date' => ['nullable', 'required_if:date_range_type,custom', 'date'],
            'end_date' => ['nullable', 'required_if:date_range_type,custom', 'date', 'after_or_equal:start_date'],
            'court_id' => ['nullable', 'exists:courts,id'],
            'case_id' => ['nullable', 'exists:cases,id'],
            'lawyer_id' => ['nullable', 'exists:lawyers,id'],
            'case_status' => ['nullable', 'string', 'in:all,سارية,منتهية,active,closed'],
            'view_type' => ['nullable', 'string', 'in:list,calendar'],
            'show_overdue' => ['nullable', 'boolean'],
            'group_by' => ['nullable', 'string', 'in:court,case,lawyer,null'],
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
            'start_date.required_if' => __('reports.date_range_start_required'),
            'end_date.required_if' => __('reports.date_range_end_required'),
            'end_date.after_or_equal' => __('reports.end_date_after_start'),
            'court_id.exists' => __('reports.court_not_found'),
            'case_id.exists' => __('reports.case_not_found'),
            'lawyer_id.exists' => __('reports.lawyer_not_found'),
        ];
    }
}

