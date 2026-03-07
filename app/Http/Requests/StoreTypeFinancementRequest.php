<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTypeFinancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:typefinanciements'],
            'description' => ['nullable', 'string', 'max:2000'],
            'typeusers' => ['required', Rule::in(['particulier', 'entreprise', 'admin'])],
            'code' => ['required', 'string', 'max:50', 'unique:typefinanciements', 'alpha_dash'],
            'amount' => ['required', 'numeric', 'min:0'],
            'registration_fee' => ['required', 'numeric', 'min:0'],
            'registration_final_fee' => ['required', 'numeric', 'min:0'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:360'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['exists:typedocs,id'],
            'is_active' => ['boolean'],
        ];
    }
}
