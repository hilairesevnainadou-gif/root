<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //  Administrateur système
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'System',
            'name' => 'Administrateur',
            'email' => 'admin@bhdm.com',
            'phone' => '0100000000',
            'password' => Hash::make('admin123456'),
            'city' => 'Abidjan',
            'member_type' => 'admin',
            'member_status' => 'active',
            'is_verified' => true,
            'is_admin' => true,
            'is_moderator' => true,
            'is_active' => true,
            'member_since' => now(),
        ]);

        // Utilisateur particulier (sans entreprise)
        $user1 = User::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'phone' => '0601020304',
            'password' => Hash::make('password123'),
            'city' => 'Paris',
            'member_type' => 'particulier',
            'is_verified' => true,
        ]);

        //  Utilisateur entreprise
        $user2 = User::create([
            'first_name' => 'Marie',
            'last_name' => 'Durand',
            'name' => 'Marie Durand',
            'email' => 'marie.durand@example.com',
            'phone' => '0605060708',
            'password' => Hash::make('password123'),
            'city' => 'Lyon',
            'member_type' => 'entreprise',
            'is_verified' => true,
        ]);

        // Crée l'entreprise pour l'utilisateur entreprise
        Company::create([
            'user_id' => $user2->id,
            'company_name' => 'Durand Technologies',
            'company_type' => 'sa',
            'sector' => 'technologie',
            'job_title' => 'CEO',
            'employees_count' => 50,
            'annual_turnover' => 1200000.50,
        ]);
    }
}