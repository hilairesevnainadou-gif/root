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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('company_name');

            $table->enum('company_type', [
                'sarl',
                'sa',
                'snc',
                'ei',
                'cooperative',
                'ong',
                'autre',
            ]);

            $table->enum('sector', [
                'agriculture',
                'elevage',
                'peche',
                'industrie',
                'commerce',
                'services',
                'tourisme',
                'batiment',
                'technologie',
                'autre',
            ]);

            $table->string('job_title')->nullable();
            $table->integer('employees_count')->default(0);
            $table->decimal('annual_turnover', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
