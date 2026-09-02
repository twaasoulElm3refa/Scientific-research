<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            userSeeder::class,
            CategorySeeder::class,
            CountrySeeder::class,
            LanguageSeeder::class,
            DocumentTypeSeeder::class,
            SubcategorySeeder::class,
            SpecializationSeeder::class,
            SourceSeeder::class,
            MagazineSeeder::class,
        ]);
    }
}
