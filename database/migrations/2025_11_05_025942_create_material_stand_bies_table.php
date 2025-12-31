<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_stand_by', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials'); 
            $table->integer('jumlah');
            $table->string('satuan'); 
            $table->datetime('tanggal');
            
            // Pastikan kolom ini ada untuk Foto Material
            $table->string('foto_path')->nullable();
            
            // Kolom foto_petugas SUDAH DIHAPUS
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_stand_by');
    }
};