<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('typefinanciements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('typeusers', ['particulier', 'entreprise', 'admin']);
            $table->string('code')->unique();
            $table->decimal('amount', 15, 2)->nullable(); // ← CORRIGÉ : nullable() sans change()
            $table->decimal('registration_fee', 15, 2);
            $table->decimal('registration_final_fee', 15, 2);
            $table->integer('duration_months')->default(1);
            $table->json('required_documents')->nullable();
            $table->integer('daily_gain')->nullable();
            $table->integer('max_daily_amount')->nullable();
            $table->boolean('is_variable_amount')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typefinanciements');
    }
};
