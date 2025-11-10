<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialStandBy extends Model
{
    use HasFactory;
    
    /**
     * Nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'material_stand_by';

    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'material_id', 
        'nama_petugas', 
        'jumlah', 
        'tanggal', 
        'foto_path'
    ];

    /**
     * === INI ADALAH PERBAIKANNYA ===
     * Beri tahu Laravel untuk mengubah kolom 'tanggal' menjadi objek datetime.
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