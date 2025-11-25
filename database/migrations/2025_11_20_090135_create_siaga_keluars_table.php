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
        // Membuat tabel 'siaga_keluars' dengan semua kolom yang dibutuhkan
        Schema::create('siaga_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('nama_material')->nullable();
            $table->string('nama_petugas');
            $table->string('stand_meter');
            $table->integer('jumlah_siaga_keluar');
            $table->integer('jumlah_siaga_kembali')->nullable(); 
            $table->timestamp('tanggal'); 
            $table->string('foto')->nullable();
            $table->string('status')->default('Selesai'); 

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