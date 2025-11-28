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
        Schema::create('siaga_keluars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            
            // START: KOLOM BARU UNTUK NOMOR UNIT DAN NAMA LENGKAP
            $table->integer('nomor_unit')->nullable(); // Menyimpan nomor 1 s/d 50
            $table->string('nama_material_lengkap')->nullable(); // Menyimpan hasil gabungan nama material + nomor unit
            // END: KOLOM BARU
            
            $table->string('nama_petugas');
            $table->string('stand_meter')->nullable();
            
            $table->integer('jumlah_siaga_keluar')->default(0);
            $table->integer('jumlah_siaga_masuk')->default(0); 
            
            $table->string('status')->default('Keluar');
            $table->dateTime('tanggal');
            $table->string('foto_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siaga_keluars');
    }
};