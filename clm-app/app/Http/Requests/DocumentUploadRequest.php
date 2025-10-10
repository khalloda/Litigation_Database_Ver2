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
        $rules = [
            'document_storage_type' => 'required|in:physical,digital,both',
            'client_id' => 'required|integer|exists:clients,id',
            'matter_id' => 'nullable|integer|exists:cases,id',
            'document_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'responsible_lawyer' => 'nullable|string|max:255',
            'movement_card' => 'boolean',
            'deposit_date' => 'nullable|date',
            'document_date' => 'nullable|date',
            'case_number' => 'nullable|string|max:255',
            'pages_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'mfiles_uploaded' => 'boolean',
            'mfiles_id' => 'nullable|string|max:255',
        ];

        // Conditional file upload validation
        $storageType = $this->input('document_storage_type');
        if (in_array($storageType, ['digital', 'both'])) {
            $rules['document'] = [
                'required',
                'file',
                'max:10240', // 10MB max
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif',
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,image/jpeg,image/png,image/gif'
            ];
        } else {
            $rules['document'] = 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif';
        }

        // Conditional M-Files validation
        if ($this->input('mfiles_uploaded')) {
            $rules['mfiles_id'] = 'required|string|max:255';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'document_storage_type.required' => 'Please select the document storage type.',
            'document_storage_type.in' => 'Invalid document storage type selected.',
            'document.required' => 'Please select a document to upload.',
            'document.max' => 'The document size cannot exceed 10MB.',
            'document.mimes' => 'The document must be a PDF, Word, Excel, PowerPoint, text, or image file.',
            'document.mimetypes' => 'The document format is not supported.',
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'The selected client does not exist.',
            'matter_id.exists' => 'The selected matter does not exist.',
            'document_type.required' => 'Please specify the document type.',
            'description.max' => 'The description cannot exceed 1000 characters.',
            'mfiles_id.required' => 'M-Files ID is required when document is uploaded to M-Files.',
            'pages_count.min' => 'Pages count cannot be negative.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'document' => 'document file',
            'document_storage_type' => 'storage type',
            'client_id' => 'client',
            'matter_id' => 'matter',
            'document_type' => 'document type',
            'mfiles_id' => 'M-Files ID',
            'responsible_lawyer' => 'responsible lawyer',
            'movement_card' => 'movement card',
            'deposit_date' => 'deposit date',
            'document_date' => 'document date',
            'case_number' => 'case number',
            'pages_count' => 'pages count',
        ];
    }
}