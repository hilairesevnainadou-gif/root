<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute :
     *  - final_fee_paid       (boolean) sur funding_requests
     *  - final_fee_paid_at    (timestamp nullable) sur funding_requests
     *  - 'pending_disbursement' dans le ENUM status de funding_requests
     *
     * POURQUOI :
     *  Ces colonnes étaient absentes du $fillable et des $casts du modèle
     *  FundingRequest, ce qui provoquait l'erreur silencieuse :
     *  "Les frais de dossier n'ont pas encore été réglés" même après paiement.
     *  Laravel ignorait le update(['final_fee_paid' => true]) car le champ
     *  n'était pas dans $fillable (mass assignment protection).
     */
    public function up(): void
    {
        Schema::table('funding_requests', function (Blueprint $table) {
            // Colonnes frais de dossier finals
            $table->boolean('final_fee_paid')->default(false)->after('funded_at');
            $table->timestamp('final_fee_paid_at')->nullable()->after('final_fee_paid');
        });

        // Ajouter 'pending_disbursement' dans le ENUM status
        // MySQL nécessite de redéfinir la liste complète
        DB::statement("
            ALTER TABLE `funding_requests`
            MODIFY COLUMN `status`
            ENUM(
                'draft',
                'submitted',
                'under_review',
                'pending_committee',
                'approved',
                'pending_disbursement',
                'funded',
                'rejected',
                'cancelled',
                'completed'
            )
            NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        // Remettre l'ENUM sans pending_disbursement
        DB::statement("
            ALTER TABLE `funding_requests`
            MODIFY COLUMN `status`
            ENUM(
                'draft',
                'submitted',
                'under_review',
                'pending_committee',
                'approved',
                'funded',
                'rejected',
                'cancelled',
                'completed'
            )
            NOT NULL DEFAULT 'draft'
        ");

        Schema::table('funding_requests', function (Blueprint $table) {
            $table->dropColumn(['final_fee_paid', 'final_fee_paid_at']);
        });
    }
};
