<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_siaga_standbies', function (Blueprint $table) {
            
            $table->id();

            // Kolom yang Anda minta
            $table->string('nama_material', 150);
            $table->string('nomor_meter', 50); // Dibuat TIDAK nullable (required) sesuai validasi Anda
            $table->string('stand_meter', 50); 
            $table->dateTime('tanggal');       // TIDAK nullable (required)
            
            // Menggunakan nama kolom 'unggah_foto' sesuai input file di form Anda
            $table->string('unggah_foto')->nullable(); 
            
            $table->enum('status', ['Ready', 'Terpakai'])->default('Ready');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_siaga_standbies');
    }
};