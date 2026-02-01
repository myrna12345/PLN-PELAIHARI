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
    Schema::create('material_history', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('material_id'); // TAMBAHKAN INI
        $table->string('nama_material');
        $table->integer('jumlah');
        $table->string('satuan');
        $table->string('foto_path'); // PASTIKAN NAMANYA foto_path (sesuai error)
        $table->timestamp('tanggal_input');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_history');
    }
};
