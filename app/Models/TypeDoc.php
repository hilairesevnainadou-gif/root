<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TypeDoc extends Model
{
    use HasFactory;

    protected $table = 'typedocs';

    protected $fillable = [
        'name',
        'description',
        'typeusers',
    ];

    protected $casts = [
        'typeusers' => 'string',
    ];

    /**
     * Documents uploadés par les utilisateurs
     */
    public function documentUsers(): HasMany
    {
        return $this->hasMany(DocumentUser::class, 'typedoc_id');
    }

    /**
     * Types de financement qui demandent ce document
     */
    public function typeFinancements(): BelongsToMany
    {
        return $this->belongsToMany(
            TypeFinancement::class,
            'typefinancement_typedoc',
            'typedoc_id',
            'typefinancement_id'
        );
    }
}
