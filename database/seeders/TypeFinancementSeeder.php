<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeFinancement;

class TypeFinancementSeeder extends Seeder
{
    public function run(): void
    {
        TypeFinancement::insert([

            // ==================== FINANCEMENTS PARTICULIERS ====================

            // SR-Standard : Option libre où l'utilisateur choisit son montant quotidien
            [
                'name' => 'SR Standard',
                'description' => 'Subvention standard accessible à tous. L\'utilisateur choisit le montant quotidien souhaité (max 100.000F/jour)',
                'typeusers' => 'particulier',
                'code' => 'SR',
                'amount' => null,
                'daily_gain' => null,
                'max_daily_amount' => 100000,
                'registration_fee' => 7225,
                'registration_final_fee' => 27000,
                'duration_months' => 6,
                'is_variable_amount' => true,
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
                'amount' => 126000,
                'daily_gain' => 700,
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
                'amount' => 450000,
                'daily_gain' => 2500,
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
                'amount' => 900000,
                'daily_gain' => 5000,
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

            // ==================== FINANCEMENTS ENTREPRISES ====================

            // SE-Standard : Subvention entreprise flexible
            [
                'name' => 'SE Standard Entreprise',
                'description' => 'Subvention standard pour entreprises. Montant quotidien variable selon les besoins (max 500.000F/jour)',
                'typeusers' => 'entreprise',
                'code' => 'SE',
                'amount' => null,
                'daily_gain' => null,
                'max_daily_amount' => 500000,
                'registration_fee' => 50000,
                'registration_final_fee' => 100000,
                'duration_months' => 12,
                'is_variable_amount' => true,
                'required_documents' => json_encode([
                    'RCCM',
                    'DNI du représentant légal',
                    'Photo du représentant légal',
                    'Attestation de localisation'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // EF1 : Financement entreprise niveau 1
            [
                'name' => 'Entreprise Finance 1',
                'description' => 'Financement entreprise avec gain fixe de 10.000F/jour. Total: 3.600.000F sur 12 mois',
                'typeusers' => 'entreprise',
                'code' => 'EF1',
                'amount' => 3600000,
                'daily_gain' => 10000,
                'max_daily_amount' => null,
                'registration_fee' => 50000,
                'registration_final_fee' => 100000,
                'duration_months' => 12,
                'is_variable_amount' => false,
                'required_documents' => json_encode([
                    'RCCM',
                    'DNI du représentant légal',
                    'Photo du représentant légal',
                    'Attestation de localisation',
                    'Compte bancaire entreprise'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // EF2 : Financement entreprise niveau 2
            [
                'name' => 'Entreprise Finance 2',
                'description' => 'Financement entreprise avec gain fixe de 25.000F/jour. Total: 9.000.000F sur 12 mois',
                'typeusers' => 'entreprise',
                'code' => 'EF2',
                'amount' => 9000000,
                'daily_gain' => 25000,
                'max_daily_amount' => null,
                'registration_fee' => 100000,
                'registration_final_fee' => 200000,
                'duration_months' => 12,
                'is_variable_amount' => false,
                'required_documents' => json_encode([
                    'RCCM',
                    'DNI du représentant légal',
                    'Photo du représentant légal',
                    'Attestation de localisation',
                    'Compte bancaire entreprise',
                    'Attestation fiscale'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // EF3 : Financement entreprise niveau 3 (Premium)
            [
                'name' => 'Entreprise Finance 3',
                'description' => 'Financement entreprise premium avec gain fixe de 50.000F/jour. Total: 18.000.000F sur 12 mois',
                'typeusers' => 'entreprise',
                'code' => 'EF3',
                'amount' => 18000000,
                'daily_gain' => 50000,
                'max_daily_amount' => null,
                'registration_fee' => 200000,
                'registration_final_fee' => 400000,
                'duration_months' => 12,
                'is_variable_amount' => false,
                'required_documents' => json_encode([
                    'RCCM',
                    'DNI du représentant légal',
                    'Photo du représentant légal',
                    'Attestation de localisation',
                    'Compte bancaire entreprise',
                    'Attestation fiscale',
                    'Plan d\'affaires'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // EF4 : Financement entreprise niveau 4 (Elite)
            [
                'name' => 'Entreprise Finance 4',
                'description' => 'Financement entreprise élite avec gain fixe de 100.000F/jour. Total: 36.000.000F sur 12 mois',
                'typeusers' => 'entreprise',
                'code' => 'EF4',
                'amount' => 36000000,
                'daily_gain' => 100000,
                'max_daily_amount' => null,
                'registration_fee' => 500000,
                'registration_final_fee' => 1000000,
                'duration_months' => 12,
                'is_variable_amount' => false,
                'required_documents' => json_encode([
                    'RCCM',
                    'DNI du représentant légal',
                    'Photo du représentant légal',
                    'Attestation de localisation',
                    'Compte bancaire entreprise',
                    'Attestation fiscale',
                    'Plan d\'affaires'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

        ]);
    }
}