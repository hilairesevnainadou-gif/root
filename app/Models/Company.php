<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_type',
        'sector',
        'job_title',
        'employees_count',
        'annual_turnover',
    ];

    protected $casts = [
        'employees_count' => 'integer',
        'annual_turnover' => 'decimal:2',
    ];

    /**
     * Propriétaire de l'entreprise
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
