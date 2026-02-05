<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->string('nama_material');
            $table->integer('jumlah');
            $table->string('satuan');
            $table->string('foto_path')->nullable(); // Dibuat nullable agar tidak error jika foto kosong
            $table->timestamp('tanggal_input');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_history');
    }
};