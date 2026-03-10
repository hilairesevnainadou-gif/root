<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute 'final_fee' à la colonne `type` de la table `transactions`.
     *
     * POURQUOI : MySQL rejette les valeurs qui ne figurent pas dans l'ENUM défini.
     * La colonne `type` acceptait : credit, debit, payment, transfer, refund, fee
     * Il faut y ajouter 'final_fee' pour les frais de dossier après approbation.
     */
    public function up(): void
    {
        // MySQL : modifier un ENUM nécessite de redéfinir toute la liste
        DB::statement("
            ALTER TABLE `transactions`
            MODIFY COLUMN `type`
            ENUM('credit','debit','payment','transfer','refund','fee','final_fee','deposit')
            NOT NULL
        ");
    }

    public function down(): void
    {
        // Supprimer 'final_fee' et 'deposit' de l'ENUM
        // ATTENTION : si des lignes ont type='final_fee', elles seront corrompues
        DB::statement("
            ALTER TABLE `transactions`
            MODIFY COLUMN `type`
            ENUM('credit','debit','payment','transfer','refund','fee')
            NOT NULL
        ");
    }
};
