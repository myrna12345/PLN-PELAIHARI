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
    $table->string('nama_material');
    $table->string('nama_petugas');
    $table->string('jumlah_material');
    $table->dateTime('tanggal');
    $table->string('foto')->nullable();
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('material_keluar');
    }
};
