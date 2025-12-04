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
        // TARGET YANG BENAR: material_retur
        Schema::table('material_retur', function (Blueprint $table) {
            $table->string('satuan')->after('jumlah'); // Menambahkan kolom satuan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_retur', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }
};