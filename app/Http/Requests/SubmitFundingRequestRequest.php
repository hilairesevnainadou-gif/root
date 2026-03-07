<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitFundingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $fundingRequest = $this->route('funding_request');
        return $fundingRequest &&
               $fundingRequest->user_id === auth()->id() &&
               $fundingRequest->status === 'draft';
    }

    public function rules(): array
    {
        $fundingRequest = $this->route('funding_request');
        $requiredDocs = $fundingRequest->typeFinancement->required_documents ?? [];

        return [
            'documents_check' => [
                'required',
                function ($attribute, $value, $fail) use ($fundingRequest, $requiredDocs) {
                    $missing = $fundingRequest->missingDocuments();

                    if (!empty($missing)) {
                        $docNames = \App\Models\TypeDoc::whereIn('id', $missing)
                            ->pluck('name')
                            ->implode(', ');
                        $fail("Documents manquants requis : {$docNames}");
                    }
                }
            ],
            'terms_accepted' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms_accepted.accepted' => 'Vous devez accepter les conditions.',
        ];
    }
}
