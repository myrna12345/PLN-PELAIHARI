<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// Panggil kedua class seeder
use Database\Seeders\MaterialSeeder;
use Database\Seeders\MaterialSiagaSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Jalankan MaterialSeeder (Ini akan menghapus data lama & isi material umum)
        $this->call(MaterialSeeder::class);
        
        // 2. Jalankan MaterialSiagaSeeder (Ini akan menambahkan material 1P & 3P)
        $this->call(MaterialSiagaSeeder::class);
    }
}