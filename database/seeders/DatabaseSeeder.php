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
        // Panggil MaterialSeeder yang baru kita buat
        $this->call([
            MaterialSeeder::class,
            // Anda bisa tambahkan seeder lain di sini nanti
        ]);
    }
}