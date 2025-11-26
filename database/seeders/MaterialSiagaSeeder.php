<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSiagaSeeder extends Seeder
{
    public function run(): void
    {
        // KITA TIDAK PAKAI TRUNCATE DI SINI
        // Agar data dari MaterialSeeder (umum) tidak terhapus.

        $materials = [];

        // Opsi 1P -> 1 sampai 50
        for ($i = 1; $i <= 50; $i++) {
            $materials[] = [
                'nama_material' => "1P $i",
                'kategori' => 'siaga', // Tanda KHUSUS
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Opsi 3P -> 1 sampai 10
        for ($i = 1; $i <= 10; $i++) {
            $materials[] = [
                'nama_material' => "3P $i",
                'kategori' => 'siaga', // Tanda KHUSUS
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Masukkan data siaga
        Material::insert($materials);
    }
}