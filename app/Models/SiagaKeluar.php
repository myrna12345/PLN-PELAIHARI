<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiagaKeluar extends Model
{
    use HasFactory;

    protected $table = 'siaga_keluars';

    // DAFTAR KOLOM YANG BOLEH DISIMPAN (Mass Assignment)
    protected $fillable = [
        'material_id',
        'nomor_unit', 
        'nama_material_lengkap', 
        'nama_petugas',
        'stand_meter',
        // 'jumlah_siaga_keluar' dan 'jumlah_siaga_masuk' DIHAPUS dari fillable
        'status',
        'tanggal',
        'foto_path',
    ];
    
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}