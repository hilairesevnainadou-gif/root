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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('funding_request_id')->nullable()->constrained('funding_requests')->cascadeOnDelete();
            $table->foreignId('typedoc_id')->constrained('typedocs');

            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->default(0);

            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['user_id', 'funding_request_id']);
            $table->index(['user_id', 'company_id', 'typedoc_id']);
            $table->index(['funding_request_id', 'status']);
            $table->index(['typedoc_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_users');
    }
};
