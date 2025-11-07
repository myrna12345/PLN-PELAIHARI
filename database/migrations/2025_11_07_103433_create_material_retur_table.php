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
        Schema::create('material_retur', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel master material
            $table->foreignId('material_id')->constrained('materials'); 
            
            $table->string('nama_petugas');
            $table->integer('jumlah');
            $table->datetime('tanggal'); // Pakai datetime agar bisa simpan jam
            
            // Kolom status untuk membedakan 'Baik' atau 'Rusak'
            $table->enum('status', ['baik', 'rusak']); 
            
            $table->text('keterangan')->nullable(); // Untuk catatan tambahan
            $table->string('foto_path')->nullable(); // Opsional jika retur butuh foto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_retur');
    }
};