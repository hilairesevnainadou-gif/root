<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        // Identité
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'email_verified_at',
        'password',
        
        // Photo & documents
        'profile_photo',
        'id_number',           // AJOUTÉ
        'id_document_path',    // AJOUTÉ
        
        // Informations personnelles
        'birth_date',
        'gender',
        'bio',                 // AJOUTÉ
        
        // Adresse complète
        'address',
        'city',
        'postal_code',         // AJOUTÉ
        'country',             // AJOUTÉ
        
        // Informations entreprise (legacy - dans table users)
        'company_name',
        'company_type',
        'sector',
        'job_title',
        'employees_count',
        'annual_turnover',
        
        // Membre
        'member_id',
        'member_since',
        'member_status',
        'member_type',
        
        // Statuts
        'is_active',
        'is_verified',
        'is_admin',
        'is_moderator',
        
        // Préférences
        'locale',              // AJOUTÉ
        'timezone',            // AJOUTÉ
        'preferences',         // AJOUTÉ
        
        // Connexion
        'last_login_at',
        'last_login_ip',
        'last_login_device',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'member_since' => 'date',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_admin' => 'boolean',
        'is_moderator' => 'boolean',
        'last_login_at' => 'datetime',
        'preferences' => 'json',    // AJOUTÉ
    ];

    /**
     * Toutes les entreprises de l'utilisateur
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Entreprise principale
     */
    public function primaryCompany(): HasOne
    {
        return $this->hasOne(Company::class)->where('is_primary', true);
    }

    /**
     * Alias legacy
     */
    public function company(): ?Company
    {
        return $this->primaryCompany ?? $this->companies()->first();
    }

    /**
     * Portefeuille
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Demandes de financement
     */
    public function fundingRequests(): HasMany
    {
        return $this->hasMany(FundingRequest::class);
    }

    /**
     * Documents
     */
    public function documentUsers(): HasMany
    {
        return $this->hasMany(DocumentUser::class);
    }

    /**
     * Notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Transactions via wallet
     */
    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class);
    }

    /**
     * Vérifie si membre entreprise
     */
    public function isEntreprise(): bool
    {
        return $this->member_type === 'entreprise';
    }

    /**
     * Vérifie si admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Nom complet
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Vérifie si l'utilisateur a une entreprise principale
     */
    public function hasPrimaryCompany(): bool
    {
        return $this->companies()->where('is_primary', true)->exists();
    }
}