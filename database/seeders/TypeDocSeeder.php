<?php

namespace Database\Seeders;

use App\Models\TypeDoc;
use Illuminate\Database\Seeder;

class TypeDocSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeDoc::insert([
            // Documents pour particuliers
            [
                'name' => 'Carte nationale d\'identité',
                'typeusers' => 'particulier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Photo identité',
                'typeusers' => 'particulier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Justificatif de domicile',
                'typeusers' => 'particulier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Relevé bancaire',
                'typeusers' => 'particulier',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Documents pour entreprises
            [
                'name' => 'RCCM (Registre du Commerce)',
                'typeusers' => 'entreprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DNI du représentant légal',
                'typeusers' => 'entreprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Photo du représentant légal',
                'typeusers' => 'entreprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Attestation de localisation',
                'typeusers' => 'entreprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Compte bancaire entreprise',
                'typeusers' => 'entreprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Attestation fiscale',
                'typeusers' => 'entreprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Plan d\'affaires',
                'typeusers' => 'entreprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}