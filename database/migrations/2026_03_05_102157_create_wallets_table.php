<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Informations du portefeuille
            $table->string('wallet_number')->unique(); // BHDM-WALLET-XXXXXXXX
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('currency', ['XOF', 'EUR', 'USD'])->default('XOF');
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            // Métadonnées
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_transaction_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
