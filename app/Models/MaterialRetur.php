<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'tanggal', 
        'status', // 'baik' or 'rusak'
        'keterangan',
        'foto_path',
        'material_keluar',
        'material_kembali' 
    ];

    /**
     * Kolom yang harus di-cast (diubah) menjadi tipe data tertentu.
     * Ini PENTING untuk jam.
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
}