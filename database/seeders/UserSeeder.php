<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. User Admin
        User::firstOrCreate(
            ['email' => 'adm.plnpelaihari@gmail.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'role' => 'admin',
                'password' => Hash::make('passwordadmin01')
            ]
        );

        // 2. User Satpam
        User::firstOrCreate(
            ['email' => 'satpam.plnpelaihari@gmail.com'], 
            [
                'name' => 'Satpam',
                'username' => 'satpam',
                'role' => 'satpam',
                'password' => Hash::make('satpampln')
            ]
        );

        // 3. User Gudang
        User::firstOrCreate(
            ['email' => 'gudang.plnpelaihari@gmail.com'], 
            [
                'name' => 'Gudang',
                'username' => 'gudang',
                'role' => 'gudang',
                'password' => Hash::make('gudangpelaihari')
            ]
        );

        // 4. User Harmet (Baru)
        User::firstOrCreate(
            ['email' => 'harmet.plnpelaihari@gmail.com'], 
            [
                'name' => 'harmet',
                'username' => 'harmet',
                'role' => 'harmet',
                'password' => Hash::make('harmetplnplh')
            ]
        );
    }
}