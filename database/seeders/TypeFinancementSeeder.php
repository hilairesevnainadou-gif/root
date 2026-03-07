<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeFinancement;

class TypeFinancementSeeder extends Seeder
{
    public function run(): void
    {
        TypeFinancement::insert([

            // SR-Standard : Option libre où l'utilisateur choisit son montant quotidien
            [
                'name' => 'SR Standard',
                'description' => 'Subvention standard accessible à tous. L\'utilisateur choisit le montant quotidien souhaité (max 100.000F/jour)',
                'typeusers' => 'particulier',
                'code' => 'SR',
                'amount' => null, // Null car montant variable choisi par user
                'daily_gain' => null, // Null car défini par l'utilisateur (max 100000)
                'max_daily_amount' => 100000, // Plafond de 100.000F par jour
                'registration_fee' => 7225,
                'registration_final_fee' => 27000,
                'duration_months' => 6,
                'is_variable_amount' => true, // Flag pour indiquer montant variable
                'required_documents' => json_encode([
                    'CNI',
                    'Photo identité',
                    'Justificatif de domicile'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // SF1 : Gain fixe de 700F/jour
            [
                'name' => 'Subvention Finance 1',
                'description' => 'Financement avec gain journalier fixe de 700F. Total: 126.000F sur 6 mois',
                'typeusers' => 'particulier',
                'code' => 'SF1',
                'amount' => 126000, // Gain total sur 6 mois
                'daily_gain' => 700, // 700F par jour
                'max_daily_amount' => null,
                'registration_fee' => 7225,
                'registration_final_fee' => 27000,
                'duration_months' => 6,
                'is_variable_amount' => false,
                'required_documents' => json_encode([
                    'CNI',
                    'Photo identité'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // SF2 : Gain fixe de 2.500F/jour
            [
                'name' => 'Subvention Finance 2',
                'description' => 'Financement avec gain journalier fixe de 2.500F. Total: 450.000F sur 6 mois',
                'typeusers' => 'particulier',
                'code' => 'SF2',
                'amount' => 450000, // Gain total sur 6 mois
                'daily_gain' => 2500, // 2.500F par jour
                'max_daily_amount' => null,
                'registration_fee' => 27777,
                'registration_final_fee' => 27000,
                'duration_months' => 6,
                'is_variable_amount' => false,
                'required_documents' => json_encode([
                    'CNI',
                    'Photo identité',
                    'Justificatif de domicile'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // SF3 : Gain fixe de 5.000F/jour
            [
                'name' => 'Subvention Finance 3',
                'description' => 'Financement avec gain journalier fixe de 5.000F. Total: 900.000F sur 6 mois',
                'typeusers' => 'particulier',
                'code' => 'SF3',
                'amount' => 900000, // Gain total sur 6 mois
                'daily_gain' => 5000, // 5.000F par jour
                'max_daily_amount' => null,
                'registration_fee' => 52777,
                'registration_final_fee' => 27000,
                'duration_months' => 6,
                'is_variable_amount' => false,
                'required_documents' => json_encode([
                    'CNI',
                    'Photo identité',
                    'Justificatif de domicile',
                    'Relevé bancaire'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

        ]);
    }
}
