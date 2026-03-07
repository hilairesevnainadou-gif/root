<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFundingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'typefinancement_id' => ['required', 'exists:typefinanciements,id', 'active_financement'],
            'title' => ['required', 'string', 'max:255'],
            'amount_requested' => ['required', 'numeric', 'min:1000', 'max:100000000'],
            'duration' => ['required', 'integer', 'min:1', 'max:120'],
            'description' => ['required', 'string', 'min:50', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'typefinancement_id.active_financement' => 'Ce type de financement n\'est pas actif.',
            'amount_requested.min' => 'Le montant minimum est de 1,000 XOF.',
            'description.min' => 'La description doit contenir au moins 50 caractères.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Vérifier que le type de financement correspond au type d'utilisateur
        $typeFinancement = \App\Models\TypeFinancement::find($this->typefinancement_id);

        if ($typeFinancement && !$this->user()->isAdmin()) {
            $userType = $this->user()->member_type;
            $allowedTypes = match($userType) {
                'particulier' => ['particulier'],
                'entreprise' => ['entreprise'],
                default => ['particulier', 'entreprise', 'admin'],
            };

            if (!in_array($typeFinancement->typeusers, $allowedTypes)) {
                $this->validator->errors()->add('typefinancement_id',
                    'Ce financement n\'est pas disponible pour votre profil.');
            }
        }
    }
}
