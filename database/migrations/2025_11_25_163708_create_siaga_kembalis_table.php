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
        Schema::create('siaga_kembalis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            
            // Kolom Nomor Meter (Bukan nomor_unit)
            $table->string('nomor_meter')->nullable(); 
            // Nama Material Lengkap (yang dibutuhkan Controller)
            $table->string('nama_material_lengkap')->nullable(); 

            $table->string('nama_petugas');
            $table->string('stand_meter')->nullable();
            
            // Kolom jumlah_siaga_kembali TELAH DIHAPUS
            
            $table->string('keterangan')->nullable(); 
            
            $table->string('status')->nullable(); // Untuk status seperti "Kembali"
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
        Schema::dropIfExists('siaga_kembalis');
    }
};