<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siaga_kembalis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            
            $table->string('nomor_meter')->nullable(); 
            $table->string('nama_material_lengkap')->nullable(); 
            $table->string('nama_petugas');
            $table->string('stand_meter')->nullable();
            
            $table->string('keterangan')->nullable(); 
            
            $table->string('status')->nullable(); 
            $table->dateTime('tanggal');
            
            $table->string('foto_path')->nullable(); // Foto Material
            $table->string('foto_petugas')->nullable(); // Foto Petugas (DITAMBAHKAN KEMBALI)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siaga_kembalis');
    }
};