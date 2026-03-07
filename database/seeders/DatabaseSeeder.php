<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\TypeDocSeeder;
use Database\Seeders\TypeFinancementSeeder;
use Database\Seeders\TypeFinancementTypeDocSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TypeDocSeeder::class,
            TypeFinancementSeeder::class,
            TypeFinancementTypeDocSeeder::class,
            UserSeeder::class,
        ]);
    }
}
