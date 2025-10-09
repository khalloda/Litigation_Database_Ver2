<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LawyerRequest extends FormRequest
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
            'lawyer_name_ar' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(empty($this->lawyer_name_en)),
            ],
            'lawyer_name_en' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(empty($this->lawyer_name_ar)),
            ],
            'lawyer_name_title' => 'nullable|string|max:255',
            'lawyer_email' => 'nullable|email|max:255',
            'attendance_track' => 'nullable|boolean',
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
            'lawyer_name_ar.required_if' => __('app.lawyer_name_required'),
            'lawyer_name_en.required_if' => __('app.lawyer_name_required'),
        ];
    }
}

