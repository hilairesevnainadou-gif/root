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
            'funding_request_id' => ['required', 'exists:funding_requests,id', 'owned_by_user'],
            'typedoc_id' => [
                'required',
                'exists:typedocs,id',
                'required_for_funding',
                'not_duplicate'
            ],
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240', // 10MB
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.max' => 'Le fichier ne doit pas dépasser 10MB.',
            'document.mimes' => 'Formats acceptés : PDF, JPG, PNG.',
            'typedoc_id.owned_by_user' => 'Cette demande ne vous appartient pas.',
            'typedoc_id.required_for_funding' => 'Ce document n\'est pas requis pour ce financement.',
            'typedoc_id.not_duplicate' => 'Vous avez déjà fourni ce document.',
        ];
    }
}
