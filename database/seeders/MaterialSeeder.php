<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material; // <-- PENTING: Impor model Material Anda

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar material yang Anda berikan
        $materials = [
            'MCB 1P 2A', 'MCB 1P 4A', 'MCB 1P 6A', 'MCB 1P 10A', 'MCB 1P 16A',
            'MCB 1P 35A', 'MCB 1P 50A', 'MCB 3P 16A', 'MCB 3P 20A', 'MCB 3P 25A',
            'MCB 3P 35A', 'MCB 3P 50A', 'MCB 3P 63A', 'KWH METER PASCABAYAR',
            'KWH METER PRABAYAR', 'KABEL SR', 'KABEL TR 4 X 16',
            'KABEL TR 4 X 35', 'KABEL TR 4X 70'
        ];

        // Loop dan masukkan setiap material ke database
        foreach ($materials as $materialName) {
            // Menggunakan firstOrCreate agar data tidak duplikat jika seeder dijalankan lagi
            Material::firstOrCreate(['nama_material' => $materialName]);
        }
        
        // Pesan sukses di terminal
        $this->command->info('Tabel materials berhasil diisi (seeded).');
    }
}