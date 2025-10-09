<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HearingRequest extends FormRequest
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
            'matter_id' => 'required|exists:cases,id',
            'lawyer_id' => 'nullable|exists:lawyers,id',
            'date' => 'required|date',
            'procedure' => 'nullable|string|max:255',
            'court' => 'nullable|string|max:255',
            'circuit' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'decision' => 'nullable|string|max:5000',
            'short_decision' => 'nullable|string|max:255',
            'last_decision' => 'nullable|string|max:255',
            'next_hearing' => 'nullable|date|after_or_equal:date',
            'report' => 'nullable|boolean',
            'notify_client' => 'nullable|boolean',
            'attendee' => 'nullable|string|max:255',
            'evaluation' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
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
            'matter_id.required' => __('app.case_required'),
            'matter_id.exists' => __('app.case_not_found'),
            'date.required' => __('app.hearing_date_required'),
            'next_hearing.after_or_equal' => __('app.next_hearing_after_date'),
        ];
    }
}

