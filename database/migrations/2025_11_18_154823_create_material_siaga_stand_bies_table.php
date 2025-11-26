<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('material_siaga_standbies', function (Blueprint $table) {
            $table->id();
            $table->string('nama_material');
            $table->string('nama_petugas');
            $table->string('stand_meter');
            $table->integer('jumlah_siaga_standby');
            $table->dateTime('tanggal');
            $table->string('foto')->nullable();

            // ➤ Tambahkan kolom status di sini
            $table->enum('status', ['Ready', 'Terpakai'])->default('Ready');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('material_siaga_standbies');
    }
};
