<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_keluar', function (Blueprint $table) { 
            // Ditempatkan setelah 'jumlah_material'
            $table->string('satuan_material', 50)->after('jumlah_material');
        });
    }

    public function down(): void
    {
        Schema::table('material_keluar', function (Blueprint $table) {
            $table->dropColumn('satuan_material');
        });
    }
};