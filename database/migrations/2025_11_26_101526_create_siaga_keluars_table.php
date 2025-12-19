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
            
            // START: PERBAIKAN KOLOM NOMOR UNIT -> NOMOR METER
            $table->string('nomor_meter')->nullable(); 
            // END: PERBAIKAN KOLOM
            
            $table->string('nama_material_lengkap')->nullable(); 
            
            $table->string('nama_petugas');
            $table->string('stand_meter')->nullable();
            
            // START: TAMBAHAN KOLOM KETERANGAN (WAJIB DIISI)
            // Menggunakan tipe data text karena keterangan cenderung panjang.
            // Tanpa nullable(), ini otomatis NOT NULL (wajib diisi).
            $table->text('keterangan');
            // END: TAMBAHAN KOLOM
            
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