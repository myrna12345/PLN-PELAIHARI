<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_retur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials'); 
            $table->string('nama_petugas'); 
            $table->integer('jumlah');
            $table->string('satuan'); // Kolom satuan
            $table->datetime('tanggal');
            $table->enum('status', ['bekas_andal', 'rusak', 'baik']); 
            $table->text('keterangan'); // Keterangan wajib
            $table->string('foto_path')->nullable(); 
            $table->string('foto_petugas')->nullable(); // Foto petugas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_retur');
    }
};