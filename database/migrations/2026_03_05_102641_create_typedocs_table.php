<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('typedocs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('typeusers', ['particulier', 'entreprise', 'admin']);
            $table->timestamps(); // Ajouté
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typedocs');
    }
};
