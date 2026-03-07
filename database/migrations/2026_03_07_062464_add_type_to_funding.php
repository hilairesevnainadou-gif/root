<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('user_id')
                  ->constrained('companies')->nullOnDelete();

            // Index pour optimiser les recherches
            $table->index(['user_id', 'company_id', 'typedoc_id']);
        });
    }

    public function down(): void
    {
        Schema::table('document_users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->dropIndex(['user_id', 'company_id', 'typedoc_id']);
        });
    }
};
