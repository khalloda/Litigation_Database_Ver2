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
            'client_name_ar' => ['nullable', 'string', 'max:255', 'required_without:client_name_en'],
            'client_name_en' => ['nullable', 'string', 'max:255', 'required_without:client_name_ar'],
        ];
    }
}


