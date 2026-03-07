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
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'email_verified_at',
        'password',
        'profile_photo',
        'birth_date',
        'gender',
        'address',
        'city',
        'member_id',
        'member_since',
        'member_status',
        'member_type',
        'is_active',
        'is_verified',
        'is_admin',
        'is_moderator',
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
    ];

    /**
     * Entreprise de l'utilisateur
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
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
        return $this->is_admin;
    }

    /**
     * Nom complet
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
