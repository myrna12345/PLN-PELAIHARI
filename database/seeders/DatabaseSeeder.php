<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil UserSeeder yang baru kita buat
        $this->call(UserSeeder::class);

        // Panggil Seeder Material yang sudah ada sebelumnya
        // (Pastikan baris ini ada agar data material ikut ter-generate)
        $this->call([
            MaterialSeeder::class,
            MaterialSiagaSeeder::class,
        ]);
    }
}