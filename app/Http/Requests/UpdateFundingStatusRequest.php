<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seuls les admins peuvent changer le statut
        return auth()->check() && auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'status'          => ['required', 'string', 'in:under_review,pending_committee,approved,rejected,funded,cancelled'],
            'amount_approved' => ['nullable', 'numeric', 'min:0'],
            'comment'         => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Le statut est obligatoire.',
            'status.in'       => 'Statut invalide.',
        ];
    }
}
