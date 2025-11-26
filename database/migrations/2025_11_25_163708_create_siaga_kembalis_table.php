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
            $table->string('nama_petugas');
            $table->string('stand_meter')->nullable();
            
            // PERUBAHAN: Mengganti 'jumlah' menjadi 'jumlah_siaga_kembali'
            $table->integer('jumlah_siaga_kembali')->default(0);
            
            $table->text('keterangan')->nullable();
            $table->string('status')->nullable(); // Untuk status seperti "Kembali"
            $table->dateTime('tanggal');
            $table->string('foto_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siaga_kembalis');
    }
};