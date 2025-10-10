<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('documents.upload');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'max:10240', // 10MB max
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif',
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,image/jpeg,image/png,image/gif'
            ],
            'client_id' => 'required|integer|exists:clients,id',
            'matter_id' => 'nullable|integer|exists:cases,id',
            'document_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_private' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'document.required' => 'Please select a document to upload.',
            'document.max' => 'The document size cannot exceed 10MB.',
            'document.mimes' => 'The document must be a PDF, Word, Excel, PowerPoint, text, or image file.',
            'document.mimetypes' => 'The document format is not supported.',
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'The selected client does not exist.',
            'matter_id.exists' => 'The selected matter does not exist.',
            'document_type.required' => 'Please specify the document type.',
            'description.max' => 'The description cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'document' => 'document file',
            'client_id' => 'client',
            'matter_id' => 'matter',
            'document_type' => 'document type',
        ];
    }
}