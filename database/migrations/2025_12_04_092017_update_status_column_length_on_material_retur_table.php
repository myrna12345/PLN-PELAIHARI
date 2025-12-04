<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('material_retur', function (Blueprint $table) {
            // Kita ubah kolom status menjadi VARCHAR(20) agar cukup menampung 'bekas_andal' (9 karakter).
            $table->string('status', 20)->change(); 
        });
    }

    /**
     * Kembalikan (rollback) migrasi.
     */
    public function down(): void
    {
        Schema::table('material_retur', function (Blueprint $table) {
            // Jika rollback, kembalikan ke panjang sebelumnya (asumsi 10 karakter)
            $table->string('status', 10)->change(); 
        });
    }
};