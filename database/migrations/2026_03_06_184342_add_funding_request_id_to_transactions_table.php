<?php
// database/migrations/xxxx_xx_xx_add_funding_request_id_to_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('funding_request_id')
                ->nullable()
                ->after('wallet_id')
                ->constrained('funding_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['funding_request_id']);
            $table->dropColumn('funding_request_id');
        });
    }
};
