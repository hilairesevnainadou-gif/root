<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_users', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('funding_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('typedoc_id')->constrained('typedocs')->onDelete('cascade');

            // Informations du document fourni
            $table->string('file_path'); // Chemin du fichier stocké
            $table->string('file_name'); // Nom original du fichier
            $table->string('file_type'); // Extension/type MIME
            $table->integer('file_size'); // Taille en bytes

            // Validation et statut
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable(); // Si rejeté, pourquoi
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // Métadonnées
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Données supplémentaires (OCR, etc.)

            $table->timestamps();

            // Index pour performance
            $table->index(['user_id', 'funding_request_id']);
            $table->index(['funding_request_id', 'typedoc_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_users');
    }
};
