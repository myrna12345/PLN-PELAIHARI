<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('nama_material');
            $table->string('kode_material')->unique()->nullable();
            $table->string('satuan')->nullable();
            
            // Saya tambahkan kembali kolom 'kategori' karena kode Anda sebelumnya
            // (SiagaKembaliController, MaterialSeeder, dll) SANGAT BERGANTUNG padanya.
            // Jika dihapus, filter "hanya tampil di halaman Siaga" TIDAK AKAN JALAN dan akan ERROR lagi.
            $table->string('kategori')->default('umum'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};