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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('typefinancement_id')->constrained('typefinanciements');

            $table->string('request_number')->unique();
            $table->string('title');
            $table->decimal('amount_requested', 15, 2);
            $table->decimal('amount_approved', 15, 2)->nullable();
            $table->decimal('amount_rembursed', 15, 2)->default(0);
            $table->integer('duration');
            $table->text('description')->nullable();

            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'pending_committee',
                'approved',
                'rejected',
                'funded',
                'completed',
                'cancelled'
            ])->default('draft');

            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('kkiapay_transaction_id')->nullable();
            $table->decimal('registration_fee_paid', 15, 2)->default(0);

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('committee_review_started_at')->nullable();
            $table->timestamp('committee_decision_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'typefinancement_id']);
            $table->index(['company_id', 'status']);
            $table->index('request_number');
            $table->index(['status', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_requests');
    }
};
