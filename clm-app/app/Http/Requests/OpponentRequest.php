<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'opponent_name_ar' => 'nullable|string|max:255',
            'opponent_name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}


