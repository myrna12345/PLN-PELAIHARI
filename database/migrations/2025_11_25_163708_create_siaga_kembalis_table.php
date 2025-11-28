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
            
            // START: PENAMBAHAN KOLOM BARU (NOMOR UNIT DAN NAMA LENGKAP)
            $table->integer('nomor_unit')->nullable(); // Kolom baru harus diisi
            $table->string('nama_material_lengkap')->nullable(); // Kolom baru harus diisi
            // END: PENAMBAHAN KOLOM BARU

            $table->string('nama_petugas');
            $table->string('stand_meter')->nullable();
            
            // PERUBAHAN: Mengganti 'jumlah' menjadi 'jumlah_siaga_kembali'
            $table->integer('jumlah_siaga_kembali')->default(0);
            
            // PERBAIKAN: Mengubah keterangan menjadi status karena sudah ada kolom status di bawah
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