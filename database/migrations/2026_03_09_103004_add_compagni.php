<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Colonne pour marquer l'entreprise principale
            $table->boolean('is_primary')->default(false)->after('annual_turnover');
            
            // Autres colonnes utiles à ajouter
            $table->string('registration_number')->nullable()->after('is_primary');
            $table->string('tax_id')->nullable()->after('registration_number');
            $table->string('address')->nullable()->after('tax_id');
            $table->string('city')->nullable()->after('address');
            $table->string('company_phone')->nullable()->after('city');
            $table->string('company_email')->nullable()->after('company_phone');
            $table->text('description')->nullable()->after('company_email');
            $table->boolean('is_active')->default(true)->after('description');
            
            // Index pour optimiser les requêtes
            $table->index(['user_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_primary']);
            $table->dropColumn([
                'is_primary',
                'registration_number',
                'tax_id',
                'address',
                'city',
                'company_phone',
                'company_email',
                'description',
                'is_active'
            ]);
        });
    }
};