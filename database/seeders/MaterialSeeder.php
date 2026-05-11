<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar material umum/teknik (BUKAN Siaga)
        $materials = [
            'MCB 1P 2A', 'MCB 1P 4A', 'MCB 1P 6A', 'MCB 1P 10A', 'MCB 1P 16A',
            'MCB 1P 20A', 'MCB 1P 25A', 
            'MCB 1P 35A', 'MCB 1P 50A', 
            'MCB 3P 10A', 
            'MCB 3P 16A', 'MCB 3P 20A', 'MCB 3P 25A',
            'MCB 3P 35A', 'MCB 3P 50A', 'MCB 3P 63A', 
            'KWH METER PASCABAYAR',
            'KWH METER PRABAYAR', 'KABEL SR', 
            // 'KABEL TR 4 X 16', DIHAPUS
            'KABEL TR 16 mm',
            'KABEL TR 35 mm', // Diganti dari KABEL TR 4 X 35
            'KABEL TR 70 mm',  // Diganti dari KABEL TR 4 X 70
        ];

        foreach ($materials as $materialName) {
            // Tambahkan ['kategori' => 'teknik'] sebagai nilai default
            Material::firstOrCreate(
                ['nama_material' => $materialName], 
                ['kategori' => 'teknik'] 
            );
        }
        
        $this->command->info('Tabel materials berhasil diisi dengan kategori teknik.');
    }
}