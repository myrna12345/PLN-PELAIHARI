<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // 💡 TAMBAHAN: Import untuk Accessor/Mutator

class MaterialRetur extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'material_retur';

    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'material_id', 
        'nama_petugas', 
        'jumlah', 
        'satuan',
        'tanggal', 
        'status', // 'baik' or 'rusak'
        'keterangan',
        'foto_path',
        'material_keluar',
        'material_kembali' 
    ];

    /**
     * Kolom yang harus di-cast (diubah) menjadi tipe data tertentu.
     */
    protected $casts = [
        'tanggal' => 'datetime',
    ];

    /**
     * Relasi ke model Material (untuk mengambil nama material).
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
    
    // 💡 FUNGSI PERBAIKAN: Accessor untuk menerjemahkan nilai status 💡

    /**
     * Accessor untuk mengkonversi nilai 'status' dari database 
     * menjadi string yang lebih mudah dibaca (misalnya 'Baik').
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                // Jika nilai di DB adalah 'bekas_andal', tampilkan 'Baik'
                if ($value === 'bekas_andal') {
                    return 'Baik';
                }
                // Jika nilai di DB adalah 'rusak', atau nilai lainnya, tampilkan nilai aslinya
                return $value;
            },
            // Tidak perlu Mutator (set) jika Anda mengelola nilai 'bekas_andal' di Controller
        );
    }
}