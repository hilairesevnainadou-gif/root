<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_photo')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('address')->nullable();
            $table->string('city');

            // Entreprise
            $table->string('company_name')->nullable();
            $table->enum('company_type', ['sarl','sa','snc','ei','cooperative','ong','autre'])->nullable();
            $table->enum('sector', ['agriculture','elevage','peche','industrie','commerce','services','tourisme','batiment','technologie','autre'])->nullable();
            $table->string('job_title')->nullable();
            $table->integer('employees_count')->default(0);
            $table->decimal('annual_turnover', 15, 2)->default(0);

            // Membre
            $table->string('member_id')->unique()->nullable();
            $table->date('member_since')->nullable();
            $table->enum('member_status', ['pending','active','suspended','inactive'])->default('pending');
            $table->enum('member_type', ['particulier','entreprise','admin'])->default('particulier');

            // ✅ COLONNE MANQUANTE — requise par AuthController
            $table->enum('preferred_verification_method', ['email','sms'])->default('email');

            // Statuts
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_moderator')->default(false);

            // Connexion
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->text('last_login_device')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
