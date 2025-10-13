<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check(); // Authorization handled by policies in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'matter_name_ar' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(empty($this->matter_name_en)),
            ],
            'matter_name_en' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(empty($this->matter_name_ar)),
            ],
            'matter_description' => 'nullable|string|max:5000',
            'matter_status' => 'nullable|string|max:255',
            'matter_category' => 'nullable|string|max:255',
            'matter_degree' => 'nullable|string|max:255',
            'court_id' => 'nullable|exists:courts,id',
            'matter_circuit' => 'nullable|exists:option_values,id',
            'matter_destination' => 'nullable|string|max:255',
            'matter_importance' => 'nullable|string|max:255',
            'matter_evaluation' => 'nullable|string|max:255',
            'matter_start_date' => 'nullable|date',
            'matter_end_date' => 'nullable|date|after_or_equal:matter_start_date',
            'matter_asked_amount' => 'nullable|numeric|min:0',
            'matter_judged_amount' => 'nullable|numeric|min:0',
            'matter_shelf' => 'nullable|string|max:255',
            'matter_partner' => 'nullable|string|max:255',
            'lawyer_a' => 'nullable|string|max:255',
            'lawyer_b' => 'nullable|string|max:255',
            'circuit_secretary' => 'nullable|exists:option_values,id',
            'court_floor' => 'nullable|exists:option_values,id',
            'court_hall' => 'nullable|exists:option_values,id',
            'notes_1' => 'nullable|string|max:5000',
            'notes_2' => 'nullable|string|max:5000',
            'client_and_capacity' => 'nullable|string|max:1000',
            'opponent_and_capacity' => 'nullable|string|max:1000',
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
            'client_id.required' => __('app.client_required'),
            'client_id.exists' => __('app.client_not_found'),
            'matter_name_ar.required_if' => __('app.case_name_required'),
            'matter_name_en.required_if' => __('app.case_name_required'),
            'matter_end_date.after_or_equal' => __('app.end_date_after_start'),
        ];
    }
}
