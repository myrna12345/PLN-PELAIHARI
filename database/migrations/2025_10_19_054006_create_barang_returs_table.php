<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ..._create_barang_retur_table.php
public function up(): void
{
    Schema::create('barang_retur', function (Blueprint $table) {
        $table->id();
        $table->string('kode_barang');
        $table->string('nama_barang');
        $table->integer('jumlah');
        $table->date('tanggal');
        $table->text('keterangan')->nullable();
        $table->enum('status', ['baik', 'rusak']); // Sesuai kebutuhan dashboard
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_returs');
    }
};
