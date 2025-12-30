<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_retur', function (Blueprint $table) {
            // Cek apakah kolom 'satuan' sudah ada, jika belum baru tambahkan
            if (!Schema::hasColumn('material_retur', 'satuan')) {
                $table->string('satuan')->after('jumlah');
            }
            
            // Tambahkan juga kolom foto_petugas jika belum ada
            if (!Schema::hasColumn('material_retur', 'foto_petugas')) {
                $table->string('foto_petugas')->nullable()->after('foto_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_retur', function (Blueprint $table) {
            $table->dropColumn(['satuan', 'foto_petugas']);
        });
    }
};