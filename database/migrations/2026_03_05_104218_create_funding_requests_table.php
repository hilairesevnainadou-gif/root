<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Dans la migration create_funding_requests_table
$table->foreignId('typefinancement_id')
      ->constrained('typefinanciements')
      ->onDelete('cascade');

            // Identifiants
            $table->string('request_number')->unique(); // BHDM-REQ-YYYYMMDD-XXXX
            $table->string('title');

            // Financement
            $table->decimal('amount_requested', 15, 2);
            $table->decimal('amount_approved', 15, 2)->nullable();
            $table->decimal('amount_rembursed', 15, 2)->nullable();
            $table->integer('duration')->comment('Durée en mois');

            // Description (selon les 5 clés)
            $table->text('description')->nullable();
            // Statuts
            $table->enum('status', [
                'draft',
                'pending',
                'submitted',
                'under_review',
                'pending_committee',
                'approved',
                'rejected',
                'funded',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('draft');


            // Dates importantes
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('committee_review_started_at')->nullable();
            $table->timestamp('committee_decision_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('funded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_requests');
    }
};
