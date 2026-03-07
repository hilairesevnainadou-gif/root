<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFundingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin() || auth()->user()?->is_moderator;
    }

    public function rules(): array
    {
        $fundingRequest = $this->route('funding_request');

        $validTransitions = match($fundingRequest->status) {
            'submitted' => ['under_review', 'cancelled'],
            'under_review' => ['pending_committee', 'rejected'],
            'pending_committee' => ['approved', 'rejected'],
            'approved' => ['funded', 'cancelled'],
            default => [],
        };

        return [
            'status' => ['required', Rule::in($validTransitions)],
            'amount_approved' => [
                'required_if:status,approved,funded',
                'numeric',
                'min:0',
                "max:{$fundingRequest->amount_requested}"
            ],
            'comment' => ['nullable', 'string', 'max:2000'],
            'committee_decision' => [
                'required_if:status,approved,rejected',
                'string',
                'max:2000'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Transition de statut non autorisée.',
            'amount_approved.max' => 'Le montant approuvé ne peut excéder le montant demandé.',
        ];
    }
}
