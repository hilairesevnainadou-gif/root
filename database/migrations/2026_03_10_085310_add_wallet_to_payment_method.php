<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajoute 'wallet' à la colonne `payment_method` de la table `transactions`.
     *
     * POURQUOI : MySQL rejette les valeurs hors ENUM.
     * La colonne acceptait : wave, orange_money, free_money, card, bank_transfer,
     *                        mobile_money, kkiapay, admin_adjustment
     * Il faut y ajouter 'wallet' pour les paiements depuis le solde interne.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `transactions`
            MODIFY COLUMN `payment_method`
            ENUM(
                'wave','orange_money','free_money','card',
                'bank_transfer','mobile_money','kkiapay',
                'admin_adjustment','wallet'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        // ATTENTION : les lignes avec payment_method='wallet' seront corrompues
        DB::statement("
            ALTER TABLE `transactions`
            MODIFY COLUMN `payment_method`
            ENUM(
                'wave','orange_money','free_money','card',
                'bank_transfer','mobile_money','kkiapay',
                'admin_adjustment'
            ) NOT NULL
        ");
    }
};
