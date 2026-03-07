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
        ]);
    }
}
