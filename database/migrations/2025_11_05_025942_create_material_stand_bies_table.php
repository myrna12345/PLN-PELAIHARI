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
            $table->string('foto_path')->nullable();
            $table->string('foto_petugas')->nullable(); // Ditambahkan sebagai pengganti nama_petugas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_stand_by');
    }
};