<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_keluar', function (Blueprint $table) {
    $table->id();
    $table->foreignId('material_id')->constrained('materials'); 
    $table->string('nama_petugas');
    $table->string('jumlah_material');
    $table->dateTime('tanggal');
    $table->text('keterangan');
    $table->string('foto')->nullable();
    $table->string('foto_petugas')->nullable();
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('material_keluar');
    }
};
