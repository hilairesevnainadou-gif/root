<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('postal_code')->nullable()->after('city');
            $table->string('country')->default('Bénin')->after('postal_code');
            $table->string('id_number')->nullable()->after('country');
            $table->string('id_document_path')->nullable()->after('id_number');
            $table->text('bio')->nullable()->after('id_document_path');
            $table->string('locale')->default('fr')->after('last_login_device');
            $table->string('timezone')->default('Africa/Porto-Novo')->after('locale');
            $table->json('preferences')->nullable()->after('timezone');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'postal_code',
                'country',
                'id_number',
                'id_document_path',
                'bio',
                'locale',
                'timezone',
                'preferences',
                'deleted_at'
            ]);
        });
    }
};