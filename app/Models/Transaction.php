<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'funding_request_id',
        'transaction_id',
        'reference',
        'type',
        'amount',
        'fee',
        'total_amount',
        'payment_method',
        'status',
        'description',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function fundingRequest(): BelongsTo
    {
        return $this->belongsTo(FundingRequest::class, 'funding_request_id');
    }

    /**
     * Relation utilisateur via le wallet (corrigé)
     */
    public function user(): ?BelongsTo
    {
        return $this->wallet?->user();
    }

    public function markAsCompleted(?string $reference = null): bool
    {
        $data = [
            'status' => 'completed',
            'completed_at' => now(),
        ];

        if ($reference) {
            $data['reference'] = $reference;
        }

        return $this->update($data);
    }

    public function markAsFailed(string $reason = ''): bool
    {
        return $this->update([
            'status' => 'failed',
            'metadata' => array_merge($this->metadata ?? [], [
                'failure_reason' => $reason,
                'failed_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    public function scopeForFundingRequest($query, int $fundingRequestId)
    {
        return $query->where('funding_request_id', $fundingRequestId);
    }

    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
