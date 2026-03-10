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
        'company_id',
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
        'completed_at',
        'cancelled_at',
        'rejection_reason',
        'reviewer_id',
        'final_fee_paid',
        'final_fee_paid_at',
    ];

    protected $casts = [
        'amount_requested'            => 'decimal:2',
        'amount_approved'             => 'decimal:2',
        'amount_rembursed'            => 'decimal:2',
        'registration_fee_paid'       => 'decimal:2',
        'paid_at'                     => 'datetime',
        'submitted_at'                => 'datetime',
        'reviewed_at'                 => 'datetime',
        'committee_review_started_at' => 'datetime',
        'committee_decision_at'       => 'datetime',
        'approved_at'                 => 'datetime',
        'funded_at'                   => 'datetime',
        'completed_at'                => 'datetime',
        'cancelled_at'                => 'datetime',
        'final_fee_paid'              => 'boolean',
        'final_fee_paid_at'           => 'datetime',
    ];

    // ========== RELATIONS ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->status !== 'draft';
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function isPendingCommittee(): bool
    {
        return $this->status === 'pending_committee';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isFunded(): bool
    {
        return $this->status === 'funded';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // ========== PAIEMENT ==========

    public function isPaymentPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isPaymentFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    // ========== DOCUMENTS ==========

    /**
     * Récupérer les documents requis pour ce type de financement
     */
    public function requiredDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->typeFinancement?->requiredTypeDocs ?? collect();
    }

    /**
     * Vérifier si tous les documents requis sont fournis et vérifiés
     */
    public function hasAllRequiredDocuments(): bool
    {
        $requiredDocs = $this->requiredDocuments();

        if ($requiredDocs->isEmpty()) {
            return true;
        }

        $providedDocIds = $this->documentUsers()
            ->where('status', 'verified')
            ->pluck('typedoc_id')
            ->toArray();

        foreach ($requiredDocs as $requiredDoc) {
            if (! in_array($requiredDoc->id, $providedDocIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Récupérer les documents manquants
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
            return ! in_array($doc->id, $providedDocIds);
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
     * Nombre total de documents requis
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
     */
    public function createRequiredDocuments(): void
    {
        // Vérifier si déjà créés pour cette demande
        if ($this->documentUsers()->exists()) {
            return;
        }

        $requiredDocs = $this->requiredDocuments();

        if ($requiredDocs->isEmpty()) {
            return;
        }

        $documentsToCreate = [];

        foreach ($requiredDocs as $typeDoc) {
            // CAS 1: Financement entreprise avec company_id
            if ($this->company_id) {
                $existingDoc = DocumentUser::where('user_id', $this->user_id)
                    ->where('typedoc_id', $typeDoc->id)
                    ->where('company_id', $this->company_id)
                    ->where('status', 'verified')
                    ->first();

                if ($existingDoc) {
                    $documentsToCreate[] = $this->buildDocumentData($typeDoc, $existingDoc, 'verified');

                    continue;
                }
            }

            // CAS 2: Documents globaux de l'utilisateur
            $existingDoc = DocumentUser::where('user_id', $this->user_id)
                ->where('typedoc_id', $typeDoc->id)
                ->whereNull('company_id')
                ->whereNull('funding_request_id')
                ->where('status', 'verified')
                ->first();

            if ($existingDoc) {
                $documentsToCreate[] = $this->buildDocumentData($typeDoc, $existingDoc, 'verified');
            } else {
                $documentsToCreate[] = $this->buildEmptyDocumentData($typeDoc);
            }
        }

        if (! empty($documentsToCreate)) {
            DocumentUser::insert($documentsToCreate);
        }
    }

    /**
     * Construire les données d'un document existant
     */
    private function buildDocumentData($typeDoc, $existingDoc, string $status): array
    {
        return [
            'user_id' => $this->user_id,
            'funding_request_id' => $this->id,
            'company_id' => $this->company_id,
            'typedoc_id' => $typeDoc->id,
            'file_path' => $existingDoc->file_path,
            'file_name' => $existingDoc->file_name,
            'file_type' => $existingDoc->file_type,
            'file_size' => $existingDoc->file_size,
            'status' => $status,
            'verified_at' => $existingDoc->verified_at,
            'verified_by' => $existingDoc->verified_by,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Construire les données d'un document vide
     */
    private function buildEmptyDocumentData($typeDoc): array
    {
        return [
            'user_id' => $this->user_id,
            'funding_request_id' => $this->id,
            'company_id' => $this->company_id,
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

    // ========== TRANSITIONS DE STATUT ==========

    /**
     * Soumettre pour examen
     */
    public function submit(): bool
    {
        if (! $this->isDraft() || ! $this->isPaid()) {
            return false;
        }

        return $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Mettre en examen
     */
    public function startReview(): bool
    {
        if (! $this->isSubmitted()) {
            return false;
        }

        return $this->update([
            'status' => 'under_review',
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Envoyer au comité
     */
    public function sendToCommittee(): bool
    {
        if (! $this->isUnderReview()) {
            return false;
        }

        return $this->update([
            'status' => 'pending_committee',
            'committee_review_started_at' => now(),
        ]);
    }

    /**
     * Approuver la demande
     */
    public function approve(?float $approvedAmount = null): bool
    {
        if (! $this->isUnderReview() && ! $this->isPendingCommittee()) {
            return false;
        }

        return $this->update([
            'status' => 'approved',
            'amount_approved' => $approvedAmount ?? $this->amount_requested,
            'approved_at' => now(),
        ]);
    }

    /**
     * Rejeter la demande
     */
    public function reject(string $reason): bool
    {
        if ($this->isFunded() || $this->isCompleted() || $this->isCancelled()) {
            return false;
        }

        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Financer la demande
     */
    public function fund(): bool
    {
        if (! $this->isApproved()) {
            return false;
        }

        return $this->update([
            'status' => 'funded',
            'funded_at' => now(),
        ]);
    }

    /**
     * Marquer comme complétée
     */
    public function complete(): bool
    {
        if (! $this->isFunded()) {
            return false;
        }

        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Annuler la demande
     */
    public function cancel(): bool
    {
        if (! $this->isDraft() && ! $this->isSubmitted()) {
            return false;
        }

        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    // ========== SCOPES ==========

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeOfType($query, int $typeId)
    {
        return $query->where('typefinancement_id', $typeId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopePendingCommittee($query)
    {
        return $query->where('status', 'pending_committee');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeFunded($query)
    {
        return $query->where('status', 'funded');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['rejected', 'cancelled']);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope pour vérifier si une demande similaire existe (même type, même entreprise)
     */
    public function scopeSimilarExists($query, int $userId, int $typeId, ?int $companyId = null)
    {
        $q = $query->where('user_id', $userId)
            ->where('typefinancement_id', $typeId)
            ->whereNotIn('status', ['rejected', 'cancelled', 'completed']);

        if ($companyId) {
            $q->where('company_id', $companyId);
        } else {
            $q->whereNull('company_id');
        }

        return $q;
    }

    // ========== HELPERS ==========

    /**
     * Libellé du statut
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'submitted' => 'Soumise',
            'under_review' => 'En examen',
            'pending_committee' => 'En attente du comité',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
            'funded' => 'Financée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            default => $this->status,
        };
    }

    /**
     * Classe CSS du statut
     */
    public function getStatusClass(): string
    {
        return match ($this->status) {
            'draft' => 'badge-secondary',
            'submitted' => 'badge-info',
            'under_review' => 'badge-warning',
            'pending_committee' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            'funded' => 'badge-primary',
            'completed' => 'badge-success',
            'cancelled' => 'badge-muted',
            default => 'badge-secondary',
        };
    }

    /**
     * Libellé du statut de paiement
     */
    public function getPaymentStatusLabel(): string
    {
        return match ($this->payment_status) {
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
        if ($total === 0) {
            return 100;
        }

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
                'is_provided' => ! is_null($userDoc),
                'is_verified' => $userDoc?->status === 'verified',
                'file_path' => $userDoc?->file_path,
                'uploaded_at' => $userDoc?->created_at,
            ];
        })->toArray();
    }

    /**
     * Montant restant à rembourser
     */
    public function getRemainingAmount(): float
    {
        return max(0, ($this->amount_approved ?? 0) - ($this->amount_rembursed ?? 0));
    }

    /**
     * Progression du remboursement (%)
     */
    public function getRepaymentProgress(): float
    {
        if (empty($this->amount_approved) || $this->amount_approved == 0) {
            return 0;
        }

        return min(100, (($this->amount_rembursed ?? 0) / $this->amount_approved) * 100);
    }

    /**
     * Vérifier si la demande peut être modifiée
     */
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    /**
     * Vérifier si la demande peut être payée
     */
    public function canBePaid(): bool
    {
        return $this->isDraft() && $this->isPaymentPending();
    }

    /**
     * Vérifier si la demande peut être annulée
     */
    public function canBeCancelled(): bool
    {
        return $this->isDraft() || $this->isSubmitted();
    }

    /**
     * Vérifier si des documents peuvent être uploadés
     */
    public function canUploadDocuments(): bool
    {
        return in_array($this->status, ['submitted', 'under_review', 'pending_committee', 'approved']);
    }
}
