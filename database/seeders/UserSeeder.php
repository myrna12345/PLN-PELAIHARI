<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. User Admin
        // Menggunakan firstOrCreate: Cek email dulu. Jika ada, skip. Jika belum, buat baru.
        User::firstOrCreate(
            ['email' => 'adm.plnpelaihari@gmail.com'], // Kunci pengecekan (unik)
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => bcrypt('passwordadmin01')
            ]
        );

        // 2. User Satpam
        User::firstOrCreate(
            ['email' => 'satpam.plnpelaihari@gmail.com'], 
            [
                'name' => 'Satpam',
                'role' => 'satpam',
                'password' => bcrypt('satpampln')
            ]
        );

        // 3. User Gudang
        User::firstOrCreate(
            ['email' => 'gudang.plnpelaihari@gmail.com'], 
            [
                'name' => 'Gudang',
                'role' => 'gudang',
                'password' => bcrypt('gudangpelaihari')
            ]
        );
    }
}