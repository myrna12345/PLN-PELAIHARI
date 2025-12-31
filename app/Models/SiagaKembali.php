<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiagaKembali extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'material_id',
        'nomor_meter',
        'nama_material_lengkap',
        'nama_petugas',
        'stand_meter',
        'keterangan',
        'status',
        'tanggal',
        'foto_path',    // Foto Material
        'foto_petugas', // Foto Petugas (DITAMBAHKAN KEMBALI)
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function standbyDetail() 
    {
        return $this->belongsTo(MaterialSiagaStandBy::class, 'nomor_meter', 'nomor_meter');
    }
}