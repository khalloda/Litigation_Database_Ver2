<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourtRequest extends FormRequest
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
            'court_name_ar' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(empty($this->court_name_en)),
            ],
            'court_name_en' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(empty($this->court_name_ar)),
            ],
            'court_circuit' => 'nullable|exists:option_values,id',
            'court_circuit_secretary' => 'nullable|exists:option_values,id',
            'court_floor' => 'nullable|exists:option_values,id',
            'court_hall' => 'nullable|exists:option_values,id',
            'is_active' => 'boolean',
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
            'court_name_ar.required_if' => __('app.court_name_required'),
            'court_name_en.required_if' => __('app.court_name_required'),
            'court_circuit.exists' => __('app.invalid_court_circuit'),
            'court_circuit_secretary.exists' => __('app.invalid_court_secretary'),
            'court_floor.exists' => __('app.invalid_court_floor'),
            'court_hall.exists' => __('app.invalid_court_hall'),
        ];
    }
}
