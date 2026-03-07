<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class FundingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'typefinancement_id',
        'request_number',
        'title',
        'amount_requested',
        'amount_approved',
        'amount_rembursed',
        'duration',
        'description',
        'status',
        'payment_status',
        'kkiapay_transaction_id',
        'registration_fee_paid',
        'paid_at',
        'submitted_at',
        'reviewed_at',
        'committee_review_started_at',
        'committee_decision_at',
        'approved_at',
        'funded_at',
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
        'amount_approved' => 'decimal:2',
        'amount_rembursed' => 'decimal:2',
        'registration_fee_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'committee_review_started_at' => 'datetime',
        'committee_decision_at' => 'datetime',
        'approved_at' => 'datetime',
        'funded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeFinancement(): BelongsTo
    {
        return $this->belongsTo(TypeFinancement::class, 'typefinancement_id');
    }

    public function documentUsers(): HasMany
    {
        return $this->hasMany(DocumentUser::class, 'funding_request_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'funding_request_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Marquer comme payé et soumis - VERSION CORRIGÉE ET ROBUSTE
     */
    public function markAsPaid(string $kkiapayTransactionId, ?float $amount = null): bool
    {
        // Vérifier si déjà payé pour éviter les doubles traitements
        if ($this->isPaid()) {
            return true;
        }

        $registrationFee = $amount ?? $this->typeFinancement?->registration_fee ?? 0;

        return DB::transaction(function () use ($kkiapayTransactionId, $registrationFee) {
            $updated = $this->update([
                'payment_status' => 'paid',
                'kkiapay_transaction_id' => $kkiapayTransactionId,
                'registration_fee_paid' => $registrationFee,
                'paid_at' => now(),
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            if ($updated) {
                // Créer les documents requis automatiquement
                $this->createRequiredDocuments();
            }

            return $updated;
        });
    }

    /**
     * Créer les documents requis - VERSION CORRIGÉE
     */
    public function createRequiredDocuments(): void
    {
        // Vérifier si déjà créés pour éviter les doublons
        $existingCount = $this->documentUsers()->count();
        if ($existingCount > 0) {
            return;
        }

        $requiredDocs = $this->typeFinancement?->requiredTypeDocs;

        if (!$requiredDocs || $requiredDocs->isEmpty()) {
            return;
        }

        $documentsToCreate = [];

        foreach ($requiredDocs as $typeDoc) {
            // Chercher document existant vérifié de l'utilisateur (global)
            $existingDoc = DocumentUser::where('user_id', $this->user_id)
                ->where('typedoc_id', $typeDoc->id)
                ->where('status', 'verified')
                ->whereNull('funding_request_id')
                ->first();

            if ($existingDoc) {
                // Lier le document existant
                $documentsToCreate[] = [
                    'user_id' => $this->user_id,
                    'funding_request_id' => $this->id,
                    'typedoc_id' => $typeDoc->id,
                    'file_path' => $existingDoc->file_path,
                    'file_name' => $existingDoc->file_name,
                    'file_type' => $existingDoc->file_type,
                    'file_size' => $existingDoc->file_size,
                    'status' => 'verified',
                    'verified_at' => $existingDoc->verified_at,
                    'verified_by' => $existingDoc->verified_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                // Créer un document vide en attente
                $documentsToCreate[] = [
                    'user_id' => $this->user_id,
                    'funding_request_id' => $this->id,
                    'typedoc_id' => $typeDoc->id,
                    'file_path' => '',
                    'file_name' => '',
                    'file_type' => '',
                    'file_size' => 0,
                    'status' => 'pending',
                    'verified_at' => null,
                    'verified_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insertion en batch pour performance
        if (!empty($documentsToCreate)) {
            DocumentUser::insert($documentsToCreate);
        }
    }

    /**
     * Compter les documents en attente
     */
    public function pendingDocumentsCount(): int
    {
        return $this->documentUsers()->where('status', 'pending')->count();
    }

    /**
     * Vérifier si tous les documents sont fournis
     */
    public function hasAllDocuments(): bool
    {
        return $this->pendingDocumentsCount() === 0;
    }

    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
