<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSiagaSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            [
                'nama_material' => "KWH Siaga 1P",
                'kategori' => 'siaga', // Tanda KHUSUS
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_material' => "KWH Siaga 3P",
                'kategori' => 'siaga', // Tanda KHUSUS
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Masukkan atau update data siaga
        foreach ($materials as $material) {
            // Gunakan updateOrCreate untuk menghindari duplikasi jika dijalankan berulang kali
            // dan untuk mengganti item 1P 1, 1P 2, dst. sebelumnya.
            Material::updateOrCreate(
                ['nama_material' => $material['nama_material'], 'kategori' => 'siaga'],
                $material
            );
        }
    }
}