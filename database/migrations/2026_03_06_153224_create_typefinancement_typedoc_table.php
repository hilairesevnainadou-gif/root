<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('typefinancement_typedoc', function (Blueprint $table) {
    $table->id();

    $table->foreignId('typefinancement_id')
        ->constrained('typefinanciements')
        ->cascadeOnDelete();

    $table->foreignId('typedoc_id')
        ->constrained('typedocs')
        ->cascadeOnDelete();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('typefinancement_typedoc');
    }
};
