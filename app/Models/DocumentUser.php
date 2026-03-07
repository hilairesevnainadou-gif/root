<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentUser extends Model
{
    use HasFactory;

    protected $table = 'document_users';

    protected $fillable = [
        'user_id',
        'funding_request_id',
        'typedoc_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',           // pending, verified, rejected
        'rejection_reason',
        'verified_at',
        'verified_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'status' => 'string',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * L'utilisateur qui a fourni le document
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * L'admin/modérateur qui a vérifié
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Marquer comme vérifié
     */
    public function markAsVerified(int $verifierId): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifierId,
        ]);
    }

    /**
     * Marquer comme rejeté
     */
    public function markAsRejected(string $reason, ?int $verifierId = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_by' => $verifierId,
        ]);
    }

    /**
     * URL du fichier
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Taille formatée (KB, MB)
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        return round($bytes / 1024, 2) . ' KB';
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
}
