<?php
// app/Models/Wallet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',             // 'user', 'system', 'commission'
        'wallet_number',    // BHDM-WALLET-XXXXXXXX
        'balance',
        'currency',         // XOF, EUR, USD
        'status',           // active, suspended, closed
        'activated_at',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'activated_at' => 'datetime',
        'last_transaction_at' => 'datetime',
    ];

    /**
     * Le propriétaire du portefeuille
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les transactions du portefeuille
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope: Wallets système
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Scope: Wallets utilisateur
     */
    public function scopeUser($query)
    {
        return $query->where('type', 'user');
    }

    /**
     * Créer un wallet pour un utilisateur (méthode manquante)
     */
    public static function createForUser(int $userId, string $currency = 'XOF'): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'user',
            'wallet_number' => self::generateWalletNumber(),
            'balance' => 0.00,
            'currency' => $currency,
            'status' => 'active',
            'activated_at' => now(),
            'last_transaction_at' => null,
        ]);
    }

    /**
     * Créditer le portefeuille
     */
    public function credit(float $amount): void
    {
        $this->increment('balance', $amount);
        $this->update(['last_transaction_at' => now()]);
    }

    /**
     * Débiter le portefeuille
     */
    public function debit(float $amount): bool
    {
        if ($this->balance < $amount) {
            return false;
        }
        $this->decrement('balance', $amount);
        $this->update(['last_transaction_at' => now()]);
        return true;
    }

    /**
     * Générer un numéro de wallet unique
     */
    public static function generateWalletNumber(): string
    {
        $prefix = 'BHDM-WALLET';
        $date = now()->format('Ymd');
        $random = strtoupper(\Illuminate\Support\Str::random(6));

        return "{$prefix}-{$date}-{$random}";
    }
}
