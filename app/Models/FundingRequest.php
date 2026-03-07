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

    // ========== RELATIONS ==========

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

    // ========== STATUTS ==========

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

    // ========== DOCUMENTS (Basés uniquement sur TypeFinancement) ==========

    /**
     * Récupérer les documents requis pour ce type de financement
     * Ne dépend PAS du statut de l'utilisateur
     */
    public function requiredDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->typeFinancement?->requiredTypeDocs ?? collect();
    }

    /**
     * Vérifier si tous les documents requis sont fournis et vérifiés
     * Basé UNIQUEMENT sur le type de financement
     */
    public function hasAllRequiredDocuments(): bool
    {
        $requiredDocs = $this->requiredDocuments();

        if ($requiredDocs->isEmpty()) {
            return true; // Aucun document requis
        }

        $providedDocIds = $this->documentUsers()
            ->where('status', 'verified')
            ->pluck('typedoc_id')
            ->toArray();

        foreach ($requiredDocs as $requiredDoc) {
            if (!in_array($requiredDoc->id, $providedDocIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Récupérer les documents manquants
     * Basé UNIQUEMENT sur le type de financement
     */
    public function missingDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        $requiredDocs = $this->requiredDocuments();

        if ($requiredDocs->isEmpty()) {
            return collect();
        }

        $providedDocIds = $this->documentUsers()
            ->pluck('typedoc_id')
            ->toArray();

        return $requiredDocs->filter(function ($doc) use ($providedDocIds) {
            return !in_array($doc->id, $providedDocIds);
        });
    }

    /**
     * Nombre de documents en attente
     */
    public function pendingDocumentsCount(): int
    {
        return $this->documentUsers()->where('status', 'pending')->count();
    }

    /**
     * Nombre de documents vérifiés
     */
    public function verifiedDocumentsCount(): int
    {
        return $this->documentUsers()->where('status', 'verified')->count();
    }

    /**
     * Nombre total de documents requis (basé sur TypeFinancement uniquement)
     */
    public function totalRequiredDocumentsCount(): int
    {
        return $this->requiredDocuments()->count();
    }

    /**
     * Documents fournis mais en attente de vérification
     */
    public function pendingVerificationDocumentsCount(): int
    {
        return $this->documentUsers()
            ->where('status', 'pending')
            ->whereNotNull('file_path')
            ->count();
    }

    // ========== PAIEMENT ==========

    /**
     * Marquer comme payé et soumis
     */
    public function markAsPaid(string $kkiapayTransactionId, ?float $amount = null): bool
    {
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
                $this->createRequiredDocuments();
            }

            return $updated;
        });
    }

    /**
     * Créer les entrées de documents requis après paiement
     * Basé UNIQUEMENT sur TypeFinancement->requiredTypeDocs()
     */
    public function createRequiredDocuments(): void
    {
        // Vérifier si déjà créés
        if ($this->documentUsers()->exists()) {
            return;
        }

        $requiredDocs = $this->requiredDocuments();

        if ($requiredDocs->isEmpty()) {
            return;
        }

        $documentsToCreate = [];

        foreach ($requiredDocs as $typeDoc) {
            // Chercher si l'utilisateur a déjà un document vérifié de ce type (global)
            $existingDoc = DocumentUser::where('user_id', $this->user_id)
                ->where('typedoc_id', $typeDoc->id)
                ->where('status', 'verified')
                ->whereNull('funding_request_id') // Document global non lié
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
                    'file_path' => null,
                    'file_name' => null,
                    'file_type' => null,
                    'file_size' => 0,
                    'status' => 'pending',
                    'verified_at' => null,
                    'verified_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($documentsToCreate)) {
            DocumentUser::insert($documentsToCreate);
        }
    }

    // ========== SCOPES ==========

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

    // ========== HELPERS ==========

    /**
     * Libellé du statut
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Brouillon',
            'submitted' => 'Soumise',
            'under_review' => 'En examen',
            'pending_committee' => 'Comité',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
            'funded' => 'Financée',
            'in_progress' => 'En cours',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            default => $this->status,
        };
    }

    /**
     * Libellé du statut de paiement
     */
    public function getPaymentStatusLabel(): string
    {
        return match($this->payment_status) {
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'refunded' => 'Remboursé',
            default => $this->payment_status,
        };
    }

    /**
     * Pourcentage de complétion des documents
     */
    public function documentsCompletionPercentage(): int
    {
        $total = $this->totalRequiredDocumentsCount();
        if ($total === 0) return 100;

        $verified = $this->verifiedDocumentsCount();
        return (int) round(($verified / $total) * 100);
    }

    /**
     * Liste des documents avec leur statut
     */
    public function getDocumentsStatus(): array
    {
        $requiredDocs = $this->requiredDocuments();
        $userDocs = $this->documentUsers()->with('typeDoc')->get()->keyBy('typedoc_id');

        return $requiredDocs->map(function ($typeDoc) use ($userDocs) {
            $userDoc = $userDocs->get($typeDoc->id);

            return [
                'type_doc' => $typeDoc,
                'status' => $userDoc?->status ?? 'missing',
                'is_provided' => !is_null($userDoc),
                'is_verified' => $userDoc?->status === 'verified',
                'file_path' => $userDoc?->file_path,
                'uploaded_at' => $userDoc?->created_at,
            ];
        })->toArray();
    }
}