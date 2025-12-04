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
        Schema::create('material_stand_by', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials'); 
            $table->string('nama_petugas');
            $table->integer('jumlah');
            
            // --- TAMBAHKAN KOLOM 'satuan' DI SINI ---
            $table->string('satuan'); // Atau $table->string('satuan')->nullable(); jika boleh kosong
            // ----------------------------------------
            
            $table->datetime('tanggal');
            $table->string('foto_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_stand_by');
    }
};