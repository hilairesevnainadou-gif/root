<?php

namespace App\Rules;

use App\Models\FundingRequest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OwnedByUser implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $fundingRequest = FundingRequest::find($value);

        if (!$fundingRequest) {
            $fail('La demande de financement n\'existe pas.');
            return;
        }

        if ($fundingRequest->user_id !== auth()->id()) {
            $fail('Vous n\'êtes pas autorisé à accéder à cette demande.');
        }
    }
}
