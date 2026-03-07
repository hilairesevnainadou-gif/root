<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funding_requests', function (Blueprint $table) {
            // Si ces champs n'existent pas déjà
            if (!Schema::hasColumn('funding_requests', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('status');
            }
            if (!Schema::hasColumn('funding_requests', 'kkiapay_transaction_id')) {
                $table->string('kkiapay_transaction_id')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('funding_requests', 'registration_fee_paid')) {
                $table->decimal('registration_fee_paid', 12, 2)->default(0)->after('kkiapay_transaction_id');
            }
            if (!Schema::hasColumn('funding_requests', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('registration_fee_paid');
            }
            if (!Schema::hasColumn('funding_requests', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('paid_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funding_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'kkiapay_transaction_id', 'registration_fee_paid', 'paid_at', 'submitted_at']);
        });
    }
};
