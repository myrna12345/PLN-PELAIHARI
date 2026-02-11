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
        Schema::table('users', function (Blueprint $table) {
            // Cek dulu: Jika kolom 'role' BELUM ada, baru tambahkan
            if (!Schema::hasColumn('users', 'role')) {
                // Tambahkan kolom role dengan default 'staff' setelah email
                $table->string('role')->default('staff')->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dulu: Jika kolom 'role' ADA, baru hapus
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
}; 