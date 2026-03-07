<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:100', 'max:100000000'],
            'description' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', 'in:wave,orange_money,free_money,bank_transfer'],
        ];
    }
}
