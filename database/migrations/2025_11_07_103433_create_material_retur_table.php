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
        Schema::create('material_retur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials'); 
            $table->string('nama_petugas');
            $table->integer('jumlah');
            $table->datetime('tanggal');
            $table->enum('status', ['baik', 'rusak']); 
            
            // --- ATRIBUT BARU (DIISI OTOMATIS NANTI) ---
            $table->integer('material_keluar')->nullable()->default(0);
            $table->integer('material_kembali')->nullable()->default(0);

            $table->text('keterangan')->nullable(); 
            $table->string('foto_path')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_retur');
    }
};