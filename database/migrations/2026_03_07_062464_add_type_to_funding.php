<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_users', function (Blueprint $table) {

            if (!Schema::hasColumn('document_users', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('user_id')
                      ->constrained('companies')->nullOnDelete();
            }

            // créer l'index seulement s'il n'existe pas
            $table->index(['user_id', 'company_id', 'typedoc_id'], 'doc_user_company_type_index');
        });
    }

    public function down(): void
    {
        Schema::table('document_users', function (Blueprint $table) {
            $table->dropIndex('doc_user_company_type_index');
        });
    }
};