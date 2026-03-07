<?php
// database/migrations/xxxx_xx_xx_add_payment_fields_to_funding_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funding_requests', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('status');
            $table->string('kkiapay_transaction_id')->nullable()->after('payment_status');
            $table->decimal('registration_fee_paid', 15, 2)->default(0)->after('kkiapay_transaction_id');
            $table->timestamp('paid_at')->nullable()->after('registration_fee_paid');
        });
    }

    public function down(): void
    {
        Schema::table('funding_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'kkiapay_transaction_id', 'registration_fee_paid', 'paid_at']);
        });
    }
};
