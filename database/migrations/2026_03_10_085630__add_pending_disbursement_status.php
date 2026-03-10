<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajoute 'pending_disbursement' au statut des funding_requests.
     *
     * POURQUOI : Nouveau statut intermédiaire entre 'approved' et 'funded'.
     * Signifie : "les frais de dossier ont été réglés par le client,
     *             l'admin doit valider le versement du financement."
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `funding_requests`
            MODIFY COLUMN `status`
            ENUM(
                'draft','submitted','under_review','pending_committee',
                'approved','pending_disbursement','funded','completed',
                'rejected','cancelled'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        // ATTENTION : les lignes avec status='pending_disbursement' seront corrompues
        DB::statement("
            ALTER TABLE `funding_requests`
            MODIFY COLUMN `status`
            ENUM(
                'draft','submitted','under_review','pending_committee',
                'approved','funded','completed','rejected','cancelled'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};

