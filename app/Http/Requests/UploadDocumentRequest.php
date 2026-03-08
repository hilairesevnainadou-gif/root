<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'funding_request_id' => 'required|integer|exists:funding_requests,id',
            'typedoc_id' => 'required|integer|exists:typedocs,id',
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240', // 10MB
            ],
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'funding_request_id.required' => 'Une demande de financement est requise.',
            'typedoc_id.required' => 'Le type de document est requis.',
            'typedoc_id.exists' => 'Le type de document sélectionné n\'existe pas.',
            'document.required' => 'Un fichier est requis.',
            'document.mimes' => 'Format accepté : PDF, JPG, PNG, DOC, DOCX.',
            'document.max' => 'Taille maximale : 10 Mo.',
        ];
    }
}
