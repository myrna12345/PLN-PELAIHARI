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
      Schema::create('material_histories', function (Blueprint $table) {
    $table->id();
    $table->string('nama_material'); // Pastikan namanya 'nama_material'
    $table->integer('jumlah');
    $table->string('satuan');
    $table->string('foto_path');
    $table->dateTime('tanggal_input');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_histories');
    }
};
