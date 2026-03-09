<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentUser extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée
     */
    protected $table = 'document_users';

    /**
     * Clé primaire de la table
     */
    protected $primaryKey = 'id';

    /**
     * Indique si la clé primaire est auto-incrémentée
     */
    public $incrementing = true;

    /**
     * Type de la clé primaire
     */
    protected $keyType = 'int';

    /**
     * Indique si le modèle utilise les timestamps
     */
    public $timestamps = true;

    /**
     * Attributs pouvant être assignés en masse
     */
    protected $fillable = [
        'user_id',
        'funding_request_id',
        'typedoc_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
        'notes',
        'metadata',
    ];

    /**
     * Attributs cachés dans la sérialisation
     */
    protected $hidden = [];

    /**
     * Attributs à caster
     */
    protected $casts = [
        'file_size' => 'integer',
        'status' => 'string',
        'verified_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Attributs par défaut
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * L'utilisateur qui a fourni le document
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * La demande de financement associée
     */
    public function fundingRequest(): BelongsTo
    {
        return $this->belongsTo(FundingRequest::class, 'funding_request_id');
    }

    /**
     * Le type de document
     */
    public function typeDoc(): BelongsTo
    {
        return $this->belongsTo(TypeDoc::class, 'typedoc_id');
    }

    /**
     * L'admin/modérateur qui a vérifié le document
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Marquer le document comme vérifié
     */
    public function markAsVerified(int $verifierId): bool
    {
        return $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifierId,
        ]);
    }

    /**
     * Marquer le document comme rejeté
     */
    public function markAsRejected(string $reason, ?int $verifierId = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_by' => $verifierId,
        ]);
    }

    /**
     * Accesseur pour l'URL du fichier
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Accesseur pour la taille formatée
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Accesseur pour l'icône du fichier selon le type MIME
     */
    public function getFileIconAttribute(): string
    {
        $icons = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'image',
            'image/png' => 'image',
            'image/gif' => 'image',
            'application/msword' => 'word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
            'application/vnd.ms-excel' => 'excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
            'text/plain' => 'text',
        ];

        return $icons[$this->file_type] ?? 'file';
    }

    /**
     * Libellé du statut pour affichage
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'verified' => 'Vérifié',
            'rejected' => 'Rejeté',
            default => $this->status,
        };
    }

    /**
     * Scope: Documents en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Documents vérifiés
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope: Documents rejetés
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: Documents pour une demande spécifique
     */
    public function scopeForFundingRequest($query, int $fundingRequestId)
    {
        return $query->where('funding_request_id', $fundingRequestId);
    }

    /**
     * Scope: Documents d'un utilisateur
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Vérifier si le document peut être modifié
     */
    public function canBeModified(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Vérifier si le document est vérifié
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Vérifier si le document est rejeté
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Supprimer le fichier physique lors de la suppression
        static::deleting(function ($document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
        });
    }
}