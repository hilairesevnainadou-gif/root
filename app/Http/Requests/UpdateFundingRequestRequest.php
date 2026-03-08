<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\OwnedByUser; // Si vous utilisez la classe Rule

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'funding_request_id' => [
                'required',
                'integer',
                'exists:funding_requests,id',
                new OwnedByUser(), // Méthode 1 : avec la classe Rule
                // 'owned_by_user', // Méthode 2 : avec Validator::extend
            ],
            'typedoc_id' => 'required|integer|exists:type_docs,id',
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
            'funding_request_id.exists' => 'La demande de financement n\'existe pas.',
            'typedoc_id.required' => 'Le type de document est requis.',
            'document.required' => 'Un fichier est requis.',
            'document.mimes' => 'Format accepté : PDF, JPG, PNG, DOC, DOCX.',
            'document.max' => 'Taille maximale : 10 Mo.',
        ];
    }
}
