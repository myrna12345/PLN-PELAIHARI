<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_kembali', function (Blueprint $table) {
            // 🟢 Tambahkan kolom satuan (string, max 50 karakter)
            $table->string('satuan', 50)->nullable()->after('jumlah_material');
        });
    }

    public function down(): void
    {
        Schema::table('material_kembali', function (Blueprint $table) {
            // 🔴 Hapus kolom satuan jika migration di-rollback
            $table->dropColumn('satuan');
        });
    }
};