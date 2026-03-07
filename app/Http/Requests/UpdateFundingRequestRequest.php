<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundingRequestRequest extends FormRequest
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
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'amount_requested' => ['sometimes', 'numeric', 'min:1000'],
            'duration' => ['sometimes', 'integer', 'min:1', 'max:120'],
            'description' => ['sometimes', 'string', 'min:50', 'max:5000'],
        ];
    }

    public function forbiddenResponse()
    {
        return response()->json([
            'message' => 'Vous ne pouvez modifier que les demandes en brouillon.'
        ], 403);
    }
}
