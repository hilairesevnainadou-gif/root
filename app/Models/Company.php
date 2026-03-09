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
        'is_primary',
        'registration_number',
        'tax_id',
        'address',
        'city',
        'company_phone',
        'company_email',
        'description',
        'is_active',
    ];

    protected $casts = [
        'employees_count' => 'integer',
        'annual_turnover' => 'decimal:2',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Propriétaire de l'entreprise
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label du type d'entreprise
     */
    public function getCompanyTypeLabelAttribute(): string
    {
        return match($this->company_type) {
            'sarl' => 'SARL',
            'sa' => 'SA',
            'snc' => 'SNC',
            'ei' => 'Entreprise Individuelle',
            'eurl' => 'EURL',
            'cooperative' => 'Coopérative',
            'ong' => 'ONG',
            'association' => 'Association',
            'autre' => 'Autre',
            default => strtoupper($this->company_type),
        };
    }

    /**
     * Label du secteur
     */
    public function getSectorLabelAttribute(): string
    {
        return match($this->sector) {
            'agriculture' => 'Agriculture',
            'elevage' => 'Élevage',
            'peche' => 'Pêche',
            'industrie' => 'Industrie',
            'commerce' => 'Commerce',
            'services' => 'Services',
            'tourisme' => 'Tourisme',
            'batiment' => 'Bâtiment & Travaux Publics',
            'technologie' => 'Technologie & IT',
            'sante' => 'Santé',
            'education' => 'Éducation',
            'finance' => 'Finance & Assurance',
            'transport' => 'Transport & Logistique',
            'autre' => 'Autre secteur',
            default => ucfirst($this->sector),
        };
    }

    /**
     * Couleur générée pour l'avatar
     */
    public function getColorAttribute(): string
    {
        $colors = [
            '#1e40af', // blue
            '#7c3aed', // violet
            '#dc2626', // red
            '#059669', // green
            '#d97706', // amber
            '#0891b2', // cyan
            '#be185d', // pink
            '#4338ca', // indigo
        ];
        
        // Génère un index basé sur le nom de l'entreprise
        $hash = array_sum(array_map('ord', str_split($this->company_name)));
        return $colors[$hash % count($colors)];
    }

    /**
     * Initialiales pour l'avatar
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->company_name);
        $initials = '';
        
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        
        return $initials ?: strtoupper(substr($this->company_name, 0, 2));
    }

    /**
     * Scope pour l'entreprise principale
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope pour les entreprises actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}