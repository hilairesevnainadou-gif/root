<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TypeFinancement extends Model
{
    use HasFactory;

    protected $table = 'typefinanciements';

    protected $fillable = [
        'name',
        'description',
        'typeusers',
        'code',
        'amount',
        'registration_fee',
        'registration_final_fee',
        'duration_months',
        'is_active',
        'is_variable_amount',
        'max_daily_amount',
        'daily_gain',
    ];

    protected $casts = [
        'typeusers' => 'string',
        'amount' => 'decimal:2',
        'registration_fee' => 'decimal:2',
        'registration_final_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'duration_months' => 'integer',
    ];

    /**
     * Les demandes de financement
     */
    public function fundingRequests(): HasMany
    {
        return $this->hasMany(FundingRequest::class, 'typefinancement_id');
    }

    /**
     * Documents requis pour ce financement
     */
    public function requiredTypeDocs(): BelongsToMany
    {
        return $this->belongsToMany(
            TypeDoc::class,
            'typefinancement_typedoc',
            'typefinancement_id',
            'typedoc_id'
        );
    }

    public function isForParticulier(): bool
    {
        return $this->typeusers === 'particulier';
    }

    public function isForEntreprise(): bool
    {
        return $this->typeusers === 'entreprise';
    }
}
